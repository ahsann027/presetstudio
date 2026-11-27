<!DOCTYPE html>
<html lang="id">
<body style="font-family: Arial, sans-serif; color:#1e293b; line-height:1.5;">
@if ($forSeller)
    <h2>Pesanan baru sudah dibayar</h2>
    <p>
        Nomor pesanan: <strong>{{ $order->order_number }}</strong><br>
        Preset: {{ $order->product_name }}<br>
        Total: {{ $order->formatted_amount }}<br>
        Pembeli: {{ $order->buyer_name }} ({{ $order->buyer_email }})<br>
        Metode: {{ $order->payment_method ?: '-' }}
    </p>
@else
    <h2>Pembayaran berhasil, terima kasih {{ $order->buyer_name }}!</h2>
    <p>
        Nomor pesanan: <strong>{{ $order->order_number }}</strong><br>
        Preset: {{ $order->product_name }}<br>
        Total: {{ $order->formatted_amount }}
    </p>
    <p><a href="{{ $order->downloadUrl() }}" style="background:#059669;color:#fff;padding:10px 18px;border-radius:6px;text-decoration:none;">Unduh preset</a></p>
    <p>Link berlaku sampai {{ $order->download_expires_at->format('d M Y, H:i') }}.<br>
       Panduan pemasangan ada di halaman pesanan: <a href="{{ route('checkout.success', $order->access_token) }}">{{ route('checkout.success', $order->access_token) }}</a></p>
@endif
</body>
</html>
