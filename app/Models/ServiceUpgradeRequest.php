<?php

namespace App\Models;

use App\Enums\UpgradeStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceUpgradeRequest extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['customer_id', 'from_service_id', 'to_service_id', 'status', 'effective_period', 'note', 'reviewed_by', 'reviewed_at'];

    protected function casts(): array { return ['status' => UpgradeStatus::class, 'reviewed_at' => 'datetime']; }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function fromService(): BelongsTo { return $this->belongsTo(Service::class, 'from_service_id'); }
    public function toService(): BelongsTo { return $this->belongsTo(Service::class, 'to_service_id'); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }
}
