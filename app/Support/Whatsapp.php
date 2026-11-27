<?php

namespace App\Support;

class Whatsapp
{
    /** Buat link wa.me ke nomor penjual dengan pesan yang sudah di-encode (aman untuk karakter khusus & baris baru). */
    public static function link(string $message): string
    {
        return 'https://wa.me/' . config('shop.whatsapp') . '?text=' . rawurlencode($message);
    }
}
