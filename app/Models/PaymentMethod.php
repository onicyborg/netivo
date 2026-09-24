<?php

namespace App\Models;

use App\Enums\PaymentMethodType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['type', 'name', 'account_number', 'account_name', 'is_active'];

    protected function casts(): array
    {
        return ['type' => PaymentMethodType::class, 'is_active' => 'boolean'];
    }

    public function payments(): HasMany { return $this->hasMany(Payment::class); }
}
