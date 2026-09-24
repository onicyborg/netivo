<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Receipt extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['payment_id', 'receipt_number', 'issued_at'];

    protected function casts(): array { return ['issued_at' => 'datetime']; }
    public function payment(): BelongsTo { return $this->belongsTo(Payment::class); }
}
