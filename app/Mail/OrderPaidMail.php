<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderPaidMail extends Mailable implements ShouldQueue
{
    public function __construct(public Order $order, public bool $forSeller = false)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->forSeller
                ? "Pesanan baru {$this->order->order_number} sudah dibayar"
                : "Pembayaran berhasil - {$this->order->order_number}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.order-paid');
    }
}
