@php
    $valueIsUnavailable = ($unavailable ?? false) || $value === null;
    $displayValue = $valueIsUnavailable ? '—' : $value;
@endphp
<div class="col-12 col-sm-6 col-lg-4 col-xl-3 mb-4 d-flex">
    <article class="card dashboard-kpi dashboard-kpi--{{ $variant ?? 'neutral' }} h-100 w-100">
        <div class="card-body">
            <div class="dashboard-kpi__icon" aria-hidden="true"><i data-feather="{{ $icon }}"></i></div>
            <div class="dashboard-kpi__copy">
                <div class="dashboard-kpi__label">{{ $label }}</div>
                <div class="dashboard-kpi__value">{{ $displayValue }}</div>
                <div class="dashboard-kpi__description">{{ $valueIsUnavailable ? 'Belum ada data' : ($description ?? '') }}</div>
            </div>
        </div>
    </article>
</div>
