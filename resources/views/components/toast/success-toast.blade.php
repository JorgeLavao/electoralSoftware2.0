<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 60000)" x-transition
    class="bg-white rounded-lg shadow-md p-2 border border-green-200 mt-2">
    <div class="flex items-center justify-between">
        <div class="flex items-center">
            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <span class="text-gray-700 text-sm">{{ $message }}</span>
        </div>
        <button @click="show = false" class="text-gray-400 hover:text-gray-600 ml-2"
            aria-label="Cerrar notificación">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>
