<?php

namespace App\Livewire\Admin\Bookings;

use App\Models\Area;
use App\Models\Booking;
use App\Models\Transaction;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Danh sách đặt phòng (khách sắp tới + đang ở).
 * Thêm/sửa/thu tiền dùng chung component BookingForm (giống hệt Lịch đặt phòng).
 */
class Index extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public $search = '';
    #[Url(except: '')]
    public $filterStatus = '';
    #[Url(except: '')]
    public $filterType = '';
    #[Url(except: '')]
    public $filterArea = '';

    protected $listeners = ['area-selected' => '$refresh', 'booking-saved' => '$refresh'];

    public function updating($name)
    {
        if (in_array($name, ['search', 'filterStatus', 'filterType', 'filterArea'])) {
            $this->resetPage();
        }
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterStatus', 'filterType', 'filterArea']);
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();
        $areaId = $user->isAreaRestricted()
            ? $user->area_id
            : ($this->filterArea ?: session('admin_selected_area_id'));

        $query = Booking::query()
            ->select('bookings.*')
            ->join('rooms', 'rooms.id', '=', 'bookings.room_id')
            ->with([
                'customer:id,name,phone,nationality',
                'room:id,code,area_id',
                'room.area:id,name',
                'transactions:id,booking_id,type,category,amount,period_due',
            ])
            ->withSum('usageLogs as services_total', 'total_amount')
            ->whereIn('bookings.status', Booking::ACTIVE_STATUSES)
            ->when($areaId, fn ($q) => $q->where('rooms.area_id', $areaId))
            ->when($this->filterStatus, fn ($q) => $q->where('bookings.status', $this->filterStatus))
            ->when($this->filterType, fn ($q) => $q->where('bookings.price_type', $this->filterType))
            ->when(trim($this->search) !== '', function ($q) {
                $term = '%' . trim($this->search) . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('rooms.code', 'like', $term)
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', $term)->orWhere('phone', 'like', $term));
                });
            })
            // Khách đang ở trước (theo số phòng), rồi khách sắp tới (theo ngày nhận gần nhất)
            ->orderByRaw("CASE WHEN bookings.status = 'checked_in' THEN 0 ELSE 1 END")
            ->orderByRaw("CASE WHEN bookings.status = 'checked_in' THEN rooms.code END ASC")
            ->orderBy('bookings.check_in');

        $bookings = $query->paginate(20);

        // Tình trạng thanh toán từng dòng (tính trên dữ liệu đã eager-load, không query thêm)
        $until = now()->addDays(3)->endOfDay();
        foreach ($bookings as $b) {
            if ($b->isContract()) {
                $unpaid = $b->unpaidContractDues($until);
                $b->pay_status = $unpaid
                    ? ['color' => $unpaid[0]->isPast() && !$unpaid[0]->isToday() ? 'red' : 'amber', 'text' => (count($unpaid) > 1 ? count($unpaid) . ' kỳ chưa thu · ' : 'Chưa thu kỳ ') . $unpaid[0]->format('d/m')]
                    : ['color' => 'green', 'text' => 'Đã thu đủ kỳ'];
            } else {
                $paid = $b->transactions->where('type', Transaction::INCOME)->sum('amount')
                    - $b->transactions->where('type', Transaction::EXPENSE)->sum('amount');
                $due = (float) $b->price + (float) $b->services_total;
                $b->paid_amount = $paid;
                $b->due_amount = $due;
                $b->pay_status = $paid >= $due && $due > 0
                    ? ['color' => 'green', 'text' => 'Đã thu đủ']
                    : ['color' => $paid > 0 ? 'amber' : 'gray', 'text' => 'Còn ' . number_format($due - $paid, 0, ',', '.') . 'đ'];
            }
        }

        return view('livewire.admin.bookings.index', [
            'bookings' => $bookings,
            'areas' => $user->isAreaRestricted() ? collect() : Area::select('id', 'name')->orderBy('name')->get(),
        ])->layout('components.layouts.admin');
    }
}
