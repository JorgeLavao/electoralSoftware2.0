<section class="dashboard__main__section">
    {{-- Navegación de migas de pan --}}
    <div class="breadcrumbs">
        Listados
    </div>

    <article class="dashboard__main__section__article mb-24">
        {{-- Mensajes de Retroalimentación: Se muestran tras acciones exitosas (crear, editar, eliminar) --}}
        @if (session()->has('success'))
            <div>
                <x-toast.success-toast :message="session('success')"/>
            </div>
        @endif

        <div class="relative">
            {{-- Feedback de carga: Bloquea visualmente la sección mientras Livewire procesa peticiones en el servidor --}}
            <div wire:loading class="absolute inset-0 z-20 cursor-progress"></div>

            {{-- Acción Principal: wire:navigate permite una transición tipo SPA sin recargar la página completa --}}
            <div class="flex justify-end md:mb-4">
                <a href="{{ route('list.create', session('current_campaign')->code) }}" class="button btn-primary" wire:navigate> 
                    <x-icons.add-fill/> Agregar Listado 
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
                        <x-icons.search/>Buscar
                    </button>
                </div>
            </div>

            {{-- Renderizado de la lista de registros --}}
            <ul class="list-vertical wrap-primary">
                @foreach ($lists as $list)
                    <li class="!overflow-visible">
                        <div class="grid-9-3">
                            {{-- Información del listado --}}
                            <div class="container-v">
                                <a href="{{ route('list.edit', [$campaign->code, $list->id]) }}">
                                    <h3>{{ $list->created_at->format('d/m/Y') }} - {{ $list->name }}</h3>
                                </a>
                            </div>

                            {{-- Etiquetas y Menú de Acciones Rápidas --}}
                            <div class="items-center flex !justify-end gap-2">
                                <div class="tag">{{ $list->foreign_users_count ?? $list->foreign_users()->count() }} Integrantes</div>
                                
                                {{-- Dropdown de acciones (Alpine.js): Maneja apertura/cierre localmente para no saturar el servidor --}}
                                <div x-data="{ open: false }" class="relative">
                                    <button @click="open = !open" type="button" class="clear text-primary">
                                        <x-icons.more-vert/>
                                    </button>
                                    
                                    {{-- El menú se cierra automáticamente al hacer clic fuera o seleccionar una opción --}}
                                    <div x-show="open" @click.outside="open = false"
                                        class="absolute right-0 mt-2 w-32 bg-white border border-gray-200 rounded-lg shadow-lg z-10">
                                        <ul class="py-2">
                                            <li> <button type="button" class="clear text-primary"> <x-icons.file-download-line/> Excel </button> </li>
                                            
                                            {{-- Toggle de Estado: Cambia dinámicamente según el estatus actual del listado --}}
                                            @if (!$list->status)
                                                <li> <button type="button" class="clear text-primary" wire:click='activeList({{ $list->id }})'> <x-icons.eye/> Activar </button> </li>
                                            @else
                                                <li> <button type="button" class="clear text-primary" wire:click='inactiveList({{ $list->id }})'> <x-icons.eye-closed/> Inactivar </button> </li>
                                            @endif
                                            
                                            <li> <button type="button" class="clear text-primary" wire:click='confirmDelete({{ $list->id }})'> <x-icons.trash-outline/> Eliminar </button> </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
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