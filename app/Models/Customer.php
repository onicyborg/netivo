<?php

namespace App\Models;

use App\Enums\CustomerStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['user_id', 'service_id', 'customer_number', 'phone', 'address', 'registered_at', 'status'];

    protected function casts(): array
    {
        return ['registered_at' => 'date', 'status' => CustomerStatus::class];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function service(): BelongsTo { return $this->belongsTo(Service::class); }
    public function bills(): HasMany { return $this->hasMany(Bill::class); }
    public function serviceUpgradeRequests(): HasMany { return $this->hasMany(ServiceUpgradeRequest::class); }
}
