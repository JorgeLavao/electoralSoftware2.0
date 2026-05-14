<section class="dashboard__main__section">
    <div class="breadcrumbs">
        Listados
    </div>

    <article class="dashboard__main__section__article mb-24">
        @if (session()->has('success'))
            <div class="mb-4">
                <x-toast.success-toast :message="session('success')" />
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-4">
                <x-toast.error-toast :message="session('error')" />
            </div>
        @endif

        <div class="relative">
            <div
                wire:loading
                wire:target="applyFilters,showGeolocation,exportExcel,selectAllColumns,resetSelectedColumns"
                class="absolute inset-0 z-30 rounded-2xl bg-white/70 backdrop-blur-sm">
            </div>

            <div class="area-2 container-v space-y-5">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 space-y-5">

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="group-form-v">
                            <div class="flex justify-between items-center">
                                <label for="joined_from">Ingresaron desde</label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" wire:model="sw_joined">
                                    Excluir
                                </label>
                            </div>
                            <input id="joined_from" type="date" wire:model="joined_from">
                        </div>

                        <div class="group-form-v">
                            <label for="joined_to">Ingresaron hasta</label>
                            <input id="joined_to" type="date" wire:model="joined_to">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="group-form-v">
                            <div class="flex justify-between items-center">
                                <label for="validation_from">Fecha de validacion desde</label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" wire:model="sw_validation">
                                    Excluir
                                </label>
                            </div>
                            <input id="validation_from" type="date" wire:model="validation_from">
                        </div>

                        <div class="group-form-v">
                            <label for="validation_to">Fecha de validacion hasta</label>
                            <input id="validation_to" type="date" wire:model="validation_to">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="group-form-v">
                            <div class="flex justify-between items-center">
                                <label for="birth_month">Mes de nacimiento</label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" wire:model="sw_birth">
                                    Excluir
                                </label>
                            </div>
                            <select id="birth_month" wire:model="birth_month">
                                <option value="">Seleccione</option>
                                @foreach (range(1, 12) as $month)
                                    <option value="{{ $month }}">{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="group-form-v">
                            <label for="birth_day">Dia de nacimiento</label>
                            <select id="birth_day" wire:model="birth_day">
                                <option value="">Seleccione</option>
                                @foreach (range(1, 31) as $day)
                                    <option value="{{ $day }}">{{ str_pad($day, 2, '0', STR_PAD_LEFT) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                
                    <br>

                
                    <div class="group-form-v">
                        <div class="flex justify-between items-center">
                            <label for="searchTerm">Busqueda General</label>
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" wire:model="sw_search">
                                Excluir
                            </label>
                        </div>
                        <input
                            id="searchTerm"
                            type="text"
                            wire:model.defer="searchTerm"
                            placeholder="Nombre, apellido o cedula">
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div class="group-form-v">
                            <div class="flex justify-between items-center">
                                <label>Nivel de acercamiento</label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" wire:model="sw_approach">
                                    Excluir
                                </label>
                            </div>
                            <select wire:model="approach">
                                <option value="">Seleccione</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                            </select>
                        </div>

                        <div class="group-form-v">
                            <div class="flex justify-between items-center">
                                <label>Validado</label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" wire:model="sw_verify">
                                    Excluir
                                </label>
                            </div>
                            <select wire:model="verify">
                                <option value="">Seleccione</option>
                                <option value="1">Si</option>
                                <option value="0">No</option>
                            </select>
                        </div>

                        <div class="group-form-v">
                            <div class="flex justify-between items-center">
                                <label>Vehiculo</label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" wire:model="sw_vehicle">
                                    Excluir
                                </label>
                            </div>
                            <select wire:model="vehicle">
                                <option value="">Seleccione</option>
                                <option value="1">Si</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div class="group-form-v">
                            <div class="flex justify-between items-center">
                                <label>Genero</label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" wire:model="sw_gender">
                                    Excluir
                                </label>
                            </div>
                            <select wire:model="gender_id">
                                <option value="">Seleccione</option>
                                @foreach ($genders as $gender)
                                    <option value="{{ $gender->id }}">{{ $gender->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="group-form-v">
                            <div class="flex justify-between items-center">
                                <label>Rango de edad</label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" wire:model="sw_age">
                                    Excluir
                                </label>
                            </div>
                            <select wire:model="age_range">
                                <option value="">Seleccione</option>
                                @foreach ($age_ranges as $age)
                                    <option value="{{ $age->id }}">{{ $age->range }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="group-form-v">
                            <div class="flex justify-between items-center">
                                <label>Profesion</label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" wire:model="sw_occupation">
                                    Excluir
                                </label>
                            </div>
                            <select wire:model="occupation_id">
                                <option value="">Seleccione</option>
                                @foreach ($occupations as $occupation)
                                    <option value="{{ $occupation->id }}">{{ $occupation->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="group-form-v">
                            <div class="flex justify-between items-center">
                                <label>Departamento</label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" wire:model="sw_department">
                                    Excluir
                                </label>
                            </div>
                            <select wire:model.live="department">
                                <option value="">Seleccione</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department['id'] }}">{{ $department['name'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="group-form-v">
                            <div class="flex justify-between items-center">
                                <label>Municipio</label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" wire:model="sw_municipality">
                                    Excluir
                                </label>
                            </div>
                            <select wire:model.live="municipality" @disabled(empty($municipalities))>
                                <option value="">Seleccione</option>
                                @foreach ($municipalities as $municipality)
                                    <option value="{{ $municipality['id'] }}">{{ $municipality['name'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="group-form-v">
                            <div class="flex justify-between items-center">
                                <label>Barrio / Vereda</label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" wire:model="sw_nghd">
                                    Excluir
                                </label>
                            </div>
                            <select wire:model.live="neighborhood" @disabled(empty($neighborhoods))>
                                <option value="">Seleccione</option>
                                @foreach ($neighborhoods as $neighborhood)
                                    <option value="{{ $neighborhood }}">{{ $neighborhood }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="group-form-v">
                            <div class="flex justify-between items-center">
                                <label>Comuna / Corregimiento</label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" wire:model="sw_district">
                                    Excluir
                                </label>
                            </div>
                            <select wire:model.live="district_commune" @disabled(empty($districtsCommunes))>
                                <option value="">Seleccione</option>
                                @foreach ($districtsCommunes as $districtCommune)
                                    <option value="{{ $districtCommune }}">{{ $districtCommune }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div
                        class="group-form-v"
                        x-data
                        x-init="
                            if ($el.querySelector('#committee-select')?.tomselect) {
                                $el.querySelector('#committee-select').tomselect.destroy();
                            }

                            new TomSelect('#committee-select', {
                                valueField: 'id',
                                labelField: 'name',
                                searchField: 'name',
                                options: @js(
                                    collect($committees)->map(fn($c) => [
                                        'id' => $c->id,
                                        'name' => $c->name . ($c->is_active ? '' : ' (Inactivo)')
                                    ])
                                ),
                                items: @js($committee_ids ?? []),
                                plugins: ['remove_button'],
                                onChange(values) {
                                    $wire.set('committee_ids', values);
                                }
                            });
                        ">
                        <div class="flex items-center justify-between gap-3">
                            <label for="committee-select">Comites</label>
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" wire:model="sw_committees">
                                Excluir
                            </label>
                        </div>

                        <div class="tom-bootstrap w-full" wire:ignore>
                            <select
                                id="committee-select"
                                multiple
                                class="form-select clear"
                                @disabled(collect($committees)->isEmpty())>
                            </select>
                        </div>

                        @if (collect($committees)->isEmpty())
                            <p class="text-sm text-slate-500">No hay comites creados en esta campana.</p>
                        @endif
                    </div>

                    <div
                        class="group-form-v"
                        x-data
                        x-init="
                            if ($el.querySelector('#role-select')?.tomselect) {
                                $el.querySelector('#role-select').tomselect.destroy();
                            }

                            new TomSelect('#role-select', {
                                valueField: 'id',
                                labelField: 'name',
                                searchField: 'name',
                                options: @js(
                                    collect($roles)->map(fn($role) => [
                                        'id' => $role->id,
                                        'name' => $role->name
                                    ])
                                ),
                                items: @js($role_ids ?? []),
                                plugins: ['remove_button'],
                                onChange(values) {
                                    $wire.set('role_ids', values);
                                }
                            });
                        ">
                        <div class="flex items-center justify-between gap-3">
                            <label for="role-select">Roles</label>
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" wire:model="sw_roles">
                                Excluir
                            </label>
                        </div>

                        <div class="tom-bootstrap w-full" wire:ignore>
                            <select
                                id="role-select"
                                multiple
                                class="form-select clear"
                                @disabled(collect($roles)->isEmpty())>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        @if (collect($roles)->isEmpty())
                            <p class="text-sm text-slate-500">No hay roles creados en esta campana.</p>
                        @endif
                    </div>

                    <div class="group-form-v">
                        <div class="flex items-center justify-between gap-3">
                            <label for="referidos-select">Referidos por</label>
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" wire:model="sw_refers">
                                Excluir
                            </label>
                        </div>

                        <div class="tom-bootstrap w-full" wire:ignore>
                            <select
                                id="referidos-select"
                                data-search-referidos
                                multiple
                                class="form-select clear"
                                @disabled(collect($referents)->isEmpty())>
                            </select>
                        </div>

                        @if (collect($referents)->isEmpty())
                            <p class="text-sm text-slate-500">No hay referidos disponibles para esta campana.</p>
                        @endif
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5"
                    x-data="{
                        selectedColumns: @entangle('selectedColumns').live,
                        maxColumns: 5,
                        limitMessage: false,
                        showLimitMessage() {
                            this.limitMessage = true;
                            setTimeout(() => this.limitMessage = false, 2500);
                        },
                        toggleColumn(column, event) {
                            if (this.selectedColumns.includes(column)) {
                                this.selectedColumns = this.selectedColumns.filter((selectedColumn) => selectedColumn !== column);
                                return;
                            }

                            if (this.selectedColumns.length >= this.maxColumns) {
                                event.target.checked = false;
                                this.showLimitMessage();
                                return;
                            }

                            this.selectedColumns = [...this.selectedColumns, column];
                        }
                    }">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-5">
                        <div>
                            <p class="text-sm text-slate-500">Escoge exactamente que datos se muestran y exportan.</p>
                        </div>

                        <div class="flex gap-3">
                            <button type="button" class="btn-secondary" wire:click="selectAllColumns">
                                Primeras 5
                            </button>

                            <button type="button" class="btn-secondary" wire:click="resetSelectedColumns">
                                Limpiar
                            </button>
                        </div>
                    </div>

                    <p class="mb-2 text-sm text-slate-500">
                             <span x-text="selectedColumns.length"></span>/5
                    </p>

                    <p
                        x-cloak
                        x-show="limitMessage"
                        class="mb-4 text-sm font-medium text-red-600">
                        Solo puedes seleccionar hasta 5 columnas.
                    </p>

                    <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                        @foreach ($columnOptions as $columnKey => $columnLabel)
                            <label
                                class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 cursor-pointer hover:bg-slate-50"
                                @click="if (selectedColumns.length >= maxColumns && ! selectedColumns.includes(@js($columnKey))) showLimitMessage()">
                                <input
                                    type="checkbox"
                                    :checked="selectedColumns.includes(@js($columnKey))"
                                    :disabled="selectedColumns.length >= maxColumns && ! selectedColumns.includes(@js($columnKey))"
                                    @change="toggleColumn(@js($columnKey), $event)"
                                    value="{{ $columnKey }}">

                                <span class="text-sm text-slate-700">{{ $columnLabel }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div class="text-sm text-slate-500">
                            @if ($hasSearched)
                                {{ $results->count() }} resultado(s) encontrado(s)
                            @else
                                Presiona buscar para consultar registros.
                            @endif
                        </div>

                        <div class="flex flex-col gap-3 md:flex-row">
                            <button type="button" class="btn-secondary" wire:click="clearFilters">
                                Limpiar filtros
                            </button>

                            <button type="button" class="btn-secondary" wire:click="applyFilters">
                                Buscar
                            </button>

                            @if ($results->isNotEmpty())
                                <button type="button" class="btn-secondary" wire:click="showGeolocation">
                                    Mirar Geolocalizacion
                                </button>

                                <button type="button" class="btn-primary" wire:click="exportExcel">
                                    Exportar Excel
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                @if ($hasSearched)
                    <div class="area-2 container-v mt-6">
                        <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                            <div class="border-b border-slate-200 px-5 py-4">
                                <h4 class="font-semibold text-slate-900">Resultados</h4>
                            </div>

                            @if (count($visibleColumns) === 0)
                                <div class="px-5 py-8 text-center text-slate-500">
                                    Selecciona al menos una columna para ver la tabla o exportar.
                                </div>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-sm whitespace-nowrap">
                                        <thead class="bg-slate-50">
                                            <tr>
                                                @foreach ($visibleColumns as $columnKey)
                                                    @if (isset($columnOptions[$columnKey]))
                                                        <th class="px-4 py-3 text-left font-semibold text-slate-600">
                                                            {{ $columnOptions[$columnKey] }}
                                                        </th>
                                                    @endif
                                                @endforeach
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @forelse ($results as $user)
                                                <tr class="border-t border-slate-100 hover:bg-slate-50">
                                                    @foreach ($visibleColumns as $columnKey)
                                                        @if (isset($columnOptions[$columnKey]))
                                                            <td class="px-4 py-3 text-slate-700">
                                                                {{ $user[$columnKey] ?? '-' }}
                                                            </td>
                                                        @endif
                                                    @endforeach
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="{{ max(count($visibleColumns), 1) }}" class="px-4 py-8 text-center text-slate-500">
                                                        No se encontraron resultados.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if ($showMap)
                        <div class="area-2 container-v mt-6">
                            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                                <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 md:flex-row md:items-center md:justify-between">
                                    <div>
                                        <h4 class="font-semibold text-slate-900">Geolocalizacion</h4>
                                        <p class="text-sm text-slate-500">
                                            Mostrando solo las personas encontradas con los filtros actuales.
                                        </p>
                                    </div>
                                </div>

                                @if (count($mapPoints) > 0)
                                    <div
                                        wire:ignore
                                        data-list-location-map
                                        data-payload='@json($mapPayload)'
                                        class="h-[540px] min-h-[420px] w-full bg-slate-100"
                                    ></div>
                                @else
                                    <div class="px-5 py-8 text-center text-slate-500">
                                        Los resultados actuales no tienen coordenadas para mostrar en el mapa.
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                @endif
            </div>

            @script
            <script>
                (function() {
                    const initReferidos = () => {
                        const select = $wire.$el.querySelector('[data-search-referidos]');
                        if (!select) return;

                        if (select.tomselect) {
                            select.tomselect.destroy();
                        }

                        select.tomselect = new TomSelect(select, {
                            maxItems: null,
                            plugins: ['remove_button'],
                            placeholder: 'Selecciona los referidos...',
                            valueField: 'id',
                            labelField: 'text',
                            searchField: 'text',
                            sortField: {
                                field: 'text',
                                direction: 'asc'
                            },
                            options: @js($referents),
                            create: false,
                            onChange(values) {
                                $wire.set('refer_ids', values);
                            }
                        });

                        select.tomselect.setValue(@js($refer_ids));
                    };

                    initReferidos();
                    document.addEventListener('livewire:navigated', initReferidos);
                    document.addEventListener('livewire:load', initReferidos);
                })();
            </script>
            @endscript
        </div>
    </article>
</section>
