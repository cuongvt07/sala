<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hoá đơn thanh toán</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f6f9; font-family:'Segoe UI',Roboto,Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f9; padding:32px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,0.08);">
                    {{-- HEADER --}}
                    <tr>
                        <td style="background:linear-gradient(135deg,#4f46e5,#3b82f6); padding:28px 32px; text-align:center;">
                            <h1 style="margin:0; color:#ffffff; font-size:22px; font-weight:800; letter-spacing:1px;">HOÁ ĐƠN THANH TOÁN</h1>
                            <p style="margin:6px 0 0; color:rgba(255,255,255,0.85); font-size:13px;">{{ now()->format('d/m/Y H:i') }}</p>
                        </td>
                    </tr>

                    {{-- BOOKING INFO --}}
                    <tr>
                        <td style="padding:24px 32px 16px;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding:10px 14px; background:#f8fafc; border-radius:8px; border:1px solid #e2e8f0;">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding:4px 0; font-size:13px; color:#64748b; width:40%;">Khách hàng:</td>
                                                <td style="padding:4px 0; font-size:13px; color:#0f172a; font-weight:700; text-align:right;">{{ $booking->customer->name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:4px 0; font-size:13px; color:#64748b;">Phòng:</td>
                                                <td style="padding:4px 0; font-size:13px; color:#0f172a; font-weight:700; text-align:right;">{{ $booking->room->code ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:4px 0; font-size:13px; color:#64748b;">Ngày vào:</td>
                                                <td style="padding:4px 0; font-size:13px; color:#0f172a; font-weight:700; text-align:right;">{{ $booking->check_in ? $booking->check_in->format('d/m/Y') : '-' }}</td>
                                            </tr>
                                            @if($booking->check_out)
                                            <tr>
                                                <td style="padding:4px 0; font-size:13px; color:#64748b;">Ngày ra:</td>
                                                <td style="padding:4px 0; font-size:13px; color:#0f172a; font-weight:700; text-align:right;">{{ $booking->check_out->format('d/m/Y') }}</td>
                                            </tr>
                                            @endif
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- DETAIL TABLE --}}
                    <tr>
                        <td style="padding:0 32px 16px;">
                            <p style="margin:0 0 10px; font-size:13px; font-weight:800; color:#0f172a; text-transform:uppercase; letter-spacing:1px;">Chi tiết hoá đơn</p>
                            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                <thead>
                                    <tr style="background:#f1f5f9;">
                                        <th style="padding:8px 10px; font-size:11px; color:#64748b; text-align:left; text-transform:uppercase; font-weight:700; border-bottom:2px solid #e2e8f0;">Khoản mục</th>
                                        <th style="padding:8px 10px; font-size:11px; color:#64748b; text-align:center; text-transform:uppercase; font-weight:700; border-bottom:2px solid #e2e8f0;">Chi tiết</th>
                                        <th style="padding:8px 10px; font-size:11px; color:#64748b; text-align:right; text-transform:uppercase; font-weight:700; border-bottom:2px solid #e2e8f0;">Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Room base price --}}
                                    <tr>
                                        <td style="padding:8px 10px; font-size:13px; color:#0f172a; font-weight:600; border-bottom:1px solid #f1f5f9;">Tiền phòng</td>
                                        <td style="padding:8px 10px; font-size:12px; color:#64748b; text-align:center; border-bottom:1px solid #f1f5f9;">{{ $booking->price_type === 'month' ? 'Dài hạn' : 'Ngày' }}</td>
                                        <td style="padding:8px 10px; font-size:13px; color:#0f172a; font-weight:700; text-align:right; border-bottom:1px solid #f1f5f9;">{{ number_format($booking->price, 0, ',', '.') }}đ</td>
                                    </tr>
                                    {{-- Usage logs --}}
                                    @foreach($usageLogs as $log)
                                    <tr>
                                        <td style="padding:8px 10px; font-size:13px; color:#0f172a; font-weight:600; border-bottom:1px solid #f1f5f9;">
                                            {{ $log->service->name ?? 'Phí phụ thu khác' }}
                                        </td>
                                        <td style="padding:8px 10px; font-size:12px; color:#64748b; text-align:center; border-bottom:1px solid #f1f5f9;">
                                            @if($log->type === 'meter')
                                                {{ $log->start_index }} → {{ $log->end_index }}
                                            @elseif($log->type === 'manual')
                                                Phụ thu
                                            @else
                                                SL: {{ $log->quantity }}
                                            @endif
                                            @if($log->billing_date)
                                                · {{ $log->billing_date->format('d/m') }}
                                            @endif
                                        </td>
                                        <td style="padding:8px 10px; font-size:13px; color:#0f172a; font-weight:700; text-align:right; border-bottom:1px solid #f1f5f9;">{{ number_format($log->total_amount, 0, ',', '.') }}đ</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </td>
                    </tr>

                    {{-- TOTAL --}}
                    <tr>
                        <td style="padding:0 32px 8px;">
                            <table width="100%" cellpadding="0" cellspacing="0" style="background:linear-gradient(135deg,#4f46e5,#3b82f6); border-radius:10px; overflow:hidden;">
                                <tr>
                                    <td style="padding:16px 20px;">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="font-size:13px; color:rgba(255,255,255,0.8); font-weight:600;">TỔNG CỘNG</td>
                                                <td style="font-size:20px; color:#ffffff; font-weight:800; text-align:right;">{{ number_format($totalAmount, 0, ',', '.') }}đ</td>
                                            </tr>
                                            @if($booking->deposit > 0)
                                            <tr>
                                                <td style="font-size:12px; color:rgba(255,255,255,0.7); padding-top:4px;">Đã cọc</td>
                                                <td style="font-size:13px; color:rgba(255,255,255,0.9); font-weight:700; text-align:right; padding-top:4px;">-{{ number_format($booking->deposit, 0, ',', '.') }}đ</td>
                                            </tr>
                                            <tr>
                                                <td style="font-size:13px; color:#ffffff; font-weight:700; padding-top:6px; border-top:1px solid rgba(255,255,255,0.2);">CÒN PHẢI THU</td>
                                                <td style="font-size:18px; color:#fbbf24; font-weight:800; text-align:right; padding-top:6px; border-top:1px solid rgba(255,255,255,0.2);">{{ number_format($totalAmount - $booking->deposit, 0, ',', '.') }}đ</td>
                                            </tr>
                                            @endif
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- FOOTER --}}
                    <tr>
                        <td style="padding:16px 32px 24px; text-align:center;">
                            <p style="margin:0; font-size:12px; color:#94a3b8;">Cảm ơn quý khách đã sử dụng dịch vụ.</p>
                            <p style="margin:4px 0 0; font-size:11px; color:#cbd5e1;">Email này được gửi tự động từ hệ thống quản lý.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
