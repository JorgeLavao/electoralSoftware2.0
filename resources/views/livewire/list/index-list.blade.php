<section class="dashboard__main__section">
    {{-- Navegación de migas de pan --}}
    <div class="breadcrumbs">
        Listados
    </div>

    <article class="dashboard__main__section__article mb-24">
        {{-- Mensajes de Retroalimentación: Se muestran tras acciones exitosas (crear, editar, eliminar) --}}
        @if (session()->has('success'))
        <div>
            <x-toast.success-toast :message="session('success')" />
        </div>
        @endif

        <div class="relative">
            {{-- Feedback de carga: Bloquea visualmente la sección mientras Livewire procesa peticiones en el servidor --}}
            <div wire:loading class="absolute inset-0 z-20 cursor-progress"></div>

            {{-- Acción Principal: wire:navigate permite una transición tipo SPA sin recargar la página completa --}}
            <div class="flex justify-end md:mb-4">
                <a href="{{ route('list.create', session('current_campaign')->code) }}" class="button btn-primary" wire:navigate>
                    <x-icons.add-fill /> Agregar Listado
                </a>
            </div>

            <br>

            {{-- Bloque de Filtros de Búsqueda --}}
            <div class="area-2 container-v">
                <h4>Buscar</h4>
                <div class="grop-columns-3 container-v">

                    {{-- Filtro por Nombre: Sincronizado en tiempo real con la propiedad $searchName del backend --}}
                    <div class="group-form-v">
                        <label for="name">Por Nombre</label>
                        <input type="text" id="name" wire:model='searchName' placeholder="Digite el Nombre a Buscar" required>
                    </div>

                    {{-- Filtro de Fecha 'Desde': Integración de Flatpickr con Alpine.js --}}
                    <div class="group-form-v">
                        <label for="">Desde</label>
                        <input type="text" id="start_date" wire:model='start_date' placeholder="Seleccione la fecha de inicio" required
                            x-data {{-- Inicializa componente Alpine --}}
                            x-ref="startDate"
                            x-init="
                                $nextTick(() => { {{-- Espera a que el DOM esté listo --}}
                                    flatpickr($refs.startDate, {
                                        dateFormat: 'Y-m-d',
                                        locale: 'es',
                                        maxDate: 'today',
                                        onChange: function(selectedDates, dateStr, instance) {
                                            $wire.start_date = dateStr; {{-- Actualiza propiedad de Livewire --}}
                                            const endDate = document.getElementById('end_date');
                                            {{-- Sincroniza el límite mínimo del calendario 'Hasta' --}}
                                            if (endDate && endDate._flatpickr) {
                                                endDate._flatpickr.set('minDate', dateStr);
                                            }
                                        }
                                    });
                                })">
                    </div>

                    {{-- Filtro de Fecha 'Hasta' --}}
                    <div class="group-form-v">
                        <label for="">Hasta</label>
                        <input type="text" id="end_date" wire:model='end_date' placeholder="Seleccione la fecha de finalización" required
                            x-data
                            x-ref="endDate"
                            x-init="
                                $nextTick(() => {
                                    flatpickr($refs.endDate, {
                                        dateFormat: 'Y-m-d',
                                        minDate: $wire.start_date || '', {{-- Inicializa con el valor actual si existe --}}
                                        maxDate: 'today',
                                        locale: 'es',
                                        onChange: function(selectedDates, dateStr, instance) {
                                            $wire.end_date = dateStr;
                                        }
                                    });
                                })">
                    </div>
                </div>

                {{-- Botón Buscar: Fuerza el refresco de los resultados filtrados --}}
                <div class="flex justify-end">
                    <button type="button" class="text-primary border-primary" wire:click="$refresh">
                        <x-icons.search />Buscar
                    </button>
                </div>
            </div>

            <br>

            {{-- Renderizado de la lista de registros --}}
            <ul class="list-vertical wrap-primary">
                @foreach ($lists as $list)
                <li class="!overflow-visible border rounded-xl mb-2">

                    {{-- HEADER DEL LISTADO --}}
                    <div class="grid-9-3 cursor-pointer"
                        wire:click="toggleList({{ $list->id }})">

                        <div class="container-v">
                            <h3 class="flex items-center justify-between">
                                {{ $list->created_at->format('d/m/Y') }} - {{ $list->name }}

                                {{-- ICONO ACORDEÓN --}}
                                <span>
                                    @if ($openListId === $list->id)
                                    
                                    @else
                                    
                                    @endif
                                </span>
                            </h3>
                        </div>

                        <div class="items-center flex justify-end gap-2">
                            <div class="tag">
                                {{ $list->foreign_users_count ?? $list->foreign_users()->count() }} Integrantes
                            </div>

                            {{-- MENÚ --}}
                            <div x-data="{ open: false }" class="relative">
                                <button @click.stop="open = !open" type="button" class="clear text-primary">
                                    <x-icons.more-vert />
                                </button>

                                <div x-show="open" @click.outside="open = false"
                                    class="absolute right-0 mt-2 w-32 bg-white border rounded-lg shadow-lg z-10">

                                    <ul class="py-2">
                                        <li>
                                            <button type="button" class="clear text-primary">
                                                <x-icons.file-download-line /> Excel
                                            </button>
                                        </li>

                                        @if (!$list->status)
                                        <li>
                                            <button type="button" class="clear text-primary"
                                                wire:click.stop='activeList({{ $list->id }})'>
                                                <x-icons.eye /> Activar
                                            </button>
                                        </li>
                                        @else
                                        <li>
                                            <button type="button" class="clear text-primary"
                                                wire:click.stop='inactiveList({{ $list->id }})'>
                                                <x-icons.eye-closed /> Inactivar
                                            </button>
                                        </li>
                                        @endif

                                        <li>
                                            <button type="button" class="clear text-primary"
                                                wire:click.stop='confirmDelete({{ $list->id }})'>
                                                <x-icons.trash-outline /> Eliminar
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- CONTENIDO DEL ACORDEÓN 🔥 --}}
MEJORAME VISUALMENTE ESO <ul class="list-vertical wrap-primary">
    @foreach ($lists as $list)
        <li class="border rounded-xl mb-3 overflow-hidden" wire:key="list-{{ $list->id }}">

            {{-- HEADER --}}
            <div class="flex justify-between items-center p-3 cursor-pointer bg-white hover:bg-gray-50 transition"
                 wire:click="toggleList({{ $list->id }})">

                {{-- INFO --}}
                <div class="flex items-center gap-3">
                    <span class="font-semibold text-gray-800">
                        {{ $list->created_at->format('d/m/Y') }} - {{ $list->name }}
                    </span>
                </div>

                {{-- DERECHA --}}
                <div class="flex items-center gap-3">

                    {{-- TAG --}}
                    <span class="px-2 py-1 text-sm bg-gray-100 rounded-full">
                        {{ $list->foreign_users_count ?? $list->foreign_users()->count() }}
                    </span>

                    {{-- ICONO --}}
                    <span class="transition-transform duration-200
                        {{ $openListId === $list->id ? 'rotate-180' : '' }}">
                        ▼
                    </span>

                    {{-- MENÚ --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click.stop="open = !open"
                                class="p-1 rounded hover:bg-gray-200">
                            ⋮
                        </button>

                        <div x-show="open" @click.outside="open = false"
                             class="absolute right-0 mt-2 w-36 bg-white border rounded shadow z-10">

                            <ul class="text-sm">
                                <li>
                                    <button class="w-full text-left px-3 py-2 hover:bg-gray-100">
                                        Excel
                                    </button>
                                </li>

                                @if (!$list->status)
                                    <li>
                                        <button wire:click.stop="activeList({{ $list->id }})"
                                                class="w-full text-left px-3 py-2 hover:bg-gray-100 text-green-600">
                                            Activar
                                        </button>
                                    </li>
                                @else
                                    <li>
                                        <button wire:click.stop="inactiveList({{ $list->id }})"
                                                class="w-full text-left px-3 py-2 hover:bg-gray-100 text-yellow-600">
                                            Inactivar
                                        </button>
                                    </li>
                                @endif

                                <li>
                                    <button wire:click.stop="confirmDelete({{ $list->id }})"
                                            class="w-full text-left px-3 py-2 hover:bg-red-50 text-red-500">
                                        Eliminar
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ACORDEÓN --}}
            @if ($openListId === $list->id)
                <div class="bg-gray-50 border-t p-3">

                    <h4 class="font-semibold mb-2 text-gray-700">Integrantes</h4>

                    <div class="max-h-60 overflow-y-auto">

                        @forelse ($listUsers as $user)
                            <div class="flex justify-between items-center py-2 border-b">

                                <div>
                                    <p class="font-medium">
                                        {{ $user->first_name }} {{ $user->paternal_surname }}
                                    </p>
                                    <small class="text-gray-400">
                                        {{ $user->document_number }}
                                    </small>
                                </div>

                                <span class="text-sm text-gray-500">
                                    {{ $user->celphone }}
                                </span>

                            </div>
                        @empty
                            <p class="text-center text-gray-400 py-3">
                                Sin usuarios
                            </p>
                        @endforelse

                    </div>
                </div>
            @endif

        </li>
    @endforeach
</ul>


            {{-- Paginación: Genera los enlaces de navegación de forma automática --}}
            <div class="mt-4">
                {{ $lists->links() }}
            </div>
        </div>
    </article>
</section>