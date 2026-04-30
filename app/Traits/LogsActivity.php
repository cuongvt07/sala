<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    protected static function booted()
    {
        static::created(function ($model) {
            $model->logActivity('created', null, $model->getAttributes());
        });

        static::updated(function ($model) {
            $oldValues = array_intersect_key($model->getOriginal(), $model->getDirty());
            $newValues = $model->getDirty();
            
            // Don't log if only timestamps changed
            unset($oldValues['updated_at'], $newValues['updated_at']);
            
            if (empty($newValues)) return;

            $model->logActivity('updated', $oldValues, $newValues);
        });

        static::deleted(function ($model) {
            $model->logActivity('deleted', $model->getAttributes(), null);
        });
    }

    protected function logActivity($action, $oldValues, $newValues)
    {
        // Don't log if no one is authenticated (e.g. seeders, unless we want to)
        // But for this project, let's log even if system does it (user_id will be null)
        
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => get_class($this),
            'model_id' => $this->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
