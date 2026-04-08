<section class="dashboard__main__section">
    {{-- Navegación --}}
    <div class="breadcrumbs">
        <a href="{{ route('list.index', session('current_campaign')->code) }}" wire:navigate>Listados</a>
        / Crear
    </div>

    <div class="relative">
        {{-- Indicador de carga específico para acciones de búsqueda y guardado --}}
        <div wire:loading wire:target="search,save" class="absolute inset-0 z-20 cursor-progress"></div>

        <div class="container-v" wire:loading.class="opacity-50" wire:target="search,save">
            
            {{-- SECCIÓN: Definición del Nombre --}}
            <div class="group-form-v">
                <label for="name">Nombre<span class="text-red-500">*</span></label>
                <div class="group-form-h">
                    <input type="text" class="!w-auto" name="name" wire:model='name' placeholder="Dígite el nombre">
                </div>
            </div>
            <div>
                @error('name')
                    <x-toast.error-toast :message="$message"/>
                @enderror
            </div>

            {{-- SECCIÓN: Parametrización (Filtros de segmentación) --}}
            <div class="area-2 container-v">
                <h4>Parametrizar</h4>
                
                {{-- Fila 1: Acercamiento, Validación y Vehículo --}}
                <div class="grop-columns-3 mb-4">
                    {{-- Nivel de acercamiento: Incluye un toggle para "Excluir" (lógica inversa en la consulta) --}}
                    <div class="group-form-v">
                        <div class="flex !justify-between !w-full">
                            <label for="approach">Nivel de acercamiento</label>
                            <div class="!flex !items-center">
                                <input type="checkbox" name="sw_approach" id="sw_approach" wire:model='sw_approach'>
                                <span>Excluir</span>
                            </div>
                        </div>
                        <select name="approach" id="approach" wire:model='approach'>
                            <option value="">Seleccione</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                        </select>
                    </div>

                    {{-- Filtro Validado --}}
                    <div class="group-form-v">
                        <label for="validate">Validado</label>
                        <select name="validate" id="validate" wire:model='verify'>
                            <option value="">Seleccione</option>
                            <option value="1">Si</option>
                            <option value="0">No</option>
                        </select>
                    </div>

                    {{-- Filtro Vehículo --}}
                    <div class="group-form-v">
                        <label for="vehicle">Cuenta con Vehículo</label>
                        <select name="vehicle" id="vehicle" wire:model='vehicle'>
                            <option value="">Seleccione</option>
                            <option value="1">Si</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                </div>

                {{-- Fila 2: Inscripción, Género y Edad --}}
                <div class="grop-columns-3">
                    <div class="group-form-v">
                        <label for="">Inscrito</label>
                        <select name="" id="">
                            <option value="">Seleccione</option>
                            <option value="1">Si</option>
                            <option value="0">No</option>
                        </select>
                    </div>

                    <div class="group-form-v">
                        <div class="flex !justify-between !w-full">
                            <label for="gender">Genero</label>
                            <div class="!flex !items-center">
                                <input type="checkbox" name="sw_gender" id="sw_gender" wire:model='sw_gender'>
                                <span>Excluir</span>
                            </div>
                        </div>
                        <select name="gender" id="gender" wire:model='gender_id'>
                            <option value="">Seleccione</option>
                            @foreach ($genders as $gender)
                                <option value="{{ $gender->id }}">{{ $gender->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="group-form-v">
                        <div class="flex !justify-between !w-full">
                            <label for="age_range">Rango de Edad</label>
                            <div class="!flex !items-center">
                                <input type="checkbox" name="sw_age" id="sw_age" wire:model='sw_age'>
                                <span>Excluir</span>
                            </div>
                        </div>
                        <select name="age_range" id="age_range" wire:model='age_range'>
                            <option value="">Seleccione</option>
                            @foreach ($age_ranges as $age)
                                <option value="{{ $age->id }}">{{ $age->range }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <hr/>

                {{-- SECCIÓN: Ubicación Geográfica --}}
                <h4>Ubicación</h4>
                <div class="grop-columns-3">
                    {{-- Departamento: Al cambiar, debe disparar la carga de Municipios (wire:model.live) --}}
                    <div class="group-form-v">
                        <div class="flex !justify-between !w-full">
                            <label for="department">Departamento</label>
                            <div class="!flex !items-center">
                                <input type="checkbox" name="sw_department" id="sw_department" wire:model='sw_department'>
                                <span>Excluir</span>
                            </div>
                        </div>
                        <select id="department" wire:model.live="department">
                            <option value="">Seleccione</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department['id'] }}"> {{ $department['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Municipio: Se habilita solo si hay datos en la lista --}}
                    <div class="group-form-v">
                        <div class="flex !justify-between !w-full">
                            <label for="municipality">Municipio</label>
                            <div class="!flex !items-center">
                                <input type="checkbox" name="sw_municipality" id="sw_municipality" wire:model='sw_municipality'>
                                <span>Excluir</span>
                            </div>
                        </div>
                        <select id="municipality" @disabled(empty($municipalities)) wire:model.live='municipality'>
                            <option value="">Seleccione</option>
                            @foreach ($municipalities as $municipality)
                                <option value="{{ $municipality['id'] }}"> {{ $municipality['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Barrio/Vereda --}}
                    <div class="group-form-v">
                        <div class="flex !justify-between !w-full">
                            <label for="">Barrio/vereda</label>
                            <div class="!flex !items-center">
                                <input type="checkbox" name="sw_nghd" id="sw_nghd" wire:model='sw_nghd'>
                                <span>Excluir</span>
                            </div>
                        </div>
                        <select name="neighborhood" id="neighborhood" @disabled(empty($neighborhoods)) wire:model.live='neighborhood'>
                            <option value="">Seleccione</option>
                            @foreach ($neighborhoods as $neighborhood)
                                <option value="{{ $neighborhood }}">{{ $neighborhood }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <hr/>

                {{-- SECCIÓN: Búsqueda por Referentes --}}
                <h4>Usuarios</h4>
                <div class="grop-columns-2">
                    <div class="group-form-v">
                        <div class="flex !justify-between !w-full">
                            <label for="">Referidos por</label>
                            <div class="!flex !items-center">
                                <input type="checkbox" name="sw_refers" id="sw_refers" wire:model='sw_refers'>
                                <span>Excluir</span>
                            </div>
                        </div>
                        {{-- TomSelect para selección múltiple de referentes (wire:ignore para evitar que Livewire destruya el JS) --}}
                        <div class="tom-bootstrap mt-0.5 w-full" wire:ignore>
                            <select data-search-referidos multiple class="form-select clear" wire:model='refer_ids'></select>
                        </div>
                    </div>
                    
                    {{-- Por Comités (Espacio para futura implementación) --}}
                    <div class="group-form-v">
                        <div class="flex !justify-between !w-full">
                            <label for="">Por Comités</label>
                            <div class="!flex !items-center">
                                <input type="checkbox" name="" id="">
                                <span>Excluir</span>
                            </div>
                        </div>
                        <select name="" id="">
                            <option value="">Seleccione</option>
                            <option value="1">Si</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                </div>
            </div>

            <hr/>

            {{-- Errores de validación generales (ej. no seleccionar usuarios) --}}
            @error('selected')
                <div class="mb-4">
                    <x-toast.error-toast :message="$message"/>
                </div>
            @enderror

            {{-- Botonera de acciones principales --}}
            <div class="flex flex-col gap-3 w-full md:flex-row md:justify-between md:items-center">
                <a href="{{ route('list.index', session('current_campaign')->code) }}" class="button btn-secondary"><x-icons.close/> Cancelar</a>
                <div class="flex flex-col gap-3 w-full md:flex-row md:w-auto">
                    <button type="button" class="btn-secondary w-full md:w-auto flex items-center justify-center gap-2" wire:click="search">
                        <x-icons.search/> Buscar
                    </button>
                    {{-- El botón Guardar solo aparece si hay resultados de búsqueda --}}
                    @if($results)
                        <button type="button" class="btn-primary w-full md:w-auto flex items-center justify-center gap-2" wire:click="save">
                            <x-icons.save/>Guardar
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- SECCIÓN DE RESULTADOS: Se muestra dinámicamente al presionar 'Buscar' --}}
        @if ($results)
            <div class="relative">
                {{-- Loader para acciones de selección masiva --}}
                <div wire:loading wire:target="toggleSelectAll,syncSelectAll" class="absolute inset-0 z-20 cursor-progress"></div>
            </div>

            <div wire:loading.class="opacity-50" wire:target="toggleSelectAll,syncSelectAll">
                <div class="area-2 container-v mt-4">
                    
                    {{-- Herramienta para agregar usuarios específicos manualmente --}}
                    <div class="group-form-v">
                        <label for="document_number">Agregar usuario </label>
                        <div class="group-form-h gap-y-4">
                            <div class="tom-bootstrap mt-0.5 w-full" wire:ignore>
                                <select data-add-referidos multiple class="form-select clear" wire:model='add_refer_ids'></select>
                            </div>
                            <div class="items-end">
                                <button type="button" class="btn-primary !flex-nowrap" wire:click='addUser'>
                                    Agregar <x-icons.add-fill/>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Tabla de previsualización de integrantes encontrados --}}
                    <div class="bg-white container-v">
                        <table class="responsive w-full">
                            <thead>
                                <tr>
                                    <th class="w-[30px]">
                                        {{-- Checkbox Maestro para seleccionar todos los resultados --}}
                                        <input type="checkbox" wire:model="selectAll" wire:change="toggleSelectAll">
                                    </th>
                                    <th>Nro. Documento</th>
                                    <th>Nombre</th>
                                    <th>Nro. de contacto</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($results as $user)
                                    <tr>
                                        <td class="w-[30px]">
                                            {{-- Sincronización individual de selección --}}
                                            <input type="checkbox" value="{{ $user->id }}" wire:model.live="selected" wire:change="syncSelectAll">
                                        </td>
                                        <td>{{ $user->document_number }}</td>
                                        <td>{{ $user->fullName }}</td>
                                        <td>{{ $user->celphone }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- LÓGICA JAVASCRIPT: Manejo de TomSelect con eventos de Livewire --}}
    @script
        <script>
            {{-- Inicialización inmediata del buscador de referentes --}}
            (function () {
                const select = $wire.$el.querySelector('[data-search-referidos]');
                if (!select) return;

                if (select.tomselect) select.tomselect.destroy();
                
                select.tomselect = new TomSelect(select, {
                    maxItems: null,
                    plugins: ['remove_button'],
                    placeholder: 'Selecciona los referentes…',
                    valueField: 'id',
                    labelField: 'text',
                    searchField: 'text',
                    sortField: { field: 'text', direction: 'asc' },
                    options: @js($referents),
                    create: false,
                });
            })();

            {{-- Escucha el evento 'init-tom-select' disparado desde PHP tras una búsqueda exitosa --}}
            $wire.on('init-tom-select', (data) => {
                requestAnimationFrame(() => {
                    const select = $wire.$el.querySelector('[data-add-referidos]');
                    if (!select) return;
                    if (select.tomselect) select.tomselect.destroy();

                    select.tomselect = new TomSelect(select, {
                        maxItems: null,
                        plugins: ['remove_button'],
                        placeholder: 'Selecciona los referentes…',
                        valueField: 'id',
                        labelField: 'text',
                        searchField: 'text',
                        sortField: { field: 'text', direction: 'asc' },
                        options: data.notResults,
                        create: false,
                    });
                })
            });
        </script>
    @endscript
</section>