@include('components.card-statistic', [
    'icon' => $icon ?? 'bar-chart-2',
    'variant' => $variant ?? ($color ?? 'neutral'),
    'label' => $label,
    'value' => $value,
    'description' => $description ?? null,
    'unavailable' => $unavailable ?? false,
])
