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
                        <td style="background:linear-gradient(135deg,#4f46e5,#3b82f6); padding:24px 32px;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td width="80" align="left">
                                        @if(file_exists(public_path('logo.jpg')))
                                            <img src="{{ $message->embed(public_path('logo.jpg')) }}" alt="Logo" style="width:80px; height:80px; border-radius:12px; object-fit:cover;">
                                        @elseif(file_exists(public_path('sala.png')))
                                            <img src="{{ $message->embed(public_path('sala.png')) }}" alt="Logo" style="width:80px; height:80px; border-radius:12px; object-fit:cover;">
                                        @endif
                                    </td>
                                    <td align="center" style="padding:0 20px;">
                                        <h1 style="margin:0; color:#ffffff; font-size:22px; font-weight:800; letter-spacing:1px; text-transform:uppercase;">HOÁ ĐƠN THANH TOÁN</h1>
                                        <p style="margin:6px 0 0; color:rgba(255,255,255,0.85); font-size:13px;">{{ now()->format('d/m/Y H:i') }}</p>
                                    </td>
                                    <td width="80" align="right">
                                        @if(file_exists(public_path('qr.jpg')))
                                            <img src="{{ $message->embed(public_path('qr.jpg')) }}" alt="QR" style="width:80px; height:80px; border-radius:12px; background:#ffffff; padding:5px;">
                                        @elseif(file_exists(public_path('qr.png')))
                                            <img src="{{ $message->embed(public_path('qr.png')) }}" alt="QR" style="width:80px; height:80px; border-radius:12px; background:#ffffff; padding:5px;">
                                        @endif
                                    </td>
                                </tr>
                            </table>
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
                                                <td style="padding:4px 0; font-size:13px; color:#0f172a; font-weight:700; text-align:right;">{{ $booking->customer->name ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:4px 0; font-size:13px; color:#64748b;">Phòng:</td>
                                                <td style="padding:4px 0; font-size:13px; color:#0f172a; font-weight:700; text-align:right;">{{ $booking->room->code ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:4px 0; font-size:13px; color:#64748b;">Ngày vào:</td>
                                                <td style="padding:4px 0; font-size:13px; color:#0f172a; font-weight:700; text-align:right;">{{ $booking->check_in ? $booking->check_in->format('d/m/Y') : '-' }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- DETAIL TABLE --}}
                    <tr>
                        <td style="padding:0 32px 16px;">
                            <p style="margin:0 0 10px; font-size:13px; font-weight:800; color:#0f172a; text-transform:uppercase; letter-spacing:1px;">Chi tiết dịch vụ</p>
                            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                <thead>
                                    <tr style="background:#f1f5f9;">
                                        <th style="padding:8px 10px; font-size:11px; color:#64748b; text-align:left; text-transform:uppercase; font-weight:700; border-bottom:2px solid #e2e8f0;">Dịch vụ</th>
                                        <th style="padding:8px 10px; font-size:11px; color:#64748b; text-align:center; text-transform:uppercase; font-weight:700; border-bottom:2px solid #e2e8f0;">Chỉ số / Chi tiết</th>
                                        <th style="padding:8px 10px; font-size:11px; color:#64748b; text-align:right; text-transform:uppercase; font-weight:700; border-bottom:2px solid #e2e8f0;">Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Room base price --}}
                                    <tr>
                                        <td style="padding:10px 10px; font-size:13px; color:#0f172a; font-weight:700; border-bottom:1px solid #f1f5f9;">Tiền phòng</td>
                                        <td style="padding:10px 10px; font-size:12px; color:#64748b; text-align:center; border-bottom:1px solid #f1f5f9;">
                                            {{ $booking->price_type === 'month' ? ($periodLabel ? 'Tháng ' . $periodLabel : 'Hợp đồng / tháng') : 'Ngày' }}
                                        </td>
                                        <td style="padding:10px 10px; font-size:13px; color:#0f172a; font-weight:800; text-align:right; border-bottom:1px solid #f1f5f9;">{{ number_format($roomPrice, 0, ',', '.') }}đ</td>
                                    </tr>

                                    {{-- Usage logs --}}
                                    @foreach($usageLogs as $log)
                                    <tr>
                                        <td style="padding:10px 10px; font-size:13px; color:#0f172a; font-weight:600; border-bottom:1px solid #f1f5f9;">
                                            {{ $log->service->name ?? ($log->notes ?: 'Phí phụ thu khác') }}
                                        </td>
                                        <td style="padding:10px 10px; font-size:12px; color:#64748b; text-align:center; border-bottom:1px solid #f1f5f9;">
                                            @if($log->type === 'meter')
                                                <span style="font-weight:700; color:#3b82f6;">{{ $log->start_index }} → {{ $log->end_index }}</span>
                                                <div style="font-size:10px; margin-top:2px;">(Sử dụng: {{ $log->end_index - $log->start_index }})</div>
                                            @elseif($log->type === 'manual')
                                                Phụ thu
                                            @else
                                                SL: {{ $log->quantity }}
                                            @endif
                                            @if($log->billing_date)
                                                · <span style="font-size:10px;">{{ $log->billing_date->format('d/m') }}</span>
                                            @endif
                                        </td>
                                        <td style="padding:10px 10px; font-size:13px; color:#0f172a; font-weight:800; text-align:right; border-bottom:1px solid #f1f5f9;">{{ number_format($log->total_amount, 0, ',', '.') }}đ</td>
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
                                                <td style="font-size:13px; color:rgba(255,255,255,0.8); font-weight:600;">TỔNG CỘNG HÓA ĐƠN</td>
                                                <td style="font-size:22px; color:#ffffff; font-weight:900; text-align:right;">{{ number_format($totalAmount, 0, ',', '.') }}đ</td>
                                            </tr>
                                            @if($totalDeposit > 0 && !$booking->is_contract)
                                            <tr>
                                                <td style="font-size:12px; color:rgba(255,255,255,0.7); padding-top:6px;">Đã cọc / thanh toán trước</td>
                                                <td style="font-size:13px; color:rgba(255,255,255,0.9); font-weight:700; text-align:right; padding-top:6px;">-{{ number_format($totalDeposit, 0, ',', '.') }}đ</td>
                                            </tr>
                                            <tr>
                                                <td style="font-size:14px; color:#ffffff; font-weight:800; padding-top:8px; border-top:1px solid rgba(255,255,255,0.2);">CÒN LẠI PHẢI THU</td>
                                                <td style="font-size:20px; color:#fbbf24; font-weight:900; text-align:right; padding-top:8px; border-top:1px solid rgba(255,255,255,0.2);">{{ number_format($totalAmount - $totalDeposit, 0, ',', '.') }}đ</td>
                                            </tr>
                                            @elseif($booking->is_contract)
                                            <tr>
                                                <td style="font-size:12px; color:rgba(255,255,255,0.7); padding-top:6px;">Tiền cọc (Khoản ngoài)</td>
                                                <td style="font-size:13px; color:rgba(255,255,255,0.9); font-weight:700; text-align:right; padding-top:6px;">{{ number_format($totalDeposit, 0, ',', '.') }}đ</td>
                                            </tr>
                                            <tr>
                                                <td style="font-size:14px; color:#ffffff; font-weight:800; padding-top:8px; border-top:1px solid rgba(255,255,255,0.2);">TỔNG THANH TOÁN KỲ NÀY</td>
                                                <td style="font-size:20px; color:#fbbf24; font-weight:900; text-align:right; padding-top:8px; border-top:1px solid rgba(255,255,255,0.2);">{{ number_format($totalAmount, 0, ',', '.') }}đ</td>
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
                            <p style="margin:0; font-size:12px; color:#94a3b8; font-weight:600;">Trân trọng cảm ơn quý khách!</p>
                            <p style="margin:4px 0 0; font-size:11px; color:#cbd5e1;">Hoá đơn được khởi tạo tự động bởi hệ thống Sala.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
