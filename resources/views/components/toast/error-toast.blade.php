<div x-data="{ show: true }" x-show="show" x-transition wire:ignore.self class="bg-white rounded-lg shadow-md p-2 mx-auto border border-red-200">
    <div class="flex items-center justify-between">
        <div class="flex items-center">
            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mr-3">
                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <span class="text-gray-700 text-sm">{{$message}}</span>
        </div>
        <button type="button" @click.prevent.stop="show = false" class="text-gray-400 hover:text-gray-600 ml-2"
            aria-label="Cerrar notificacion">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>
