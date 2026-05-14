<section class="dashboard__main__section">
    <div class="breadcrumbs">
        <a href="{{ route('campaign.groups', $campaign->code) }}">Grupos de la Campaña</a> / Crear grupo
    </div>

    <div class="mb-4 flex items-center justify-between">
        <div>
            <h3 class="text-xl font-semibold">Crear nuevo grupo</h3>
            <p class="text-sm text-gray-500">Define si el grupo servirá para organizar simpatizantes o para construir estrategias.</p>
        </div>
    </div>

    <div class="grop-columns-2">
        <div class="container-v area-2">
            <form wire:submit="save" class="space-y-4">
                <div class="container-v">
                    <div class="group-form-v">
                        <label for="name">Nombre del grupo<span class="text-red-500">*</span></label>
                        <input id="name" type="text" wire:model="name" placeholder="Ej. Voluntarios Zona Norte">
                    </div>
                    @error('name')
                        <x-toast.error-toast :message="$message" />
                    @enderror
                </div>

                <div class="grop-columns-2">
                    <div class="container-v">
                        <div class="group-form-v">
                            <label for="mode">Uso del grupo<span class="text-red-500">*</span></label>
                            <select id="mode" wire:model.live="mode">
                                @foreach ($modeOptions as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('mode')
                            <x-toast.error-toast :message="$message" />
                        @enderror
                    </div>

                    <div class="container-v">
                        <div class="group-form-v">
                            <label for="type">Tipo o categoría<span class="text-red-500">*</span></label>
                            <select id="type" wire:model="type">
                                @foreach ($typeOptions as $key => $definition)
                                    <option value="{{ $key }}">{{ $definition['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('type')
                            <x-toast.error-toast :message="$message" />
                        @enderror
                    </div>
                </div>

                <div class="grop-columns-2">
                    <div class="container-v">
                        <div class="group-form-v">
                            <label for="responsible_name">Encargado</label>
                            <input id="responsible_name" type="text" wire:model="responsible_name" placeholder="Responsable del grupo">
                        </div>
                        @error('responsible_name')
                            <x-toast.error-toast :message="$message" />
                        @enderror
                    </div>

                    <div class="container-v">
                        <div class="group-form-v">
                            <label for="zone">Zona</label>
                            <input id="zone" type="text" wire:model="zone" placeholder="Barrio, comuna, vereda o sector">
                        </div>
                        @error('zone')
                            <x-toast.error-toast :message="$message" />
                        @enderror
                    </div>
                </div>

                <div class="container-v">
                    <div class="group-form-v">
                        <label for="description">Descripción general</label>
                        <textarea id="description" rows="4" wire:model="description" placeholder="Objetivo, observaciones, tareas o contexto del grupo"></textarea>
                    </div>
                    @error('description')
                        <x-toast.error-toast :message="$message" />
                    @enderror
                </div>

                @if ($mode === 'strategies')
                    <div class="container-v">
                        <div class="group-form-v">
                            <label for="strategy_content">Estrategias o líneas de acción<span class="text-red-500">*</span></label>
                            <textarea id="strategy_content" rows="7" wire:model="strategy_content" placeholder="Escribe aquí las estrategias, temas o acciones que se deben desarrollar"></textarea>
                        </div>
                        @error('strategy_content')
                            <x-toast.error-toast :message="$message" />
                        @enderror
                    </div>
                @endif

                <div class="grop-columns-2">
                    <div class="container-v">
                        <div class="group-form-v">
                            <label for="sort_order">Orden</label>
                            <input id="sort_order" type="number" min="0" wire:model="sort_order">
                        </div>
                    </div>

                    <div class="flex items-center gap-6 pt-7">
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" wire:model="is_active">
                            Activo
                        </label>
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" wire:model="is_hidden">
                            Oculto
                        </label>
                    </div>
                </div>

                <div class="flex justify-between">
                    <a href="{{ route('campaign.groups', $campaign->code) }}" class="button btn-secundary border-primary text-primary">
                        Volver
                    </a>
                    <button type="submit" class="btn-primary">Guardar grupo</button>
                </div>
            </form>
        </div>

        <div class="container-v area-2">
            @if ($mode === 'supporters')
                <h4>Seleccionar simpatizantes</h4>
                <p class="text-sm text-gray-500">Solo se listan simpatizantes activos y aceptados dentro de la campaña.</p>

                <div class="container-v mt-3">
                    <div class="group-form-v">
                        <label for="supporterSearch">Buscar simpatizante</label>
                        <input id="supporterSearch" type="text" wire:model.live.debounce.400ms="supporterSearch" placeholder="Nombre o documento">
                    </div>
                </div>

                <div class="mt-4 max-h-[520px] space-y-3 overflow-y-auto rounded-lg border border-gray-200 p-4">
                    @forelse ($supporters as $supporter)
                        <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-3">
                            <input type="checkbox" value="{{ $supporter->id }}" wire:model="selectedSupporters" class="mt-1">
                            <div>
                                <div class="font-semibold">{{ $supporter->fullName }}</div>
                                <div class="text-sm text-gray-500">{{ $supporter->document_number }} | {{ $supporter->celphone }}</div>
                            </div>
                        </label>
                    @empty
                        <p class="text-sm text-gray-500">No hay simpatizantes disponibles con ese filtro.</p>
                    @endforelse
                </div>
            @else
                <h4>Panel de estrategias</h4>
                <div class="rounded-lg border border-gray-200 p-4 text-sm text-gray-600">
                    Aquí puedes escribir líneas de campaña, temas de interés, mensajes clave, planificación territorial o cualquier estrategia que quieras conservar en el grupo.
                </div>
            @endif
        </div>
    </div>
</section>
