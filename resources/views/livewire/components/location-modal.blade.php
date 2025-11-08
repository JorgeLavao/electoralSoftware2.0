<div x-data="{ show: @entangle('showModal'),
    init() {
        this.$watch('show', (value) => {
            if (value) {
                document.body.classList.add('modal-open');
            } else {
                document.body.classList.remove('modal-open');
            }
        });
    }}">
    <!-- Modal con transiciones -->
    <div x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        :class="{ 'show': show }" class="modal-container" tabindex="-1" @click="show = false">
        <div class="modal-inner modal-md" @click.stop>
            <button type="button" class="button modal-close" @click="show = false">
                <x-icons.close/>
            </button>
            <div class="modal-inner__data space-y-5">
                <header class="section-header">
                    <div class="section-header__title">
                        <hgroup>
                            <h3 class="text-grey-400">Ubicación</h3>
                        </hgroup>
                    </div>
                    <hr>
                </header>
                <div class="md:grid md:grid-cols-5 space-y-2 md:space-y-0">
                    <div class="col-span-2">
                        <button type="button" class="btn-secondary w-full md:w-auto" id="btn-actual">
                            <x-icons.actual-location/>
                            Ubicación actual
                        </button>
                    </div>
                    <div class="col-span-3">
                        <div class="flex flex-col md:flex-row gap-2">
                            <input id="buscar" type="text" placeholder="Digite la Ubicación a Buscar" class="..." />
                        </div>
                    </div>
                </div>
                {{-- google maps --}}
                <div id="map" wire:ignore class="w-full rounded-2xl" style="height:320px;"></div>
                <div class="grop-columns-2">
                    <div class="container-v">
                        <div class="group-form-v">
                            <label for="lat">Latitud<span class="text-red-500">*</span></label>
                            <input type="text" id="lat" placeholder="Digite la Latitud">
                        </div>
                    </div>
                    <div class="container-v">
                        <div class="group-form-v">
                            <label for="lng">Longitud<span class="text-red-500">*</span></label>
                            <input type="text" id="lng" placeholder="Digite la Longitud">
                        </div>
                    </div>
                </div>
                <hr/>
                <div class="justify-between flex w-full">
                    <button type="button" class="btn-secondary">
                        <x-icons.close/> Cancelar
                    </button>
                    <button type="button" class="btn-primary" wire:click='saveAdrress'>
                        <x-icons.save/> Guardar
                    </button>
                </div>
            </div>
        </div>
        @push('scripts')
            <script type="module" src="{{ Vite::asset('resources/js/google-maps.js') }}"></script>
        @endpush
    </div>
</div>
