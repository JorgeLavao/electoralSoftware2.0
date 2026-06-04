<section class="dashboard__main__section">
    <div class="breadcrumbs">
        <a href="{{ route('list.index', session('current_campaign')->code) }}" wire:navigate>Listados</a>
        / {{ $list->created_at->format('d/m/Y') }} {{ $list->name }}
    </div>

    <div class="relative">
        <div wire:loading class="absolute inset-0 z-20 cursor-progress"></div>

        <div class="container-v mb-4" wire:loading.class="opacity-50">
            @if (session()->has('success'))
            <div>
                <x-toast.success-toast :message="session('success')" />
            </div>
            @endif

            <div class="group-form-v">
                <label for="name">Nombre<span class="text-red-500">*</span></label>
                <div class="group-form-h gap-y-4">
                    <input type="text" id="name" class="!py-3" wire:loading.attr="disabled" wire:model="name" placeholder="Digite el nombre">
                    <div class="items-end">
                        <button type="button" class="btn-primary !flex-nowrap" wire:click="updateList">
                            Actualizar <x-icons.save />
                        </button>
                    </div>
                </div>
            </div>

            @error('name')
            <div>
                <x-toast.error-toast :message="$message" />
            </div>
            @enderror
        </div>

        <div class="area-2 container-v">
            <h4>Integrantes</h4>

            <div class="group-form-v">
                <div class="group-form-h gap-y-4">
                    <input type="text" id="search" class="!py-3" wire:loading.attr="disabled" wire:model="searchInput" placeholder="Digite el Nombre o Número de documento a Buscar">
                    <div class="items-end">
                        <button type="button" class="btn-primary !flex-nowrap" wire:click="search">
                            Buscar <x-icons.search />
                        </button>
                    </div>
                </div>
            </div>

            <div>
                <button type="button" class="text-primary border-primary" wire:click="$dispatch('openModal', { list_id: {{ json_encode($list->id) }} })">
                    <x-icons.add-fill /> Agregar Integrante
                </button>
            </div>

            <hr>

            <h5 class="base-semibold text-gray-400">{{ $list->foreign_users()->count() }} integrantes</h5>

            @if (session()->has('error'))
            <div>
                <x-toast.error-toast :message="session('error')" />
            </div>
            @endif

            <div class="bg-white container-v">
                <table class="responsive w-full">
                    <thead>
                        <tr>
                            <th class="w-[30px]">Estatus</th>
                            <th>Nro. Documento</th>
                            <th>Nombre</th>
                            <th>Nro. de contacto</th>
                            <th class="w-[135px]">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                        <tr>
                            <td data-label="Estatus">
                                @if ($user->pivot->validate)
                                <div class="rounded-xl border border-valid px-2 py-1 text-valid" title="Usuario validado">
                                    <x-icons.check-fill />
                                </div>
                                @else
                                <div class="rounded-xl border border-invalid px-2 py-1 text-invalid" title="Pendiente de validación">
                                    <x-icons.alert-line />
                                </div>
                                @endif
                            </td>
                            <td data-label="Nro. Documento">{{ $user->document_number }}</td>
                            <td data-label="Nombre">{{ $user->fullName }}</td>
                            <td data-label="Nro. de contacto">{{ $user->celphone }}</td>
                            <td data-label="Acciones">
                                <button type="button" class="text-primary border-primary" wire:click="delUser({{ $user->id }})">
                                    <x-icons.trash-outline /> Quitar
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$users" :livewire="true" />
        </div>
    </div>

    <livewire:list.add-user-modal />
</section>
