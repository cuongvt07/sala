<?php

namespace App\Livewire\Admin\Finance;

use App\Models\Area;
use App\Models\Transaction;
use Carbon\Carbon;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Sổ thu chi: liệt kê mọi khoản đã xác nhận theo khoảng ngày, cột Thu / Chi và tổng kết.
 */
class Index extends Component
{
    use WithPagination;

    #[Url]
    public $from;
    #[Url]
    public $to;
    #[Url(except: '')]
    public $type = '';
    #[Url(except: '')]
    public $method = '';
    #[Url(except: '')]
    public $area = '';
    #[Url(except: '')]
    public $search = '';

    // Form thêm phiếu
    public bool $showForm = false;
    public $f_type = 'expense';
    public $f_category = 'Khác';
    public $f_amount = '';
    public $f_method = 'cash';
    public $f_date;
    public $f_area_id = '';
    public $f_room_code = '';
    public $f_description = '';

    protected $listeners = ['area-selected' => '$refresh'];

    public function mount()
    {
        $this->from = $this->from ?: now()->startOfMonth()->toDateString();
        $this->to = $this->to ?: now()->endOfMonth()->toDateString();
    }

    public function updating($name)
    {
        if (in_array($name, ['from', 'to', 'type', 'method', 'area', 'search'])) {
            $this->resetPage();
        }
    }

    public function setRange($preset)
    {
        $now = now();
        [$this->from, $this->to] = match ($preset) {
            'today' => [$now->toDateString(), $now->toDateString()],
            'last_month' => [$now->copy()->subMonthNoOverflow()->startOfMonth()->toDateString(), $now->copy()->subMonthNoOverflow()->endOfMonth()->toDateString()],
            'year' => [$now->copy()->startOfYear()->toDateString(), $now->copy()->endOfYear()->toDateString()],
            default => [$now->copy()->startOfMonth()->toDateString(), $now->copy()->endOfMonth()->toDateString()],
        };
        $this->resetPage();
    }

    protected function areaScope()
    {
        $user = auth()->user();
        if ($user->isAreaRestricted()) {
            return $user->area_id;
        }
        return $this->area ?: session('admin_selected_area_id');
    }

    protected function baseQuery()
    {
        $areaId = $this->areaScope();
        $from = $this->safeDate($this->from) ?? now()->startOfMonth()->toDateString();
        $to = $this->safeDate($this->to) ?? now()->endOfMonth()->toDateString();

        return Transaction::query()
            ->whereBetween('txn_date', [$from, $to])
            ->when($areaId, fn ($q) => $q->where('area_id', $areaId))
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->when($this->method, fn ($q) => $q->where('method', $this->method))
            ->when(trim($this->search) !== '', function ($q) {
                $term = '%' . trim($this->search) . '%';
                $q->where(fn ($s) => $s->where('description', 'like', $term)
                    ->orWhere('category', 'like', $term)
                    ->orWhere('room_code', 'like', $term)
                    ->orWhere('customer_name', 'like', $term));
            });
    }

    protected function safeDate($value): ?string
    {
        try {
            return $value ? Carbon::parse($value)->toDateString() : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    // ---------------- Thêm / xoá phiếu ----------------

    public function openForm($type = 'expense')
    {
        $this->resetValidation();
        $this->f_type = $type === 'income' ? 'income' : 'expense';
        $this->f_category = 'Khác';
        $this->f_amount = '';
        $this->f_method = 'cash';
        $this->f_date = now()->toDateString();
        $this->f_area_id = auth()->user()->isAreaRestricted() ? auth()->user()->area_id : (session('admin_selected_area_id') ?: '');
        $this->f_room_code = '';
        $this->f_description = '';
        $this->showForm = true;
    }

    public function updatedFType()
    {
        $this->f_category = 'Khác';
    }

    public function saveForm()
    {
        $amount = (float) preg_replace('/[^0-9]/', '', (string) $this->f_amount);
        $this->validate([
            'f_type' => 'required|in:income,expense',
            'f_category' => 'required|string|max:50',
            'f_method' => 'required|in:cash,transfer',
            'f_date' => 'required|date',
            'f_description' => 'nullable|string|max:255',
            'f_room_code' => 'nullable|string|max:50',
        ]);
        if ($amount <= 0) {
            $this->addError('f_amount', 'Nhập số tiền.');
            return;
        }

        $areaId = auth()->user()->isAreaRestricted() ? auth()->user()->area_id : ($this->f_area_id ?: null);

        Transaction::create([
            'type' => $this->f_type,
            'category' => $this->f_category,
            'amount' => $amount,
            'method' => $this->f_method,
            'txn_date' => $this->f_date,
            'description' => $this->f_description ?: null,
            'area_id' => $areaId,
            'room_code' => $this->f_room_code ?: null,
            'user_id' => auth()->id(),
        ]);

        $this->showForm = false;
        $this->dispatch('toast', message: 'Đã ghi phiếu ' . ($this->f_type === 'income' ? 'thu' : 'chi') . '.', type: 'success');
    }

    public function deleteTxn($id)
    {
        if (!auth()->user()->isAdminLevel()) {
            $this->dispatch('toast', message: 'Chỉ quản lý mới được xoá phiếu.', type: 'error');
            return;
        }
        Transaction::whereKey($id)->delete();
        $this->dispatch('toast', message: 'Đã xoá phiếu.', type: 'success');
    }

    // ---------------- Xuất Excel (CSV) ----------------

    public function export()
    {
        $rows = $this->baseQuery()->orderBy('txn_date')->orderBy('id')->get();
        $filename = 'thu-chi_' . $this->from . '_' . $this->to . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM để Excel đọc đúng tiếng Việt
            fputcsv($out, ['Ngày', 'Khoản', 'Nội dung', 'Phòng', 'Khách', 'Hình thức', 'Thu', 'Chi']);
            $in = 0;
            $ex = 0;
            foreach ($rows as $t) {
                $isIn = $t->type === Transaction::INCOME;
                $isIn ? $in += $t->amount : $ex += $t->amount;
                fputcsv($out, [
                    $t->txn_date->format('d/m/Y'), $t->category, $t->description, $t->room_code, $t->customer_name,
                    $t->method_label, $isIn ? $t->amount : '', $isIn ? '' : $t->amount,
                ]);
            }
            fputcsv($out, []);
            fputcsv($out, ['', '', '', '', '', 'TỔNG', $in, $ex]);
            fputcsv($out, ['', '', '', '', '', 'CÒN LẠI', $in - $ex, '']);
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function render()
    {
        // Tổng kết bằng 1 câu SQL gộp (không tải toàn bộ dòng)
        $totals = $this->baseQuery()
            ->selectRaw("type, method, SUM(amount) as total, COUNT(*) as cnt")
            ->groupBy('type', 'method')
            ->get();

        $sum = fn ($type, $method = null) => (float) $totals
            ->where('type', $type)
            ->when($method, fn ($c) => $c->where('method', $method))
            ->sum('total');

        $summary = [
            'income' => $sum('income'),
            'expense' => $sum('expense'),
            'income_cash' => $sum('income', 'cash'),
            'income_transfer' => $sum('income', 'transfer'),
            'expense_cash' => $sum('expense', 'cash'),
            'expense_transfer' => $sum('expense', 'transfer'),
            'count' => (int) $totals->sum('cnt'),
        ];

        $byCategory = $this->baseQuery()
            ->selectRaw('type, category, SUM(amount) as total')
            ->groupBy('type', 'category')
            ->orderByDesc('total')
            ->get();

        return view('livewire.admin.finance.index', [
            'rows' => $this->baseQuery()->with('user:id,name')->orderByDesc('txn_date')->orderByDesc('id')->paginate(50),
            'summary' => $summary,
            'byCategory' => $byCategory,
            'areas' => auth()->user()->isAreaRestricted() ? collect() : Area::select('id', 'name')->orderBy('name')->get(),
        ])->layout('components.layouts.admin');
    }
}
