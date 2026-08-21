@props([
    'status',
])

@if ($status)
    <div {{ $attributes->merge(['class' => 'text-success small mb-3']) }}>
        {{ $status }}
    </div>
@endif
