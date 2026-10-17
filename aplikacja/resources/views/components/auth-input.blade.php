{{-- Text input with an icon, label and validation error, styled like the login and register forms --}}
@props(['name', 'label', 'icon', 'type' => 'text'])

<div>
    <label for="{{ $name }}" class="mb-1.5 block text-sm font-semibold text-brand-text">{{ $label }}</label>
    <div class="relative">
        <i
            class="fas {{ $icon }} pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-brand-muted"></i>
        <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}"
            {{ $attributes->class([
                'w-full rounded-lg border bg-white py-2.5 pl-10 pr-3 text-sm text-brand-text placeholder:text-brand-muted/60 transition duration-200 focus:outline-none focus:ring-2',
                'border-red-400 focus:border-red-500 focus:ring-red-500/20' => $errors->has($name),
                'border-brand-border focus:border-brand-accent focus:ring-brand-accent/20' => !$errors->has($name),
            ]) }}>
    </div>
    @error($name)
        <p class="mt-1.5 flex items-center gap-1.5 text-sm text-red-600">
            <i class="fas fa-circle-exclamation text-xs"></i>{{ $message }}
        </p>
    @enderror
</div>
