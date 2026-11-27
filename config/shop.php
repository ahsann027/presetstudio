<?php

// Pengaturan toko. Ubah nilainya lewat file .env, bukan di sini.
return [
    'name'    => env('SHOP_NAME', 'Preset Studio'),
    'tagline' => env('SHOP_TAGLINE', 'Preset foto siap pakai, bayar langsung, unduh seketika'),

    // Kontak penjual (N7: sebaiknya nomor/email khusus jualan).
    // WhatsApp format internasional tanpa + dan tanpa 0 di depan, contoh: 6281234567890
    'whatsapp'     => env('SHOP_WHATSAPP', '6281234567890'),
    'seller_email' => env('SHOP_SELLER_EMAIL', 'penjual@example.com'),
    'hours'        => env('SHOP_HOURS', 'Setiap hari, 08.00 - 21.00 WITA'),

    // Masa berlaku link unduhan setelah lunas (N9).
    'download_days'  => (int) env('SHOP_DOWNLOAD_DAYS', 7),
    // Maksimal berapa kali file boleh diunduh per pesanan (mengurangi penyebaran link).
    'download_limit' => (int) env('SHOP_DOWNLOAD_LIMIT', 5),

    // Akun admin awal (dipakai AdminSeeder). Tidak ada password bawaan: wajib diisi di .env.
    'admin_email'    => env('ADMIN_EMAIL'),
    'admin_password' => env('ADMIN_PASSWORD'),

    // 'simulation' = pembayaran palsu untuk demo/tugas (tanpa akun gateway).
    // 'midtrans'   = pembayaran sungguhan via Midtrans Snap.
    'payment_driver' => env('PAYMENT_DRIVER', 'simulation'),

    'midtrans' => [
        'server_key'    => env('MIDTRANS_SERVER_KEY'),
        'client_key'    => env('MIDTRANS_CLIENT_KEY'),
        'is_production' => (bool) env('MIDTRANS_IS_PRODUCTION', false),
    ],

    // Metode pembayaran + instruksinya (F6). Ditampilkan di halaman pembayaran dan halaman info.
    'payment_methods' => [
        'qris' => [
            'label' => 'QRIS',
            'steps' => [
                'Kode QR akan muncul setelah Anda menekan tombol bayar.',
                'Buka aplikasi mobile banking atau e-wallet apa pun yang mendukung QRIS.',
                'Pilih menu Scan/Bayar, lalu pindai kode QR.',
                'Periksa nominal, lalu konfirmasi pembayaran.',
            ],
        ],
        'virtual_account' => [
            'label' => 'Virtual Account (Transfer Bank)',
            'steps' => [
                'Pilih bank (BCA, BNI, BRI, Mandiri, dll.) setelah menekan tombol bayar.',
                'Salin nomor virtual account yang ditampilkan.',
                'Buka mobile banking/ATM, pilih Transfer > Virtual Account, masukkan nomornya.',
                'Pastikan nominal sesuai, lalu konfirmasi.',
            ],
        ],
        'ewallet' => [
            'label' => 'Dompet Digital (GoPay, ShopeePay, DANA, dll.)',
            'steps' => [
                'Pilih dompet digital setelah menekan tombol bayar.',
                'Anda akan diarahkan ke aplikasi dompet digital tersebut.',
                'Periksa nominal, lalu konfirmasi dengan PIN Anda.',
                'Kembali ke website; halaman akan menampilkan tautan unduhan otomatis.',
            ],
        ],
    ],
];
