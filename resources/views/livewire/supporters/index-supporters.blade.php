<section class="dashboard__main__section">
    <div class="breadcrumbs">
        Simpatizantes
    </div>
    <div class="relative">
        <div wire:loading  class="absolute inset-0 z-20 cursor-progress"></div>
        <div class="flex justify-end md:mb-4 gap-4 flex-col md:flex-row">
            <button type="button" class="btn-secundary text-primary border-primary"><x-icons.file-download-line/>Plantilla</button>
            <button type="button" class="btn-secundary text-primary border-primary"><x-icons.upload-line/>Importar</button>
            <a href="{{ route('list.create', session('current_campaign')->code) }}" class="button btn-primary" wire:navigate> <x-icons.add-fill/> Agregar</a>
        </div>
        <div class="container-v area-2">
            <h4>Novedades</h4>
            <div class="grop-columns-3">
                <button type="button" class="btn-secundary  border-primary {{ $filter === null ? 'text-white bg-primary'  : 'text-primary'}}" wire:click='applyFilter(null)'>
                    {{ $this->campaign->foreign_users()->wherePivot('validate','!=' ,2)->count() }} Usuarios Registrados
                </button>
                <button type="button" class="btn-secundary border-primary {{ $filter === 0 ? 'text-white bg-primary'  : 'text-primary'}}" wire:click='applyFilter(0)'>
                    {{ $this->campaign->foreign_users()->wherePivot('validate', 0)->count() }} Pendientes
                </button>
                <button type="button" class="btn-secundary border-primary {{ $filter === 1 ? 'text-white bg-primary'  : 'text-primary'}}" wire:click='applyFilter(1)'>
                    {{ $this->campaign->foreign_users()->wherePivot('validate', 1)->count() }} Aceptados
                </button>
                <button type="button" class="btn-secundary border-primary {{ $filter === 2 ? 'text-white bg-primary'  : 'text-primary'}}" wire:click='applyFilter(2)'>
                    {{ $this->campaign->foreign_users()->wherePivot('validate', 2)->count() }} Rechazados
                </button>
            </div>
        </div>
        <div class="container-v mt-4">
            <div class="group-form-v">
                <div class="group-form-h gap-y-4">
                    <input type="text" id="search" class="!py-3" wire:loading.attr="disabled" wire:model="searchTerm" placeholder="Digite el Nombre o Número de documento a Buscar">
                    <div class="items-end">
                        <button type="button" class="btn-primary !flex-nowrap" wire:click="$refresh">
                            buscar <x-icons.search/>
                        </button>
                    </div>
                </div>
            </div>
            @if (session()->has('success'))
                <div>
                    <x-toast.success-toast :message="session('success')"/>
                </div>
            @endif
            <h4>{{ $users->count() }} resultados</h4>
            <table class="responsive w-full">
                <thead>
                    <tr>
                        <th class="w-[30px]"></th>
                        <th>Nro. Documento</th>
                        <th>Nombre</th>
                        <th>Nro. de contacto</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>
                                @if ($user->pivot->validate)
                                    <div class="text-valid border-valid rounded-xl border py-1 px-2">
                                        <x-icons.check-fill :size="16"/>
                                    </div>
                                @else
                                    <div class="text-amber-700 border-amber-700 rounded-xl border py-1 px-2">
                                        <x-icons.alert-line :size="16"/>
                                    </div>
                                @endif
                            </td>
                            <td>{{ $user->document_number }}</td>
                            <td>{{ $user->fullName }}</td>
                            <td>{{ $user->celphone }}</td>
                            <td>
                                <button type="button" class="btn-secundary {{ $user->pivot->validate === 1 ? 'text-white' : 'text-green-500 border-green-300'}}"
                                    @disabled($user->pivot->validate === 1) wire:click="acceptInvitation({{ $user->id }})" title="Aceptar">
                                    <x-icons.check-fill :size="16"/>
                                </button>
                                @if ($user->pivot->validate)
                                    <button type="button" class="text-red-500 border-red-300" wire:click="delUser({{ $user->id }})" title="Eliminar">
                                        <x-icons.trash-outline :size="16"/>
                                    </button>
                                @else
                                    <button type="button" class="text-red-500 border-red-300" wire:click="refuse({{ $user->id }})" title="Rechazar">
                                        <x-icons.close :size="16"/>
                                    </button>
                                @endif
                                <button type="button" class="text-gray-500 border-gray-300" wire:click="$dispatch('openModal', { user_id: {{ json_encode($user->id) }} })" title="Ver información">
                                    <x-icons.eye :size="16"/>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-2">
                {{ $users->links() }}
            </div>
        </div>
    </div>
    <livewire:supporters.show-user-modal/>
</section>
