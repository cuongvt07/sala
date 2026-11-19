<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Booking extends Model
{
    use HasFactory, LogsActivity;

    /** Trạng thái còn "chiếm phòng" (khách sắp tới + khách đang ở). */
    public const ACTIVE_STATUSES = ['pending', 'checked_in'];

    public const STATUS_LABELS = [
        'pending' => 'Chờ nhận phòng',
        'checked_in' => 'Đang ở',
        'checked_out' => 'Đã trả phòng',
        'cancelled' => 'Đã huỷ',
    ];

    protected $fillable = ['customer_id', 'room_id', 'check_in', 'check_out', 'price_type', 'is_contract', 'unit_price', 'price', 'deposit', 'deposit_2', 'deposit_3', 'deposit_usd', 'deposit_2_usd', 'usd_rate', 'status', 'notes', 'source', 'additional_guests'];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'additional_guests' => 'array',
        'is_contract' => 'boolean',
        'deposit_usd' => 'float',
        'deposit_2_usd' => 'float',
        'usd_rate' => 'float',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class)
            ->withPivot(['quantity', 'start_index', 'end_index', 'usage', 'unit_price', 'total_amount', 'note'])
            ->withTimestamps();
    }

    public function usageLogs()
    {
        return $this->hasMany(BookingUsageLog::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function isContract(): bool
    {
        return $this->price_type === 'month' || (bool) $this->is_contract;
    }

    /**
     * Ngày đến hạn đóng tiền của hợp đồng: đúng NGÀY NÀY các tháng sau (không cộng 30 ngày).
     * VD nhận phòng 15/01 -> 15/01, 15/02, 15/03... Nhận 31/01 -> 31/01, 28/02 (29/02), 31/03...
     * Luôn tính từ ngày nhận gốc nên không bị "trôi" ngày sau các tháng ngắn.
     *
     * @return Carbon[]
     */
    public function contractDueDates(Carbon $until): array
    {
        if (!$this->isContract() || !$this->check_in) {
            return [];
        }

        $start = $this->check_in->copy()->startOfDay();
        $end = $this->check_out ? $this->check_out->copy()->startOfDay() : null;

        $dates = [];
        for ($n = 0; $n < 240; $n++) {
            $due = $start->copy()->addMonthsNoOverflow($n);
            if ($due->gt($until)) {
                break;
            }
            // Ngày trả phòng trùng/trước ngày đến hạn -> không phát sinh kỳ mới
            if ($end && $due->gte($end)) {
                break;
            }
            $dates[] = $due;
        }

        return $dates;
    }

    /**
     * Các kỳ hợp đồng CHƯA thu tiền, đến hạn trước hoặc bằng $until.
     * Yêu cầu đã eager-load quan hệ transactions để tránh N+1.
     *
     * @return Carbon[]
     */
    public function unpaidContractDues(Carbon $until): array
    {
        $paid = $this->transactions
            ->where('type', Transaction::INCOME)
            ->whereNotNull('period_due')
            ->map(fn ($t) => $t->period_due->toDateString())
            ->all();

        return array_values(array_filter(
            $this->contractDueDates($until),
            fn (Carbon $d) => !in_array($d->toDateString(), $paid, true)
        ));
    }
}
