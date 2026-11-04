@props(['name', 'label' => null, 'checked' => false])

<label class="inline-flex cursor-pointer items-center gap-2.5 text-[13.5px] text-muted">
    <input
        type="checkbox"
        name="{{ $name }}"
        id="{{ $attributes->get('id', $name) }}"
        value="1"
        @checked(old($name, $checked))
        {{ $attributes->except(['id'])->merge([
            'class' => 'size-4 rounded border-line-strong bg-surface text-brand accent-brand shadow-sm focus:ring-2 focus:ring-brand/25 focus:ring-offset-0',
        ]) }}
    />
    <span>{{ $label ?? $slot }}</span>
</label>
