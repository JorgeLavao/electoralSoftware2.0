<section class="dashboard__main__section">
    {{-- Navegación Jerárquica: Permite regresar al índice de listados usando wire:navigate para rapidez --}}
    <div class="breadcrumbs">
        <a href="{{ route('list.index', session('current_campaign')->code) }}" wire:navigate>Listados</a>
        /  {{ $list->created_at->format('d/m/Y') }} {{ $list->name }}
    </div>

    <div class="relative">
        {{-- Overlay de carga: Se activa durante cualquier proceso de red (actualizar nombre, buscar, etc.) --}}
        <div wire:loading class="absolute inset-0 z-20 cursor-progress"></div>
        
        {{-- SECCIÓN 1: Actualización del Nombre del Listado --}}
        <div class="container-v mb-4" wire:loading.class="opacity-50">
            {{-- Feedback de éxito --}}
            @if (session()->has('success'))
                <div>
                    <x-toast.success-toast :message="session('success')"/>
                </div>
            @endif

            <div class="group-form-v">
                <label for="name">Nombre<span class="text-red-500">*</span></label>
                <div class="group-form-h gap-y-4">
                    {{-- wire:model: Vincula el input con la propiedad pública del componente --}}
                    <input type="text" id="name" class="!py-3" wire:loading.attr="disabled" wire:model="name"
                        placeholder="Dígite el nombre">
                    <div class="items-end">
                        <button type="button" class="btn-primary !flex-nowrap" wire:click='updateList'>
                            Actualizar <x-icons.save/>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Validación de error específica para el campo nombre --}}
            @error('name')
                <div>
                    <x-toast.error-toast :message="$message"/>
                </div>
            @enderror
        </div>

        {{-- SECCIÓN 2: Gestión de Integrantes (Tabla y Búsqueda) --}}
        <div class="area-2 container-v">
            <h4>Integrantes</h4>
            
            {{-- Buscador Interno del Listado: Por nombre o documento --}}
            <div class="group-form-v">
                <div class="group-form-h gap-y-4">
                    <input type="text" id="search" class="!py-3" wire:loading.attr="disabled" wire:model="searchInput"
                        placeholder="Digite el Nombre o Número de documento a Buscar">
                    <div class="items-end">
                        <button type="button" class="btn-primary !flex-nowrap" wire:click='search'>
                            buscar <x-icons.search/>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Disparador de Modal: Usa el sistema de eventos de Livewire ($dispatch) para abrir el modal de agregar usuario --}}
            <div>
                <button type="button" class="text-primary border-primary" wire:click="$dispatch('openModal', { list_id: {{ json_encode($list->id) }} })">
                    <x-icons.add-fill/> Agregar Integrante
                </button>
            </div>

            <hr>
            
            {{-- Contador dinámico de integrantes --}}
            <h5 class="base-semibold text-gray-400">{{ $list->foreign_users()->count() }} integrantes</h5>

            {{-- Feedback de errores en la gestión de integrantes --}}
            @if (session()->has('error'))
                <div>
                    <x-toast.error-toast :message="session('error')"/>
                </div>
            @endif

            {{-- Tabla de Integrantes --}}
            <div class="bg-white container-v">
                <table class="responsive w-full">
                    <thead>
                        <tr>
                            <th class="w-[30px]">Estatus</th> {{-- Columna para validación visual --}}
                            <th>Nro. Documento</th>
                            <th>Nombre</th>
                            <th>Nro. de contacto</th>
                            <th class="w-[135px]">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>
                                    {{-- Lógica de validación desde la tabla pivote ($user->pivot) --}}
                                    @if ($user->pivot->validate)
                                        <div class="text-valid border-valid rounded-xl border py-1 px-2" title="Usuario Validado">
                                            <x-icons.check-fill/>
                                        </div>
                                    @else
                                        <div class="text-invalid border-invalid rounded-xl border py-1 px-2" title="Pendiente de Validación">
                                            <x-icons.alert-line/>
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $user->document_number }}</td>
                                <td>{{ $user->fullName }}</td>
                                <td>{{ $user->celphone }}</td>
                                <td>
                                    {{-- Acción para remover integrante de la relación --}}
                                    <button type="button" class="text-primary border-primary" wire:click="delUser({{ $user->id }})">
                                        <x-icons.trash-outline/> Quitar
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Paginación de usuarios dentro del listado --}}
            <div class="">
                {{ $users->links('vendor.pagination.tailwind') }}
            </div>
        </div>
    </div>

    {{-- Inclusión del componente modal: Debe estar fuera del 'relative' principal para evitar problemas de capas (z-index) --}}
    <livewire:list.add-user-modal />
</section>