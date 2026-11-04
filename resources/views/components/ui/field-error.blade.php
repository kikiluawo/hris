@props(['name'])

@error($name)
    <p class="mt-1.5 text-[12.5px] text-rose-600 dark:text-rose-400">{{ $message }}</p>
@enderror
