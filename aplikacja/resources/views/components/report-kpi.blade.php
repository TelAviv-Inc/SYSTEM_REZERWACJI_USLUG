{{-- One summary tile: value, change against the previous period (green when it goes the good way) and a hint. --}}
@props(['kpi'])

@php($good = $kpi['change'] === null || $kpi['change'] == 0 ? null : $kpi['change'] > 0 === $kpi['goodUp'])

<div class="flex min-w-0 flex-col gap-1 rounded-xl border border-brand-border bg-white p-3 sm:p-4">
    <span class="flex items-center gap-2 text-xs font-medium text-brand-muted">
        <i class="{{ $kpi['icon'] }}"></i> {{ $kpi['label'] }}
    </span>
    <span class="text-lg font-bold text-brand-navy sm:text-2xl break-words">{{ $kpi['value'] }}</span>
    <span class="flex flex-wrap items-center gap-x-2 text-xs text-brand-muted">
        @if ($kpi['change'] !== null)
            <span @class([
                'inline-flex items-center gap-1 font-semibold',
                'text-green-700' => $good === true,
                'text-red-700' => $good === false,
                'text-brand-muted' => $good === null,
            ]) title="Zmiana względem poprzedniego okresu">
                <i @class([
                    'fa-solid',
                    'fa-arrow-up' => $kpi['change'] > 0,
                    'fa-arrow-down' => $kpi['change'] < 0,
                    'fa-equals' => $kpi['change'] == 0,
                ])></i>
                {{ ($kpi['change'] > 0 ? '+' : '') . \App\Support\Report::number($kpi['change']) . $kpi['unit'] }}
            </span>
        @endif
        <span>{{ $kpi['hint'] }}</span>
    </span>
</div>
