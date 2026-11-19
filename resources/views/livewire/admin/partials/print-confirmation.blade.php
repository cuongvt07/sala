{{-- Xác nhận đặt phòng (Booking Confirmation). Dữ liệu: $d (BookingForm::printConfirmation) --}}
<style>
    .booking-confirmation-page{background:#fff;padding:56px 64px;position:relative;overflow:hidden;color:#1a1a1a;font-family:'Source Serif 4',Georgia,serif}
    .booking-confirmation-page .header-lux{display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;gap:20px}
    .booking-confirmation-page .logo-img{width:100px;height:auto;object-fit:contain}
    .booking-confirmation-page .hotel-info{text-align:center;flex:1}
    .booking-confirmation-page .hotel-name{font-size:20px;font-weight:700;letter-spacing:.05em;text-transform:uppercase}
    .booking-confirmation-page .hotel-contact{font-size:12.5px;color:#444;line-height:2;margin-top:4px}
    .booking-confirmation-page .qr-box-lux{width:64px;height:64px;border:1.5px solid #d4c9b0;display:flex;align-items:center;justify-content:center;overflow:hidden}
    .booking-confirmation-page .divider-lux{display:flex;align-items:center;gap:12px;margin:4px 0 24px}
    .booking-confirmation-page .divider-line{flex:1;height:1px;background:#d4c9b0}
    .booking-confirmation-page .divider-diamond{width:8px;height:8px;background:#b8975a;transform:rotate(45deg)}
    .booking-confirmation-page .title-block{text-align:center;margin-bottom:24px}
    .booking-confirmation-page .title-label{font-size:26px;font-weight:700;letter-spacing:.15em;text-transform:uppercase}
    .booking-confirmation-page .intro{font-size:13.5px;line-height:1.8;text-align:center;color:#444;margin-bottom:24px;font-style:italic}
    .booking-confirmation-page .details-table{width:100%;border-collapse:collapse;font-size:13.5px;margin-bottom:24px}
    .booking-confirmation-page .details-table td{padding:12px 16px;border:1px solid #d4c9b0;vertical-align:top;line-height:1.6}
    .booking-confirmation-page .details-table .label{color:#444;font-weight:300;white-space:nowrap;width:25%}
    .booking-confirmation-page .details-table .value{font-weight:600}
    .booking-confirmation-page .price-note{font-size:11.5px;color:#444;font-weight:300;font-style:italic;margin-top:4px}
    .booking-confirmation-page .paid-badge{display:inline-block;background:#e8f4ec;color:#2e7d4f;border:1px solid #a8d5b5;padding:1px 10px;border-radius:20px;font-size:11px;text-transform:uppercase;font-weight:600;margin-left:8px}
    .booking-confirmation-page .signature-lux{text-align:right;margin-top:28px}
    .booking-confirmation-page .sig-title{font-size:12px;letter-spacing:.12em;text-transform:uppercase;color:#444}
    .booking-confirmation-page .sig-line{width:160px;height:1px;background:#d4c9b0;margin:14px 0 8px auto}
    .booking-confirmation-page .sig-name{font-size:16px;font-style:italic}
    .booking-confirmation-page .sig-company{font-size:12px;color:#b8975a;letter-spacing:.1em;text-transform:uppercase;margin-top:2px}
</style>
<div class="booking-confirmation-page" id="bfConfirmation">
    <div class="header-lux">
        <div class="logo-area"><img src="{{ asset('logo.jpg') }}" alt="Logo" class="logo-img"></div>
        <div class="hotel-info">
            <div class="hotel-name">Sala Apartment and Hotel Da Nang</div>
            <div class="hotel-contact">Hotline: +84 84 424 4567<br>Address: 16 Ly Nhat Quang, Son Tra district, Da Nang city</div>
        </div>
        <div class="qr-box-lux"><img src="{{ asset('qr.jpg') }}" alt="QR" style="width:100%;height:100%;object-fit:contain"></div>
    </div>
    <div class="divider-lux"><div class="divider-line"></div><div class="divider-diamond"></div><div class="divider-line"></div></div>
    <div class="title-block"><div class="title-label">Booking Confirmation</div></div>
    <p class="intro">Thank you for choosing and using services at Sala Apartment and Hotel for your trip.<br>Sala Apartment and Hotel would like to confirm your booking:</p>
    <table class="details-table">
        <tr>
            <td class="label">Names of the guest</td>
            <td class="value">
                {{ $d['customer_name'] ?? '-' }}
                @if(!empty($d['guests']))<div style="font-size:11px;color:#666;font-weight:400;margin-top:4px">Stay with: {{ implode(', ', $d['guests']) }}</div>@endif
            </td>
            <td class="label">Phone number</td>
            <td class="value">{{ $d['customer_phone'] ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Room</td>
            <td class="value">{{ $d['room_code'] ?? '-' }}</td>
            <td class="label">Number of guests</td>
            <td class="value">{{ sprintf('%02d', count($d['guests'] ?? []) + 1) }}</td>
        </tr>
        <tr>
            <td class="label">Arrival date</td>
            <td class="value">{{ $d['check_in'] ?? '-' }}</td>
            <td class="label">Term of stay</td>
            <td class="value">{{ $d['term_of_stay'] ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Price of the room</td>
            <td colspan="3" class="value">{{ $d['unit_price'] ?? 0 }} VND / {{ $d['price_type'] ?? '' }}
                <div class="price-note">(not included deposit fee, electric fee and water fee)</div>
            </td>
        </tr>
        <tr>
            <td class="label">Deposit money</td>
            <td colspan="3" class="value">{{ number_format($d['total_deposit'] ?? 0, 0, ',', '.') }} VND
                @if(($d['total_deposit'] ?? 0) > 0)<span class="paid-badge">✓ Paid</span>@endif
            </td>
        </tr>
    </table>
    <div class="divider-lux"><div class="divider-line"></div><div class="divider-diamond"></div><div class="divider-line"></div></div>
    <div class="signature-lux">
        <div class="sig-title">Apartment Confirmation</div>
        <div class="sig-line"></div>
        <div class="sig-name">Ngo Thi Phuong Thao</div>
        <div class="sig-company">Sala Apartment</div>
    </div>
</div>
