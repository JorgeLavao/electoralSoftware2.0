<section class="dashboard__main__section">
    {{-- Breadcrumbs --}}
    <div class="breadcrumbs">
        Simpatizantes
    </div>

    <div class="relative">
        {{-- Indicador de carga --}}
        <div wire:loading class="absolute inset-0 z-20 cursor-progress bg-white/50 flex items-center justify-center">
            <span class="text-primary font-bold">Cargando...</span>
        </div>

        {{-- BARRA DE ACCIONES --}}
        <div class="flex justify-end md:mb-4 gap-4 flex-col md:flex-row">
            <a href="{{ route('download.template.supporter') }}" class="button btn-secundary text-primary border-primary">
                <x-icons.file-download-line/> Plantilla
            </a>
            
            <a href="{{ route('supporter.import', session('current_campaign')->code) }}" class="button btn-primary"> 
                <x-icons.upload-line/> Importar 
            </a>

            <a href="{{ route('campaign.add-supporter', session('current_campaign')->code) }}" class="button btn-primary" wire:navigate> 
                <x-icons.add-fill/> Agregar
            </a>
        </div>

        {{-- DASHBOARD DE ESTADOS --}}
        <div class="container-v area-2">
            <h4>Novedades</h4>
            <div class="grop-columns-3">
                <button type="button" class="btn-secundary border-primary {{ $filter === null ? 'bg-primary text-white' : 'text-primary'}}" wire:click='applyFilter(null)'>
                    {{ $this->campaign->foreign_users()->wherePivot('validate','!=' ,2)->count() }} Usuarios Registrados
                </button>
                
                <button type="button" class="btn-secundary border-primary {{ $filter === 0 ? 'bg-primary text-white' : 'text-primary'}}" wire:click='applyFilter(0)'>
                    {{ $this->campaign->foreign_users()->wherePivot('validate', 0)->count() }} Pendientes
                </button>

                <button type="button" class="btn-secundary border-primary {{ $filter === 1 ? 'bg-primary text-white' : 'text-primary'}}" wire:click='applyFilter(1)'>
                    {{ $this->campaign->foreign_users()->wherePivot('validate', 1)->count() }} Aceptados
                </button>

                <button type="button" class="btn-secundary border-primary {{ $filter === 2 ? 'bg-primary text-white' : 'text-primary'}}" wire:click='applyFilter(2)'>
                    {{ $this->campaign->foreign_users()->wherePivot('validate', 2)->count() }} Rechazados
                </button>
            </div>
        </div>

        {{-- SECCIÓN DE BÚSQUEDA Y TABLA --}}
        <div class="container-v mt-4">
            <div class="group-form-v">
                <div class="group-form-h gap-y-4">
                    {{-- Usamos wire:model.live para que busque mientras escribe o .blur para cuando pierda el foco --}}
                    <input type="text" id="search" class="!py-3" wire:model.live.debounce.500ms="searchTerm" placeholder="Digite el Nombre o Número de documento a Buscar">
                    <div class="items-end">
                        <button type="button" class="btn-primary !flex-nowrap" wire:click="$refresh">
                            Buscar <x-icons.search/>
                        </button>
                    </div>
                </div>
            </div>

            @if (session()->has('success'))
                <x-toast.success-toast :message="session('success')"/>
            @endif

            <div class="flex justify-between items-center mb-2">
                <h4>{{ $users->total() }} Resultados</h4>
            </div>

            <div class="overflow-x-auto">
                <table class="responsive w-full">
                    <thead>
                        <tr>
                            <th class="w-[30px] p-2">Estado</th>
                            <th class="p-2">No. Documento</th>
                            <th class="p-2 text-left">Nombre</th>
                            <th class="p-2">No. de contacto</th>
                            <th class="p-2 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            {{-- LA KEY ES VITAL: Ayuda a Livewire a mantener el orden de la lista --}}
                            <tr wire:key="user-row-{{ $user->id }}">
                                <td class="p-2">
                                    @if ($user->pivot->validate == 1)
                                        <div class="text-valid border-valid rounded-xl border py-1 px-2 flex justify-center" title="Aceptado">
                                            <x-icons.check-fill :size="16"/>
                                        </div>
                                    @elseif($user->pivot->validate == 2)
                                        <div class="text-red-500 border-red-500 rounded-xl border py-1 px-2 flex justify-center" title="Rechazado">
                                            <x-icons.close :size="16"/>
                                        </div>
                                    @else
                                        <div class="text-amber-700 border-amber-700 rounded-xl border py-1 px-2 flex justify-center" title="Pendiente">
                                            <x-icons.alert-line :size="16"/>
                                        </div>
                                    @endif
                                </td>
                                <td class="p-2 text-center">{{ $user->document_number }}</td>
                                <td class="p-2">{{ $user->fullName }}</td>
                                <td class="p-2 text-center">{{ $user->celphone }}</td>
                                <td class="p-2">
                                    <div class="flex justify-center gap-2">
                                        {{-- Aceptar --}}
                                        <button type="button" 
                                            class="button-icon {{ $user->pivot->validate == 1 ? 'opacity-50 cursor-not-allowed' : 'text-green-500'}}"
                                            @disabled($user->pivot->validate == 1) 
                                            wire:click="acceptInvitation({{ $user->id }})" 
                                            title="Aceptar">
                                            <x-icons.check-fill :size="16"/>
                                        </button>

                                        {{-- Eliminar/Rechazar --}}
                                        @if ($user->pivot->validate != 0)
                                            <button type="button" class="text-red-500" wire:click="delUser({{ $user->id }})" title="Eliminar">
                                                <x-icons.trash-outline :size="16"/>
                                            </button>
                                        @else
                                            <button type="button" class="text-red-500" wire:click="refuse({{ $user->id }})" title="Rechazar">
                                                <x-icons.close :size="16"/>
                                            </button>
                                        @endif

                                        {{-- Ver Detalles --}}
                                        <button type="button" class="text-gray-500" 
                                            wire:click="$dispatch('openModal', { user_id: {{ $user->id }} })" title="Ver información">
                                            <x-icons.eye :size="16"/>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-gray-500">
                                    No se encontraron simpatizantes registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $users->links() }}
            </div>
        </div>
    </div>

    <livewire:supporters.show-user-modal/>
</section>