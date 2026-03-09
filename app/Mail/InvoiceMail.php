<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;
    public Collection $usageLogs;
    public float $totalAmount;

    public function __construct(Booking $booking, Collection $usageLogs)
    {
        $this->booking = $booking;
        $this->usageLogs = $usageLogs;
        $this->totalAmount = $usageLogs->sum('total_amount') + $booking->price;
    }

    public function envelope(): Envelope
    {
        $roomCode = $this->booking->room->code ?? 'N/A';
        return new Envelope(
            subject: 'Hoá đơn thanh toán - Phòng ' . $roomCode . ' - ' . now()->format('d/m/Y'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
        );
    }
}
