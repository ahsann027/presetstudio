<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Webhook Midtrans. Pesanan hanya dinyatakan lunas dari sini (atau dari sinkronisasi server-ke-server),
 * tidak pernah dari parameter yang dikirim browser pembeli.
 */
class PaymentNotificationController extends Controller
{
    public function __invoke(Request $request, MidtransService $midtrans)
    {
        $payload = $request->all();

        // 1. Pastikan benar-benar dari Midtrans: signature = SHA512(order_id + status_code + gross_amount + server_key)
        if (! $midtrans->isValidSignature($payload)) {
            Log::warning('Midtrans webhook: signature tidak valid', ['ip' => $request->ip()]);
            abort(403, 'Signature tidak valid');
        }

        // 2. Cari pesanan. Order tak dikenal (mis. tombol "test notification" Midtrans) dijawab 200 agar tidak di-retry terus.
        $order = Order::where('order_number', $payload['order_id'])->first();
        if (! $order) {
            return response()->json(['ok' => false, 'message' => 'Pesanan tidak ditemukan']);
        }

        // 3. Nominal harus sama dengan pesanan (mencegah manipulasi).
        if ((int) round((float) $payload['gross_amount']) !== (int) $order->amount) {
            Log::warning('Midtrans webhook: nominal tidak cocok', ['order' => $order->order_number]);
            abort(400, 'Nominal tidak cocok');
        }

        // 4. settlement/capture -> paid + OrderPaidMail (sekali saja); lainnya diatur di applyStatus().
        $midtrans->applyStatus($order, $payload);

        return response()->json(['ok' => true]);
    }
}
