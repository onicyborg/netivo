<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SettingService
{
    private const CACHE_KEY = 'netivo.settings';

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    /** @return array<string, string> */
    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addHour(), function (): array {
            return Setting::query()->pluck('value', 'key')->all();
        });
    }

    /** @param array<string, string|int> $values */
    public function updateMany(array $values): void
    {
        DB::transaction(function () use ($values): void {
            foreach ($values as $key => $value) {
                Setting::updateOrCreate(['key' => $key], ['value' => (string) $value]);
            }
        });

        Cache::forget(self::CACHE_KEY);
    }
}
