<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Pembungkus sederhana untuk Midtrans Snap (tanpa SDK tambahan).
 * Dokumentasi: https://docs.midtrans.com/docs/snap-snap-integration-guide
 */
class MidtransService
{
    private function serverKey(): string
    {
        return (string) config('shop.midtrans.server_key');
    }

    private function isProduction(): bool
    {
        return (bool) config('shop.midtrans.is_production');
    }

    /** Alamat file snap.js (dimuat di halaman pembayaran). */
    public function snapJsUrl(): string
    {
        return $this->isProduction()
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }

    /** Minta token Snap untuk sebuah pesanan. Token disimpan agar tidak dibuat ulang. */
    public function snapToken(Order $order): string
    {
        if ($order->snap_token) {
            return $order->snap_token;
        }

        $url = $this->isProduction()
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        $response = Http::withBasicAuth($this->serverKey(), '')
            ->acceptJson()
            ->timeout(15)
            ->retry(2, 500)
            ->post($url, [
                'transaction_details' => [
                    'order_id'     => $order->order_number,
                    'gross_amount' => (int) $order->amount,
                ],
                'item_details' => [[
                    'id'       => (string) $order->product_id,
                    'price'    => (int) $order->amount,
                    'quantity' => 1,
                    'name'     => Str::limit($order->product_name, 45, ''),
                ]],
                'customer_details' => [
                    'first_name' => $order->buyer_name,
                    'email'      => $order->buyer_email,
                ],
                'callbacks' => [
                    'finish' => route('checkout.success', $order->access_token),
                ],
            ])
            ->throw();

        $token = $response->json('token');
        if (! is_string($token) || trim($token) === '') {
            throw ValidationException::withMessages([
                'payment' => 'Midtrans tidak mengembalikan token Snap.',
            ]);
        }

        $order->update(['snap_token' => $token]);

        return $token;
    }

    /**
     * Validasi notifikasi dari Midtrans (N9): signature_key harus sama dengan
     * SHA512(order_id + status_code + gross_amount + server_key).
     */
    public function isValidSignature(array $payload): bool
    {
        foreach (['order_id', 'status_code', 'gross_amount', 'signature_key'] as $key) {
            if (! isset($payload[$key])) {
                return false;
            }
        }

        $expected = hash('sha512', $payload['order_id'] . $payload['status_code'] . $payload['gross_amount'] . $this->serverKey());

        return hash_equals($expected, (string) $payload['signature_key']);
    }

    /** Ambil status transaksi langsung dari Midtrans (Get Status API). Null jika gagal / tidak ditemukan. */
    public function fetchStatus(string $orderNumber): ?array
    {
        $base = $this->isProduction() ? 'https://api.midtrans.com' : 'https://api.sandbox.midtrans.com';

        $response = Http::withBasicAuth($this->serverKey(), '')
            ->acceptJson()->timeout(10)
            ->get("{$base}/v2/" . rawurlencode($orderNumber) . '/status');

        $data = $response->json();

        // Midtrans membalas HTTP 200 dengan status_code "404" di dalam body bila transaksi belum ada.
        return ($response->successful() && isset($data['transaction_status'])) ? $data : null;
    }

    /**
     * Terapkan status transaksi Midtrans ke pesanan. Dipakai bersama oleh webhook dan sinkronisasi manual,
     * sehingga aturannya hanya ada di satu tempat:
     *  - settlement / capture (fraud accept) -> lunas + email terkirim (lewat Order::markAsPaid)
     *  - deny / cancel / expire / failure    -> gagal (kecuali sudah lunas)
     *  - pending                             -> hanya mencatat metode bayar
     */
    public function applyStatus(Order $order, array $data): void
    {
        $status = $data['transaction_status'] ?? '';
        $fraud  = $data['fraud_status'] ?? 'accept';
        $type   = $data['payment_type'] ?? null;

        if ($status === 'settlement' || ($status === 'capture' && $fraud === 'accept')) {
            $order->markAsPaid($type);
            return;
        }

        if ($order->isPaid()) {
            return; // jangan turunkan status pesanan yang sudah lunas
        }

        if (in_array($status, ['deny', 'cancel', 'expire', 'failure'], true)) {
            $order->update(['status' => Order::FAILED, 'payment_method' => $type ?: $order->payment_method]);
        } elseif ($status === 'pending' && $type) {
            $order->update(['payment_method' => $type]);
        }
    }

    /**
     * Cek status ke Midtrans untuk pesanan yang masih pending.
     * Berguna saat webhook belum/tidak sampai (misalnya saat development di localhost).
     */
    public function syncStatus(Order $order): void
    {
        if ($order->status !== Order::PENDING) {
            return;
        }

        try {
            if ($data = $this->fetchStatus($order->order_number)) {
                if (isset($data['gross_amount'])
                    && (int) round((float) $data['gross_amount']) !== (int) $order->amount) {
                    report("Midtrans status nominal tidak cocok untuk pesanan {$order->order_number}");
                    return;
                }

                $this->applyStatus($order, $data);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
