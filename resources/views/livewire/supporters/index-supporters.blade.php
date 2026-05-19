<section class="dashboard__main__section">
    <div class="breadcrumbs">
        Simpatizantes
    </div>

    <div class="relative">
        <div wire:loading class="absolute inset-0 z-20 flex cursor-progress items-center justify-center bg-white/50">
            <span class="font-bold text-primary">Cargando...</span>
        </div>

        <div class="flex flex-col gap-4 md:mb-4 md:flex-row md:justify-end">
            <a href="{{ route('download.template.supporter') }}" class="button btn-secundary border-primary text-primary">
                <x-icons.file-download-line /> Plantilla
            </a>

            @can('importSupporters', $campaign)
            <a href="{{ route('supporter.import', $campaign->code) }}" class="button btn-primary">
                <x-icons.upload-line /> Importar
            </a>
            @endcan

            @can('referSupporters', $campaign)
            <a href="{{ route('campaign.add-supporter', $campaign->code) }}" class="button btn-primary" wire:navigate>
                <x-icons.add-fill /> Agregar
            </a>
            @endcan
        </div>

        <div class="container-v area-2">
            <h4>Novedades</h4>
            <div class="grop-columns-3">
                <button type="button" class="btn-secundary border-primary {{ $filter === null ? 'bg-primary text-white' : 'text-primary' }}" wire:click="applyFilter(null)">
                    {{ $this->campaign->foreign_users()->wherePivot('validate', '!=', 2)->count() }} Usuarios Registrados
                </button>

                <button type="button" class="btn-secundary border-primary {{ $filter === 0 ? 'bg-primary text-white' : 'text-primary' }}" wire:click="applyFilter(0)">
                    {{ $this->campaign->foreign_users()->wherePivot('validate', 0)->count() }} Pendientes
                </button>

                <button type="button" class="btn-secundary border-primary {{ $filter === 1 ? 'bg-primary text-white' : 'text-primary' }}" wire:click="applyFilter(1)">
                    {{ $this->campaign->foreign_users()->wherePivot('validate', 1)->count() }} Aceptados
                </button>

                <button type="button" class="btn-secundary border-primary {{ $filter === 2 ? 'bg-primary text-white' : 'text-primary' }}" wire:click="applyFilter(2)">
                    {{ $this->campaign->foreign_users()->wherePivot('validate', 2)->count() }} Rechazados
                </button>
            </div>
        </div>

        <div class="container-v mt-4">
            <div class="group-form-v">
                <div class="group-form-h gap-y-4">
                    <input type="text" id="search" class="!py-3" wire:model.live.debounce.500ms="searchTerm" placeholder="Digite el Nombre o Número de documento a Buscar">
                    <div class="items-end">
                        <button type="button" class="btn-primary !flex-nowrap" wire:click="$refresh">
                            Buscar <x-icons.search />
                        </button>
                    </div>
                </div>
            </div>

            @if (session()->has('success'))
            <x-toast.success-toast :message="session('success')" />
            @endif

            <div class="mb-2 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h4>{{ $users->total() }} Resultados</h4>

                <label class="flex w-full items-center justify-between gap-3 rounded-lg border border-slate-200 px-4 py-2 text-sm text-slate-600 sm:w-auto">
                    <span>Ver</span>
                    <select class="!w-24 !rounded-lg !py-2" wire:model.live="perPage">
                        @foreach ($perPageOptions as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </select>
                    <span>por pagina</span>
                </label>
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
                        <tr wire:key="user-row-{{ $user->id }}">
                            <td class="p-2">
                                @if ($user->pivot->validate == 1)
                                <div class="flex justify-center rounded-xl border border-valid px-2 py-1 text-valid" title="Aceptado">
                                    <x-icons.check-fill :size="16" />
                                </div>
                                @elseif ($user->pivot->validate == 2)
                                <div class="flex justify-center rounded-xl border border-red-500 px-2 py-1 text-red-500" title="Rechazado">
                                    <x-icons.close :size="16" />
                                </div>
                                @else
                                <div class="flex justify-center rounded-xl border border-amber-700 px-2 py-1 text-amber-700" title="Pendiente">
                                    <x-icons.alert-line :size="16" />
                                </div>
                                @endif
                            </td>
                            <td class="p-2 text-center">{{ $user->document_number }}</td>
                            <td class="p-2">{{ $user->fullName }}</td>
                            <td class="p-2 text-center">{{ $user->celphone }}</td>
                            <td class="p-2">
                                <div class="flex justify-center gap-2">
                                    @can('validateSupporters', $campaign)
                                    <button
                                        type="button"
                                        class="button-icon {{ $user->pivot->validate == 1 ? 'cursor-not-allowed opacity-50' : 'text-green-500' }}"
                                        @disabled($user->pivot->validate == 1)
                                        wire:click="acceptInvitation({{ $user->id }})"
                                        title="Aceptar"
                                        >
                                        <x-icons.check-fill :size="16" />
                                    </button>
                                    @endcan

                                    @if ($user->pivot->validate != 0)
                                    @can('removeSupporters', $campaign)
                                    <button type="button" class="text-red-500" wire:click="delUser({{ $user->id }})" title="Eliminar">
                                        <x-icons.trash-outline :size="16" />
                                    </button>
                                    @endcan
                                    @else
                                    @can('validateSupporters', $campaign)
                                    <button type="button" class="text-red-500" wire:click="refuse({{ $user->id }})" title="Rechazar">
                                        <x-icons.close :size="16" />
                                    </button>
                                    @endcan
                                    @endif

                                    <button type="button" class="text-gray-500" wire:click="$dispatch('openModal', { user_id: {{ $user->id }} })" title="Ver información">
                                        <x-icons.eye :size="16" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-4 text-center text-gray-500">
                                No se encontraron simpatizantes registrados.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$users" :livewire="true" />
        </div>
    </div>

    <livewire:supporters.show-user-modal :campaign="$campaign" />
</section>
