<?php

namespace App\Services;

use App\Models\SystemLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public function log(string $table, ?string $recordId, string $action, array $oldValues = [], array $newValues = [], ?User $user = null): SystemLog
    {
        $request = app()->bound('request') ? request() : null;

        return SystemLog::create([
            'user_id' => $user?->id ?? Auth::id(),
            'table_name' => $table,
            'record_id' => $recordId,
            'action' => $action,
            'method' => $request?->method() ?? 'CLI',
            'url' => $request?->url() ?? 'console',
            'ip_address' => $request?->ip(),
            'old_values' => $this->sanitize($oldValues),
            'new_values' => $this->sanitize($newValues),
        ]);
    }

    public function model(Model $model, string $action, array $oldValues = [], array $newValues = []): SystemLog
    {
        return $this->log($model->getTable(), $model->getKey() ? (string) $model->getKey() : null, $action, $oldValues, $newValues);
    }

    /** @return array<string, mixed> */
    public function changed(Model $model): array
    {
        $changes = $model->getChanges();

        return [
            'old' => array_intersect_key($model->getRawOriginal(), $changes),
            'new' => $changes,
        ];
    }

    private function sanitize(mixed $value, ?string $key = null): mixed
    {
        if ($value instanceof UploadedFile) {
            return '[data biner disembunyikan]';
        }
        if (is_array($value)) {
            $result = [];
            foreach ($value as $itemKey => $item) {
                $result[$itemKey] = $this->sanitize($item, (string) $itemKey);
            }

            return $result;
        }
        if ($key !== null && preg_match('/password|token|secret|authorization|api.?key|remember/i', $key)) {
            return '[disembunyikan]';
        }
        if (is_string($value) && preg_match('//u', $value) !== 1) {
            return '[data biner disembunyikan]';
        }

        return $value;
    }
}
