<?php

namespace App\Livewire\Admin\ActivityLogs;

use App\Models\ActivityLog;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $userId;
    public $action;
    public $dateFrom;
    public $dateTo;

    protected $queryString = ['search', 'userId', 'action', 'dateFrom', 'dateTo'];

    public function render()
    {
        $logs = ActivityLog::query()
            ->with('user')
            ->when($this->userId, fn($q) => $q->where('user_id', $this->userId))
            ->when($this->action, fn($q) => $q->where('action', $this->action))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->latest()
            ->paginate(20);

        $users = User::orderBy('name')->get();

        return view('livewire.admin.activity-logs.index', [
            'logs' => $logs,
            'users' => $users,
        ])->layout('components.layouts.admin');
    }

    public function resetFilters()
    {
        $this->reset(['userId', 'action', 'dateFrom', 'dateTo', 'search']);
    }
}
