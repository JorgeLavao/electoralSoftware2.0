<section class="dashboard__main__section">
    <div class="breadcrumbs">
        <a href="">Puesto de Votación</a>
    </div>

    <div class="container-v mb-4">
        @if (session()->has('success'))
            <x-toast.success-toast :message="session('success')"/>
        @endif
    </div>

    <form wire:submit.prevent="searchUser">
        <div class="container-v">
            <div class="group-form-v">
                <label for="search">Documento de Identificación<span class="text-red-500">*</span></label>

                <div class="group-form-h gap-y-4">
                    <input type="text" id="search" class="!py-3"
                        wire:model="search"
                        placeholder="Digite su número de identificación" required>

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
                No está registrado en el sistema
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
            <p class="font-semibold text-lg">Información del usuario</p>
            <p class="text-gray-600">
                {{ $user->first_name }} {{ $user->paternal_surname }}
            </p>
        </div>

        <div class="mt-6">
            <h3 class="text-lg font-semibold mb-4">
                {{ $isComplete ? 'Editar información' : 'Completar información' }}
            </h3>

            <form wire:submit.prevent="save" class="space-y-5">
                <livewire:components.location-selector />

                @if (session()->has('error'))
                    <x-toast.error-toast :message="session('error')"/>
                @endif

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
                            <input type="text" wire:model="table" placeholder="Número de mesa">
                        </div>
                        @error('table') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="container-v">
                    <div class="group-form-v">
                        <label>Dirección</label>
                        <input type="text" wire:model="address" placeholder="Dirección del puesto">
                    </div>
                </div>

                <div class="flex justify-end mt-4">
                    <button type="submit" class="btn-primary w-full md:w-auto">
                        {{ $isComplete ? 'Actualizar información' : 'Guardar información' }}
                        <x-icons.send-fill/>
                    </button>
                </div>
            </form>
        </div>
    @endif
</section>
