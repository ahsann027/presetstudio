<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DownloadController extends Controller
{
    /**
     * Unduh file preset. Lapisan pengaman:
     *  1. Route memakai middleware `signed` -> URL tidak bisa dipalsukan dan punya masa berlaku sendiri.
     *  2. Token pesanan (40 karakter acak) harus cocok dengan pesanan di database.
     *  3. Pesanan harus berstatus paid.
     *  4. Masa berlaku (download_expires_at) belum lewat.
     *  5. Jumlah unduhan belum melebihi batas (SHOP_DOWNLOAD_LIMIT).
     */
    public function __invoke(string $token)
    {
        $order = Order::where('access_token', $token)->with('product')->firstOrFail();

        abort_unless($order->isPaid(), 403, 'Pesanan ini belum dibayar.');
        abort_unless($order->isDownloadActive(), 410, 'Link unduhan sudah kedaluwarsa. Hubungi penjual untuk meminta link baru.');

        $file = $order->product->download_url;
        abort_if(blank($file), 404, 'File belum tersedia. Hubungi penjual.');

        $isExternal = Str::startsWith($file, ['http://', 'https://']);
        if (! $isExternal) {
            abort_unless(Storage::disk('local')->exists($file), 404, 'File tidak ditemukan. Hubungi penjual.');
        }

        // Tambah hitungan secara atomik; gagal bila sudah mencapai batas (aman dari klik ganda bersamaan).
        $allowed = Order::whereKey($order->id)
            ->where('download_count', '<', (int) config('shop.download_limit'))
            ->increment('download_count');
        abort_unless($allowed, 403, 'Batas jumlah unduhan sudah tercapai. Hubungi penjual.');

        if ($isExternal) {
            return redirect()->away($file); // Google Drive, dll.
        }

        $extension = pathinfo($file, PATHINFO_EXTENSION);
        $name      = Str::slug($order->product_name) . ($extension ? ".{$extension}" : '');

        return Storage::disk('local')->download($file, $name);
    }
}
