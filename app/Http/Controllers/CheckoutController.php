<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Services\MidtransService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    /** F5: halaman checkout - data pembeli, ringkasan pesanan, total harga. */
    public function create(int $id)
    {
        $product = Product::active()->with('images')->findOrFail($id);

        return view('checkout.create', compact('product'));
    }

    /** Simpan pesanan (status pending), lanjut ke halaman pembayaran. */
    public function store(Request $request, int $id)
    {
        $product = Product::active()->findOrFail($id);

        $data = $request->validate([
            'buyer_name'  => ['required', 'string', 'max:100'],
            'buyer_email' => ['required', 'email', 'max:150'],
        ], [], [
            'buyer_name'  => 'nama',
            'buyer_email' => 'email',
        ]);

        $order = Order::createFor($product, $data['buyer_name'], $data['buyer_email']);

        return redirect()->route('checkout.pay', $order->access_token);
    }

    /** F6: pilihan metode pembayaran + instruksi. */
    public function pay(string $token, MidtransService $midtrans)
    {
        $order = Order::where('access_token', $token)->firstOrFail();

        // Sudah lunas atau gagal/kedaluwarsa -> tampilkan halaman status, bukan halaman bayar.
        if ($order->status !== Order::PENDING) {
            return redirect()->route('checkout.success', $token);
        }

        $driver    = config('shop.payment_driver');
        $methods   = config('shop.payment_methods');
        $snapToken = null;
        $snapJsUrl = null;
        $error     = null;

        if ($driver === 'midtrans') {
            try {
                $snapToken = $midtrans->snapToken($order);
                $snapJsUrl = $midtrans->snapJsUrl();
            } catch (\Throwable $e) {
                report($e);
                $error = 'Pembayaran belum bisa diproses saat ini. Silakan coba lagi beberapa saat atau pesan lewat WhatsApp.';
            }
        }

        return view('checkout.pay', compact('order', 'driver', 'methods', 'snapToken', 'snapJsUrl', 'error'));
    }

    /** Hanya untuk mode simulasi (demo): anggap pembayaran berhasil. */
    public function simulate(Request $request, string $token)
    {
        abort_unless(config('shop.payment_driver') === 'simulation', 404);

        $order = Order::where('access_token', $token)->firstOrFail();

        $data = $request->validate([
            'method' => ['required', 'in:' . implode(',', array_keys(config('shop.payment_methods')))],
        ]);

        $order->markAsPaid($data['method']);

        return redirect()->route('checkout.success', $token);
    }

    /** F7: halaman status pesanan / pembayaran berhasil. */
    public function success(string $token, MidtransService $midtrans)
    {
        $order = Order::where('access_token', $token)->with('product')->firstOrFail();

        // Jika webhook belum sampai (mis. di localhost), tanyakan langsung status ke Midtrans.
        if ($order->status === Order::PENDING && config('shop.payment_driver') === 'midtrans') {
            $midtrans->syncStatus($order);
        }

        return view('checkout.success', compact('order'));
    }
}
