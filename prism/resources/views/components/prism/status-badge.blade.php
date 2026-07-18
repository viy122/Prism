@props(['status' => ''])

@php
    $label = (string) $status;
    $key = str($label)->lower()->replace(' ', '-')->toString();
    [$bg, $fg, $border] = match ($key) {
        'submitted', 'in-progress' => ['#DBEAFE', '#1E40AF', '#BFDBFE'],
        'under-review', 'pending', 'watch', 'at-risk' => ['#FEF3C7', '#92400E', '#FDE68A'],
        'endorsed' => ['#EDE9FE', '#4C1D95', '#DDD6FE'],
        'returned', 'delayed', 'critical' => ['#FEE2E2', '#991B1B', '#FECACA'],
        'approved', 'completed', 'on-track' => ['#DCFCE7', '#166534', '#BBF7D0'],
        default => ['#F1F5F9', '#334155', '#E2E8F0'],
    };
    $style = "display:inline-flex;align-items:center;justify-content:center;height:28px;padding:0 14px;border-radius:999px;font-size:11px;font-weight:700;white-space:nowrap;background:{$bg};color:{$fg};border:1px solid {$border};";
@endphp

<span {{ $attributes->merge(['style' => $style]) }}>
    {{ $slot->isEmpty() ? $label : $slot }}
</span>
