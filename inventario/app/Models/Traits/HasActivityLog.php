<?php

namespace App\Models\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;

trait HasActivityLog
{
    public static function bootHasActivityLog(): void
    {
        static::created(function ($model) {
            $model->recordActivity('created', $model->getAttributes());
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();
            unset($changes['updated_at']);
            if (!empty($changes)) {
                $model->recordActivity('updated', $changes);
            }
        });
    }

    public function recordActivity(string $action, array $changes = []): void
    {
        $this->activityLogs()->create([
            'user_id' => Auth::id(),
            'action' => $action,
            'changes' => $changes ?: null,
        ]);
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }
}
