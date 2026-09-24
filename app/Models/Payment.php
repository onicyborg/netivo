<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['bill_id', 'payment_method_id', 'amount', 'paid_date', 'sender_name', 'note', 'proof_path', 'status', 'rejection_reason', 'verified_by', 'verified_at'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'paid_date' => 'date', 'status' => PaymentStatus::class, 'verified_at' => 'datetime'];
    }

    public function bill(): BelongsTo { return $this->belongsTo(Bill::class); }
    public function paymentMethod(): BelongsTo { return $this->belongsTo(PaymentMethod::class); }
    public function verifier(): BelongsTo { return $this->belongsTo(User::class, 'verified_by'); }
    public function receipt(): HasOne { return $this->hasOne(Receipt::class); }
}
