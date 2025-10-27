@props([
    'id' => null,
    'hint' => null,
])

@php
    $wireModel = $attributes->get('wire:model')
        ?? $attributes->get('wire:model.live')
        ?? $attributes->get('wire:model.defer')
        ?? null;
@endphp

<div class="w-full">
    <div class="relative" x-data="{ show: false }">
        <input :type="show ? 'text' : 'password'" {{ $attributes->except(['claattributesss','id'])->merge() }}/>
        <button class="btn-icon" type="button" :aria-label="show ? 'Ocultar contraseña' : 'Mostrar contraseña'"
            :title="show ? 'Ocultar contraseña' : 'Mostrar contraseña'" @click="show = !show">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                <g fill="none">
                    <path stroke="currentColor" stroke-width="2" d="M12 5c-5.444 0-8.469 4.234-9.544 6.116c-.221.386-.331.58-.32.868c.013.288.143.476.402.852C3.818 14.694 7.294 19 12 19s8.182-4.306 9.462-6.164c.26-.376.39-.564.401-.852s-.098-.482-.319-.868C20.47 9.234 17.444 5 12 5Z"/>
                    <circle cx="12" cy="12" r="4" fill="currentColor"/>
                </g>
            </svg>
        </button>
    </div>

    @if($hint)
        <p class="mt-1 text-xs text-gray-500">{{ $hint }}</p>
    @endif
</div>
