<section class="dashboard__main__section">
    <div class="breadcrumbs">
        <a href="">Puesto de Votacion</a>
    </div>

    <div class="container-v mb-4">
        @if (session()->has('success'))
            <x-toast.success-toast :message="session('success')"/>
        @endif
    </div>

    <form wire:submit.prevent="searchUser">
        <div class="container-v">
            <div class="group-form-v">
                <label for="search">Documento de Identificacion<span class="text-red-500">*</span></label>

                <div class="group-form-h gap-y-4">
                    <input type="text" id="search" class="!py-3"
                        wire:model="search"
                        placeholder="Digite su numero de identificacion" required>

                    <div class="items-end flex gap-2">
                        <button type="submit" class="btn-primary !flex-nowrap">
                            Buscar <x-icons.search/>
                        </button>

                        @if($search)
                            <button type="button" wire:click="clearSearch" class="btn-secondary">
                                Limpiar
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>

    <hr class="my-4">

    @if($notFound)
        <div class="mt-4 p-4 rounded-lg border border-red-300 bg-red-50 flex justify-between items-center">
            <span class="text-red-800 font-medium">
                No esta registrado en el sistema
            </span>

            @can('referSupporters', $campaign)
                <a href="{{ route('campaign.add-supporter', $campaign) }}" class="btn-primary">
                    Registrar usuario
                </a>
            @endcan
        </div>
    @endif

    @if($user)
        <div class="mt-4 p-4 rounded-lg border border-gray-300 bg-gray-50">
            <p class="font-semibold text-lg">Informacion del usuario</p>
            <p class="text-gray-600">
                {{ $user->first_name }} {{ $user->paternal_surname }}
            </p>
        </div>

        <div class="mt-6">
            @if($isComplete && ! $isEditing)
                <div class="space-y-5">
                    <h3 class="text-lg font-semibold">Informacion encontrada</h3>

                    <div class="grop-columns-2">
                        <div class="container-v">
                            <ul class="list-vertical wrap-primary">
                                <li class="!border-gray-200"><div class="flex items-start !px-4 !py-1"><div class="flex-1 min-w-0"><p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Departamento</p><p class="text-gray-800 font-medium">{{ $department ?: '-' }}</p></div></div></li>
                                <li class="!border-gray-200"><div class="flex items-start !px-4 !py-1"><div class="flex-1 min-w-0"><p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Municipio</p><p class="text-gray-800 font-medium">{{ $municipality ?: '-' }}</p></div></div></li>
                                <li class="!border-gray-200"><div class="flex items-start !px-4 !py-1"><div class="flex-1 min-w-0"><p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Puesto</p><p class="text-gray-800 font-medium">{{ $stand ?: '-' }}</p></div></div></li>
                            </ul>
                        </div>

                        <div class="container-v">
                            <ul class="list-vertical wrap-primary">
                                <li class="!border-gray-200"><div class="flex items-start !px-4 !py-1"><div class="flex-1 min-w-0"><p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Mesa</p><p class="text-gray-800 font-medium">{{ $table ?: '-' }}</p></div></div></li>
                                <li class="!border-gray-200"><div class="flex items-start !px-4 !py-1"><div class="flex-1 min-w-0"><p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Nombre de la Institucion</p><p class="text-gray-800 font-medium">{{ $address ?: '-' }}</p></div></div></li>
                            </ul>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="button" class="btn-primary w-full md:w-auto" wire:click="startEditing">
                            Editar
                        </button>
                    </div>
                </div>
            @else
                <h3 class="text-lg font-semibold mb-4">
                    {{ $isComplete ? 'Editar informacion' : 'Completar informacion' }}
                </h3>

                <form wire:submit.prevent="save" class="space-y-5">
                    <livewire:components.location-selector
                        :initial-department-name="$department"
                        :initial-municipality-name="$municipality"
                        :key="'point-location-selector-'.($user?->id ?? 'new').'-'.($department ?? 'none').'-'.($municipality ?? 'none')" />

                    @if (session()->has('error'))
                        <x-toast.error-toast :message="session('error')"/>
                    @endif

                    <div class="grop-columns-2">
                        <div>
                            @error('department')
                                <x-toast.error-toast :message="$message"/>
                            @enderror
                        </div>
                        <div>
                            @error('municipality')
                                <x-toast.error-toast :message="$message"/>
                            @enderror
                        </div>
                    </div>

                    <div class="grop-columns-2">
                        <div class="container-v">
                            <div class="group-form-v">
                                <label>Puesto</label>
                                <input type="text" wire:model="stand" placeholder="Nombre del puesto">
                            </div>
                            @error('stand') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="container-v">
                            <div class="group-form-v">
                                <label>Mesa</label>
                                <input type="text" wire:model="table" placeholder="Numero de mesa">
                            </div>
                            @error('table') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="container-v">
                        <div class="group-form-v">
                            <label>Nombre de la Institucion</label>
                            <input type="text" wire:model="address" placeholder="Nombre de la institucion">
                        </div>
                        @error('address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end mt-4">
                        <button type="submit" class="btn-primary w-full md:w-auto">
                            {{ $isComplete ? 'Actualizar informacion' : 'Guardar informacion' }}
                            <x-icons.send-fill/>
                        </button>
                    </div>
                </form>
            @endif
        </div>
    @endif
</section>
