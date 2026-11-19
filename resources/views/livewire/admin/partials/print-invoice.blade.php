{{-- Hoá đơn tiền phòng. Dữ liệu: $d (BookingForm::printInvoice). In bằng window.print() — CSS .print-area ở layout. --}}
@php $m = fn($v) => number_format((float) $v, 0, ',', '.'); @endphp
<div class="print-area p-8 sm:p-10" style="font-family: 'Times New Roman', Times, serif;">
    <div class="flex justify-between items-start mb-6 pb-4 border-b-2 border-gray-800">
        <img src="{{ asset('logo.jpg') }}" alt="Logo" class="w-16 h-16 border-2 border-gray-800 rounded-full object-cover">
        <div class="text-center flex-1 px-4">
            <h1 class="text-sm font-bold uppercase">SALA APARTMENT AND HOTEL ĐÀ NẴNG</h1>
            <p class="text-[11px] mt-1">Số điện thoại: 084 424 4567</p>
            <p class="text-[11px]">Địa chỉ: 22 Lý Nhật Quang, Nại Hiên Đông, Sơn Trà, Đà Nẵng</p>
        </div>
        <img src="{{ asset('qr.jpg') }}" alt="QR" class="w-16 h-16 border border-gray-800 object-contain">
    </div>

    <div class="text-center my-5">
        <h2 class="text-lg font-bold">HOÁ ĐƠN TIỀN PHÒNG {{ !empty($d['label']) ? '- ' . mb_strtoupper($d['label']) : '' }}</h2>
        <div class="text-base font-bold">ROOM BILL</div>
    </div>

    <div class="flex justify-between items-end mb-4 text-sm">
        <p>Kính gửi / Dear <strong>{{ $d['customer_name'] ?? '-' }}</strong></p>
        <div class="border border-gray-800 px-4 py-2 text-right">
            <p><em>Phòng/Room:</em> <strong>{{ $d['room_code'] ?? '-' }}</strong></p>
            <p><em>Check-in:</em> <strong>{{ $d['check_in'] ?? '-' }}</strong>@if(!empty($d['check_out'])) → <strong>{{ $d['check_out'] }}</strong>@endif</p>
        </div>
    </div>

    <table class="w-full border-collapse mb-4 text-xs">
        <thead>
            <tr class="bg-gray-200">
                <th class="border border-gray-800 p-2 text-left">Khoản / Item</th>
                <th class="border border-gray-800 p-2 text-center">Chi tiết / Detail</th>
                <th class="border border-gray-800 p-2 text-right">Thành tiền / Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="border border-gray-800 p-2">{{ $d['room_label'] ?? 'Tiền phòng' }}</td>
                <td class="border border-gray-800 p-2 text-center">-</td>
                <td class="border border-gray-800 p-2 text-right">{{ $m($d['room_price'] ?? 0) }}</td>
            </tr>
            @foreach($d['logs'] ?? [] as $log)
                <tr>
                    <td class="border border-gray-800 p-2">{{ $log['name'] }}</td>
                    <td class="border border-gray-800 p-2 text-center">
                        @if($log['type'] === 'meter')
                            {{ rtrim(rtrim(number_format($log['start'], 2, ',', '.'), '0'), ',') }} → {{ rtrim(rtrim(number_format($log['end'], 2, ',', '.'), '0'), ',') }}
                            = {{ rtrim(rtrim(number_format($log['qty'], 2, ',', '.'), '0'), ',') }} {{ $log['unit'] }} x {{ $m($log['unit_price']) }}
                        @else
                            {{ rtrim(rtrim(number_format($log['qty'], 2, ',', '.'), '0'), ',') }} x {{ $m($log['unit_price']) }}
                        @endif
                    </td>
                    <td class="border border-gray-800 p-2 text-right">{{ $m($log['total']) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-bold">
                <td colspan="2" class="border border-gray-800 p-2 text-right">TỔNG / TOTAL</td>
                <td class="border border-gray-800 p-2 text-right text-sm">{{ $m($d['total'] ?? 0) }} VNĐ</td>
            </tr>
            @if(($d['paid'] ?? 0) > 0)
                <tr>
                    <td colspan="2" class="border border-gray-800 p-2 text-right">Đã thanh toán (gồm cọc) / Paid</td>
                    <td class="border border-gray-800 p-2 text-right">-{{ $m($d['paid']) }}</td>
                </tr>
                <tr class="font-bold">
                    <td colspan="2" class="border border-gray-800 p-2 text-right">CÒN LẠI / BALANCE</td>
                    <td class="border border-gray-800 p-2 text-right text-sm">{{ $m(($d['total'] ?? 0) - $d['paid']) }} VNĐ</td>
                </tr>
            @endif
        </tfoot>
    </table>

    <div class="text-xs mt-6 space-y-1 italic">
        <p>• Nếu có bất kỳ thắc mắc nào, vui lòng liên hệ: 084 424 4567</p>
        <p>• Thank you very much for choosing Sala Apartment and Hotel.</p>
    </div>
</div>
