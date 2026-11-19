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
    public float $totalDeposit;
    public float $roomPrice;
    public ?string $periodLabel;

    /**
     * @param float|null  $roomPrice   Tiền phòng dùng cho hóa đơn (mặc định = giá booking).
     *                                 Với hợp đồng, truyền giá 1 THÁNG để xuất bill theo tháng.
     * @param string|null $periodLabel Nhãn kỳ (vd "06/2026") khi xuất hóa đơn theo tháng.
     */
    public function __construct(Booking $booking, Collection $usageLogs, ?float $roomPrice = null, ?string $periodLabel = null)
    {
        $this->booking = $booking;
        $this->usageLogs = $usageLogs;
        $this->roomPrice = $roomPrice !== null ? $roomPrice : (float) $booking->price;
        $this->periodLabel = $periodLabel;
        $this->totalAmount = $usageLogs->sum('total_amount') + $this->roomPrice;
        // Thuê ngày: tổng đã thanh toán (gồm cọc) từ sổ thu chi. Hợp đồng: tiền cọc đang giữ.
        $txns = $booking->transactions()->get(['type', 'category', 'amount']);
        if ($booking->isContract()) {
            $this->totalDeposit = (float) $txns->where('type', 'income')->where('category', 'Tiền cọc')->sum('amount')
                - (float) $txns->where('type', 'expense')->where('category', 'Hoàn cọc')->sum('amount');
        } else {
            $this->totalDeposit = (float) $txns->where('type', 'income')->sum('amount')
                - (float) $txns->where('type', 'expense')->sum('amount');
        }
    }

    public function envelope(): Envelope
    {
        $roomCode = $this->booking->room->code ?? 'N/A';
        $suffix = $this->periodLabel ? (' - Kỳ ' . $this->periodLabel) : (' - ' . now()->format('d/m/Y'));
        return new Envelope(
            subject: 'Hoá đơn thanh toán - Phòng ' . $roomCode . $suffix,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
        );
    }
}
