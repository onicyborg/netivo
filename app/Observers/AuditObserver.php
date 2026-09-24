<?php

namespace App\Observers;

use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use WeakMap;

class AuditObserver
{
    /** @var WeakMap<Model, array<string, mixed>> */
    private WeakMap $originals;

    public function __construct(private readonly AuditLogger $logger) {}

    public function updating(Model $model): void
    {
        $this->originals ??= new WeakMap;
        $this->originals[$model] = $model->getRawOriginal();
    }

    public function created(Model $model): void
    {
        $this->logger->model($model, 'created', [], $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $this->originals ??= new WeakMap;
        $newValues = $model->getChanges();
        $originalValues = $this->originals[$model] ?? $model->getRawOriginal();
        $this->logger->model($model, 'updated', array_intersect_key($originalValues, $newValues), $newValues);
        unset($this->originals[$model]);
    }

    public function deleted(Model $model): void
    {
        $this->logger->model($model, 'deleted', $model->getAttributes(), []);
    }
}
