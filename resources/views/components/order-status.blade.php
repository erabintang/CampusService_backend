@props(['status' => 'pending'])

@php
    $badge = [
        'pending' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'processing' => 'bg-blue-50 text-blue-700 ring-blue-200',
        'completed' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'cancelled' => 'bg-rose-50 text-rose-700 ring-rose-200',
    ][$status] ?? 'bg-gray-100 text-gray-600 ring-gray-200';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium capitalize ring-1 '.$badge]) }}>
    {{ $status }}
</span>
