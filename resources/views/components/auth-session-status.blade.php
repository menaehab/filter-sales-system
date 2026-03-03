@props(['status'])

@if($status)
    <div {{ $attributes->merge(['class' => 'rounded-lg bg-emerald-50 p-4 text-sm text-emerald-700']) }}>
        {{ $status }}
    </div>
@endif
