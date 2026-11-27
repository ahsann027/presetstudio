<?php

namespace App\Models;

use App\Mail\OrderPaidMail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

class Order extends Model
{
    public const PENDING = 'pending';
    public const PAID    = 'paid';
    public const FAILED  = 'failed';

    protected $fillable = [
        'order_number', 'access_token', 'product_id', 'product_name', 'buyer_name', 'buyer_email',
        'amount', 'status', 'payment_method', 'snap_token', 'paid_at', 'download_expires_at',
        'download_count',
    ];

    protected $casts = [
        'amount'              => 'decimal:2',
        'paid_at'             => 'datetime',
        'download_expires_at' => 'datetime',
        'download_count'      => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** Buat pesanan baru berstatus pending untuk satu preset. */
    public static function createFor(Product $product, string $name, string $email): self
    {
        return self::create([
            'order_number' => 'INV-' . now()->format('ymd') . '-' . strtoupper(Str::random(5)),
            'access_token' => Str::random(40),
            'product_id'   => $product->id,
            'product_name' => $product->name,
            'buyer_name'   => $name,
            'buyer_email'  => $email,
            'amount'       => $product->price,
            'status'       => self::PENDING,
        ]);
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    public function isPaid(): bool
    {
        return $this->status === self::PAID;
    }

    /** Link unduhan hanya aktif jika sudah lunas dan belum lewat masa berlaku (N9). */
    public function isDownloadActive(): bool
    {
        return $this->isPaid() && $this->download_expires_at && $this->download_expires_at->isFuture();
    }

    public function downloadUrl(): string
    {
        return URL::temporarySignedRoute(
            'download',
            $this->download_expires_at,
            ['token' => $this->access_token],
        );
    }

    public function whatsappConfirmMessage(): string
    {
        return implode("\n", [
            'Halo Kak, saya ingin mengonfirmasi pesanan:',
            "- Nomor pesanan: {$this->order_number}",
            "- Nama preset: {$this->product_name}",
            "- Nama pembeli: {$this->buyer_name}",
            '- Status: ' . ($this->isPaid() ? 'Sudah dibayar' : 'Menunggu pembayaran'),
        ]);
    }

    /**
     * Tandai pesanan lunas. Aman dipanggil berulang (notifikasi gateway bisa terkirim lebih dari sekali):
     * pesanan hanya diproses dan email hanya dikirim satu kali.
     */
    public function markAsPaid(?string $method = null): void
    {
        $justPaid = false;

        DB::transaction(function () use ($method, &$justPaid) {
            $fresh = self::whereKey($this->id)->lockForUpdate()->first();

            if ($fresh->isPaid()) {
                return;
            }

            $fresh->update([
                'status'              => self::PAID,
                'payment_method'      => $method ?: $fresh->payment_method,
                'paid_at'             => now(),
                'download_expires_at' => now()->addDays((int) config('shop.download_days')),
            ]);
            $justPaid = true;
        });

        $this->refresh();

        if ($justPaid) {
            $this->sendPaidEmails();
        }
    }

    private function sendPaidEmails(): void
    {
        try {
            Mail::to($this->buyer_email)->send(new OrderPaidMail($this));                  // ke pembeli
            if ($seller = config('shop.seller_email')) {
                Mail::to($seller)->send(new OrderPaidMail($this, forSeller: true));        // notifikasi ke penjual
            }
        } catch (\Throwable $e) {
            report($e); // gagal kirim email tidak boleh membatalkan pesanan yang sudah lunas
        }
    }
}
