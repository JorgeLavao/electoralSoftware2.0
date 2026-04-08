<div>
    {{-- Se muestra solo si la propiedad $showModal es verdadera --}}
    @if($showModal)
        {{-- Contenedor oscuro de fondo. Al hacer clic (fuera del modal), se ejecuta closeModal --}}
        <div class="modal-container show" wire:click="closeModal">
            
            {{-- Cuerpo del modal. x-on:click.stop evita que los clics internos cierren el modal --}}
            <div class="modal-inner modal-md" x-on:click.stop>
                
                <button
                    type="button"
                    class="button modal-close"
                    wire:click="closeModal"
                >
                    <x-icons.close/>
                </button>

                <div class="modal-inner__data space-y-5">
                    <header class="section-header">
                        <div class="section-header__title">
                            <hgroup>
                                <h3 class="text-grey-400">Agregar Campaña</h3>
                            </hgroup>
                        </div>
                        <hr>
                    </header>

                    {{-- Formulario vinculado al método saveCampaign del componente Livewire --}}
                    <form wire:submit="saveCampaign" method="POST" class="space-y-5">
                        
                        <div class="grop-columns-2">
                            <div class="container-v">
                                <div class="group-form-v">
                                    <label for="cpg_name">Nombre de la Campaña<span class="text-red-500">*</span></label>
                                    <input type="text" id="cpg_name" wire:model='cpg_name' placeholder="Digite el nombre de la Campaña" required>
                                </div>
                                {{-- Manejo de errores de validación para cpg_name --}}
                                <div>
                                    @error('cpg_name')
                                        <x-toast.error-toast :message="$message"/>
                                    @enderror
                                </div>
                            </div>
                            <div class="container-v">
                                <div class="group-form-v">
                                    <label for="cand_name">Nombre de el Candidato<span class="text-red-500">*</span></label>
                                    <input type="text" id="cand_name" wire:model='cand_name' placeholder="Digite el nombre de el Candidato" required>
                                </div>
                                <div>
                                    @error('cand_name')
                                        <x-toast.error-toast :message="$message"/>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="grop-columns-2">
                            <div class="container-v">
                                <div class="group-form-v">
                                    <label for="cpg_code">Código de Campaña<span class="text-red-500">*</span></label>
                                    <input type="text" id="cpg_code" wire:model='cpg_code' placeholder="Digite el código de la campaña">
                                </div>
                                <div>
                                    @error('cpg_code')
                                        <x-toast.error-toast :message="$message"/>
                                    @enderror
                                </div>
                            </div>
                            <div class="container-v">
                                <div class="group-form-v">
                                    <label for="position">Cargo Aspirado<span class="text-red-500">*</span></label>
                                    <input type="text" id="position" wire:model='position' placeholder="Digite el cargo al que aspira 2026-2030" required>
                                </div>
                                <div>
                                    @error('position')
                                        <x-toast.error-toast :message="$message"/>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="grop-columns-2">
                            <div class="container-v">
                                <div class="group-form-v">
                                    <label for="start_date">Fecha de inicio<span class="text-red-500">*</span></label>
                                    {{-- x-init inicializa el calendario y vincula el valor seleccionado con Livewire --}}
                                    <input type="text" id="start_date" wire:model='start_date' placeholder="Seleccione la fecha de inicio" required x-data
                                        x-ref="startDate" x-init="
                                            $nextTick(() => {
                                                flatpickr($refs.startDate, {
                                                    dateFormat: 'Y-m-d',
                                                    minDate: 'today',
                                                    locale: 'es',
                                                    onChange: function(selectedDates, dateStr, instance) {
                                                        $wire.start_date = dateStr; // Sincroniza con el backend
                                                        const endDate = document.getElementById('end_date');
                                                        if (endDate && endDate._flatpickr) {
                                                            endDate._flatpickr.set('minDate', dateStr); // La fecha fin no puede ser menor a la de inicio
                                                        }
                                                    }
                                                });
                                            })">
                                </div>
                                <div>
                                    @error('start_date')
                                        <x-toast.error-toast :message="$message"/>
                                    @enderror
                                </div>
                            </div>
                            <div class="container-v">
                                <div class="group-form-v">
                                    <label for="end_date">Fecha de finalización<span class="text-red-500">*</span></label>
                                    <input type="text" id="end_date" wire:model='end_date' placeholder="Seleccione la fecha de finalización" required x-data
                                        x-ref="endDate" x-init="
                                            $nextTick(() => {
                                                flatpickr($refs.endDate, {
                                                    dateFormat: 'Y-m-d',
                                                    minDate: $wire.start_date || 'today',
                                                    locale: 'es',
                                                    onChange: function(selectedDates, dateStr, instance) {
                                                        $wire.end_date = dateStr;
                                                    }
                                                });
                                            })">
                                </div>
                                <div>
                                    @error('end_date')
                                        <x-toast.error-toast :message="$message"/>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="container-v">
                            {{-- Componente anidado de Livewire para buscar usuarios --}}
                            <livewire:components.search-users/>
                            <div>
                                @error('user_ids')
                                    <x-toast.error-toast :message="$message"/>
                                @enderror
                            </div>
                        </div>

                        {{-- Muestra errores de sesión generales (ej: errores de base de datos) --}}
                        @if (session()->has('error'))
                            <x-toast.error-toast :message="session('error')"/>
                        @endif

                        <hr/>

                        <div class="justify-between flex w-full">
                            <button type="button" class="btn-secondary" wire:click='closeModal'>
                                <x-icons.close/> Cancelar
                            </button>
                            <button type="submit" class="btn-primary">
                                <x-icons.save/> Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>