<section class="dashboard__main__section">
    <div class="breadcrumbs">
        <a href="{{ route('campaign.groups', $campaign->code) }}">Grupos de la Campaña</a> / Editar grupo
    </div>

    <div class="container-v mb-4">
        @if (session()->has('success'))
        <x-toast.success-toast :message="session('success')" />
        @endif
    </div>

    <div class="mb-4">
        <h3 class="text-xl font-semibold">Editar {{ $group->name }}</h3>
        <p class="text-sm text-gray-500">Actualiza responsable, zona, estrategia, integrantes y estado del grupo sin eliminarlo.</p>
    </div>

    <div class="grop-columns-2">
        <div class="container-v area-2">
            <form wire:submit="save" class="space-y-4">
                <div class="container-v">
                    <div class="group-form-v">
                        <label for="name">Nombre del grupo<span class="text-red-500">*</span></label>
                        <input id="name" type="text" wire:model="name">
                    </div>
                    @error('name')
                    <x-toast.error-toast :message="$message" />
                    @enderror
                </div>


                <div class="grop-columns-2">
                    <div class="container-v">
                        <div class="group-form-v">
                            <label for="responsible_name">Encargado</label>
                            <input id="responsible_name" type="text" wire:model="responsible_name">
                        </div>
                    </div>

                </div>

                <div class="container-v">
                    <div class="group-form-v">
                        <label for="description">Descripción general</label>
                        <textarea id="description" rows="4" wire:model="description"></textarea>
                    </div>
                </div>



                <div class="grop-columns-2">


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
                    <button type="submit" class="btn-primary">Actualizar grupo</button>
                </div>
            </form>
        </div>

        <div class="container-v area-2">
            @if ($mode === 'supporters')
            <h4>Gestionar simpatizantes del grupo</h4>
            <p class="text-sm text-gray-500">Marca o desmarca las personas que deben pertenecer a este grupo.</p>

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
            <h4>Vista de estrategia</h4>
            <div class="rounded-lg border border-gray-200 p-4 text-sm text-gray-600">
                Usa este espacio para dejar líneas estratégicas, mensajes, tareas clave o temas de interés que quieras revisar más adelante.
            </div>
            @endif
        </div>
    </div>
</section>
