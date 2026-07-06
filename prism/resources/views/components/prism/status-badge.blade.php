@props(['status' => ''])

@php
    $label = (string) $status;
    $key = str($label)->lower()->replace(' ', '-')->toString();
    $tone = match ($key) {
        'draft' => 'bg-slate-100 text-slate-700 ring-slate-200',
        'submitted' => 'bg-blue-50 text-blue-700 ring-blue-200',
        'under-review' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'endorsed' => 'bg-purple-50 text-purple-700 ring-purple-200',
        'returned' => 'bg-red-50 text-red-700 ring-red-200',
        'approved' => 'bg-green-50 text-green-700 ring-green-200',
        'pending' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'in-progress' => 'bg-blue-50 text-blue-700 ring-blue-200',
        'completed' => 'bg-green-50 text-green-700 ring-green-200',
        'delayed' => 'bg-red-50 text-red-700 ring-red-200',
        'on-track' => 'bg-green-50 text-green-700 ring-green-200',
        'at-risk' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'watch' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'critical' => 'bg-red-50 text-red-700 ring-red-200',
        default => 'bg-slate-100 text-slate-700 ring-slate-200',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex min-h-7 items-center rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {$tone}"]) }}>
    {{ $slot->isEmpty() ? $label : $slot }}
</span>
