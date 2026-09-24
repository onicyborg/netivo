<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CronLog extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['job', 'status', 'summary', 'started_at', 'finished_at'];

    protected function casts(): array { return ['summary' => 'array', 'started_at' => 'datetime', 'finished_at' => 'datetime']; }
}
