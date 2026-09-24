<?php

namespace App\Models;

use App\Enums\ReportStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyReport extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['report_date', 'source', 'status', 'total_confirmed_count', 'total_confirmed_amount', 'rejected_count', 'pending_count', 'created_by', 'reviewed_by', 'revision_note', 'sent_at', 'reviewed_at', 'archived_at'];

    protected function casts(): array
    {
        return ['report_date' => 'date', 'status' => ReportStatus::class, 'total_confirmed_count' => 'integer', 'total_confirmed_amount' => 'decimal:2', 'rejected_count' => 'integer', 'pending_count' => 'integer', 'sent_at' => 'datetime', 'reviewed_at' => 'datetime', 'archived_at' => 'datetime'];
    }

    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }
}
