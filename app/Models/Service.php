<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['name', 'speed_mbps', 'price', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['speed_mbps' => 'integer', 'price' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function customers(): HasMany { return $this->hasMany(Customer::class); }
    public function bills(): HasMany { return $this->hasMany(Bill::class); }
    public function upgradesFrom(): HasMany { return $this->hasMany(ServiceUpgradeRequest::class, 'from_service_id'); }
    public function upgradesTo(): HasMany { return $this->hasMany(ServiceUpgradeRequest::class, 'to_service_id'); }
}
