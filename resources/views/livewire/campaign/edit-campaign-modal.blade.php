<div>
    @if($showModal)
    <div class="modal-container show">

        <div class="modal-inner modal-md" x-data @click.stop>

            <!-- Botón cerrar -->
            <button
                type="button"
                class="button modal-close"
                wire:click="closeModal">
                <x-icons.close />
            </button>

            <!-- FORMULARIO -->
            <form wire:submit.prevent="saveCampaign" class="space-y-5">

                <div class="modal-inner__data space-y-5">
                    <header class="section-header">
                        <div class="section-header__title">
                            <hgroup>
                                <h3 class="text-grey-400">{{ $campaign->name }} - Editar</h3>
                            </hgroup>
                        </div>
                        <hr>
                    </header>

                    <!-- NOMBRE Y CANDIDATO -->
                    <div class="grop-columns-2">
                        <div class="container-v">
                            <div class="group-form-v">
                                <label>Nombre de la Campaña<span class="text-red-500">*</span></label>
                                <input type="text" wire:model.defer="cpg_name" required>
                            </div>
                            @error('cpg_name') <x-toast.error-toast :message="$message" /> @enderror
                        </div>

                        <div class="container-v">
                            <div class="group-form-v">
                                <label>Nombre del Candidato<span class="text-red-500">*</span></label>
                                <input type="text" wire:model.defer="cand_name" required>
                            </div>
                            @error('cand_name') <x-toast.error-toast :message="$message" /> @enderror
                        </div>
                    </div>

                    <!-- CÓDIGO Y CARGO -->
                    <div class="grop-columns-2">
                        <div class="container-v">
                            <div class="group-form-v">
                                <label>Código de Campaña<span class="text-red-500">*</span></label>
                                <input type="text" wire:model.defer="cpg_code">
                            </div>
                            @error('cpg_code') <x-toast.error-toast :message="$message" /> @enderror
                        </div>

                        <div class="container-v">
                            <div class="group-form-v">
                                <label>Cargo Aspirado<span class="text-red-500">*</span></label>
                                <input type="text" wire:model.defer="position" required>
                            </div>
                            @error('position') <x-toast.error-toast :message="$message" /> @enderror
                        </div>
                    </div>

                    <!-- FECHAS -->
                    <div class="grop-columns-2">
                        <div class="container-v">
                            <div class="group-form-v">
                                <label>Fecha de inicio<span class="text-red-500">*</span></label>
                                <input type="text"
                                    wire:model.defer="start_date"
                                    x-data
                                    x-ref="startDate"
                                    x-init="
                                            flatpickr($refs.startDate, {
                                                dateFormat: 'Y-m-d',
                                                locale: 'es',
                                                onChange: (selectedDates, dateStr) => {
                                                    $wire.set('start_date', dateStr);
                                                }
                                            });
                                       ">
                            </div>
                            @error('start_date') <x-toast.error-toast :message="$message" /> @enderror
                        </div>

                        <div class="container-v">
                            <div class="group-form-v">
                                <label>Fecha de finalización<span class="text-red-500">*</span></label>
                                <input type="text"
                                    wire:model.defer="end_date"
                                    x-data
                                    x-ref="endDate"
                                    x-init="
                                            flatpickr($refs.endDate, {
                                                dateFormat: 'Y-m-d',
                                                locale: 'es',
                                                onChange: (selectedDates, dateStr) => {
                                                    $wire.set('end_date', dateStr);
                                                }
                                            });
                                       ">
                            </div>
                            @error('end_date') <x-toast.error-toast :message="$message" /> @enderror
                        </div>
                    </div>

                    <!-- USUARIOS -->
                    <div class="container-v">
                        <livewire:components.search-users
                            :userIds="$user_ids"
                            :allowRemoval="auth()->user()->can('removeCampaignMembers', $campaign)" />
                        @error('user_ids') <x-toast.error-toast :message="$message" /> @enderror
                    </div>
                </div>

                <!-- ERRORES -->
                @if (session()->has('error'))
                <x-toast.error-toast :message="session('error')" />
                @endif

                <hr />

                <!-- BOTONES -->
                <div class="justify-between flex w-full">
                    <button type="button" class="btn-secondary" wire:click="closeModal">
                        <x-icons.close /> Cancelar
                    </button>

                    <button type="submit" class="btn-primary">
                        <x-icons.save /> Guardar
                    </button>
                </div>

            </form>
        </div>
    </div>
    @endif
</div>
