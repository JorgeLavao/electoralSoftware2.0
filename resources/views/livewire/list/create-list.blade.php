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
                wire:target="applyFilters,applyReferralSearch,showGeolocation,requestExport,selectAllColumns,resetSelectedColumns,refreshExportStatus"
                class="absolute inset-0 z-30 rounded-2xl bg-white/70 backdrop-blur-sm">
            </div>

            <div
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
                }"
                class="area-2 container-v min-w-0 space-y-5 overflow-hidden">
                <div class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 space-y-5">

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="group-form-v">
                            <label for="profile_photo_filter">Foto de perfil</label>
                            <select id="profile_photo_filter" wire:model="profile_photo_filter">
                                <option value="">Todas</option>
                                <option value="with">Con foto</option>
                                <option value="without">Sin foto</option>
                            </select>
                        </div>
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
                        <p class="text-sm text-slate-500">No hay comités creados en esta campaña.</p>
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
                        <p class="text-sm text-slate-500">No hay roles creados en esta campaña.</p>
                        @endif
                    </div>

                    <div
                        class="group-form-v"
                        wire:key="list-referrals"
                        x-data
                        x-init="
                            const select = $el.querySelector('[data-search-referidos]');
                            if (select?.tomselect) {
                                select.tomselect.destroy();
                            }

                            if (select) {
                                select.tomselect = new TomSelect(select, {
                                    maxItems: null,
                                    plugins: ['remove_button'],
                                    placeholder: 'Selecciona quien refirio...',
                                    valueField: 'id',
                                    labelField: 'text',
                                    searchField: 'text',
                                    sortField: {
                                        field: 'text',
                                        direction: 'asc'
                                    },
                                    options: @js($referralOptions),
                                    items: @js($refer_ids ?? []),
                                    create: false,
                                    load: function(query, callback) {
                                        if (!query.length || query.length < 2) {
                                            return callback();
                                        }

                                        axios.get(@js($referralSearchUrl), {
                                            params: { q: query }
                                        })
                                            .then(response => callback(response.data))
                                            .catch(() => callback());
                                    },
                                    onChange(values) {
                                        $wire.set('refer_ids', values);
                                    }
                                });
                            }
                        ">
                        <div class="flex items-center justify-between gap-3">
                            <label for="referidos-select">Personas referidas por</label>
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" wire:model="sw_refers">
                                Excluir
                            </label>
                        </div>

                        <div class="relative w-full">
                            <div class="tom-bootstrap w-full pr-14" wire:ignore>
                                <select
                                    id="referidos-select"
                                    data-search-referidos
                                    multiple
                                    class="form-select clear">
                                </select>
                            </div>

                            <button
                                type="button"
                                class="btn-secondary absolute right-0 top-0 inline-flex h-11 w-11 items-center justify-center"
                                title="Buscar referidos"
                                aria-label="Buscar referidos"
                                @click="
                                    const referralSelect = $event.currentTarget.closest('.group-form-v').querySelector('[data-search-referidos]');
                                    const values = referralSelect?.tomselect?.getValue() ?? [];
                                    $wire.applyReferralSearch(Array.isArray(values) ? values : [values]);
                                ">
                                <x-icons.search />
                            </button>
                        </div>

                        <p class="text-sm text-slate-500">Escribe al menos 2 caracteres para buscar personas de la campa&ntilde;a.</p>
                    </div>

                    @unless ($showReferralAccordionResults)
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
                    @endunless
                </div>

                <div class="min-w-0 overflow-visible rounded-2xl border border-slate-200 bg-white p-4 sm:p-5">
                    <div class="grid gap-4 xl:grid-cols-[auto_1fr] xl:items-center">
                        <div class="flex min-w-0 flex-col gap-3 sm:flex-row sm:items-center">
                            @if ($hasSearched && ! $showReferralAccordionResults)
                            <label class="flex w-full items-center justify-between gap-3 rounded-lg border border-slate-200 px-4 py-2 text-sm text-slate-600 sm:w-auto sm:min-w-[230px]">
                                <span>Ver</span>
                                <select class="!w-24 !rounded-lg !py-2" wire:model.live="perPage">
                                    @foreach ($perPageOptions as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </select>
                                <span>por pagina</span>
                            </label>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:flex lg:flex-nowrap lg:justify-end">
                            <button type="button" class="btn-secondary w-full whitespace-nowrap lg:w-auto" wire:click="clearFilters">
                                Limpiar filtros
                            </button>

                            <button type="button" class="btn-secondary w-full whitespace-nowrap lg:w-auto" wire:click="applyFilters">
                                Buscar
                            </button>

                            @if ($hasSearched && $totalResults > 0 && ! $showReferralAccordionResults)
                            <button type="button" class="btn-secondary w-full whitespace-nowrap lg:w-auto" wire:click="showGeolocation">
                                Mirar Geolocalizacion
                            </button>

                            <div x-data="{ open: false }" class="relative w-full sm:col-span-2 lg:w-auto">
                                <button
                                    type="button"
                                    class="btn-primary flex w-full items-center justify-center gap-2 whitespace-nowrap lg:w-auto"
                                    @click="open = !open"
                                    @keydown.escape.window="open = false"
                                    aria-haspopup="menu"
                                    :aria-expanded="open">
                                    <x-icons.file-download-line /> Exportar
                                </button>

                                <div
                                    x-cloak
                                    x-show="open"
                                    x-transition
                                    @click.outside="open = false"
                                    class="absolute right-0 z-40 mt-2 w-full min-w-[180px] overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg lg:w-44">
                                    <button
                                        type="button"
                                        class="block w-full px-4 py-3 text-left text-sm hover:bg-slate-50"
                                        wire:click="requestExport('current_page', 'xlsx')"
                                        @click="open = false">
                                        Excel
                                    </button>
                                    <button
                                        type="button"
                                        class="block w-full px-4 py-3 text-left text-sm hover:bg-slate-50"
                                        wire:click="requestExport('current_page', 'pdf')"
                                        @click="open = false">
                                        PDF
                                    </button>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    @if ($exportBatchId)
                    <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <div>
                                <strong>Exportacion:</strong>
                                {{ match($exportStatus) {
                                        'queued' => 'en cola',
                                        'processing' => 'procesando',
                                        'done' => 'lista',
                                        'failed' => 'fallida',
                                        default => $exportStatus ?? 'pendiente',
                                    } }}

                                @if ($exportErrorMessage)
                                <p class="mt-1 text-red-600">{{ $exportErrorMessage }}</p>
                                @endif
                            </div>

                            <div class="flex gap-2">
                                <button type="button" class="btn-secondary" wire:click="refreshExportStatus">
                                    Actualizar estado
                                </button>

                                @if ($exportDownloadUrl)
                                <a href="{{ $exportDownloadUrl }}" class="btn-primary">
                                    Descargar
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                @if ($hasSearched)
                <div class="area-2 container-v min-w-0 mt-6 overflow-hidden">
                    <div class="list-results-panel rounded-2xl border border-slate-200 bg-white">
                        <div class="border-b border-slate-200 px-5 py-4">
                            <h4 class="font-semibold text-slate-900">Resultados</h4>
                        </div>

                        @if ($showReferralAccordionResults)
                        <div class="overflow-auto bg-slate-50 px-3 py-4 sm:px-5 sm:py-5" style="max-height: 72vh;">
                            <div class="mb-4 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600">
                                <div>
                                    <span class="block font-semibold text-slate-800">Referidos</span>
                                </div>
                                <div class="mt-3 flex flex-wrap gap-3">
                                    <span class="inline-flex items-center gap-2"><i class="h-3 w-3 rounded-full bg-red-600"></i>Administrador</span>
                                    <span class="inline-flex items-center gap-2"><i class="h-3 w-3 rounded-full bg-slate-500"></i>Simpatizante</span>
                                    <span class="inline-flex items-center gap-2"><i class="h-3 w-3 rounded-full bg-cyan-600"></i>Call center</span>
                                    <span class="inline-flex items-center gap-2"><i class="h-3 w-3 rounded-full bg-yellow-600"></i>Coordinador</span>
                                    <span class="inline-flex items-center gap-2"><i class="h-3 w-3 rounded-full bg-purple-600"></i>Soporte tecnico</span>
                                    <span class="inline-flex items-center gap-2"><i class="h-3 w-3 rounded-full bg-green-600"></i>Lider</span>
                                </div>
                            </div>

                            <div class="grid w-full min-w-0 gap-3">
                                @forelse ($referralAccordionTrees as $tree)
                                @include('livewire.list.partials.referral-accordion-node', ['node' => $tree])
                                @empty
                                <div class="rounded-xl border border-dashed border-slate-300 bg-white px-5 py-8 text-center text-slate-500">
                                    No se encontraron referidos para mostrar.
                                </div>
                                @endforelse
                            </div>
                        </div>
                        @elseif (count($visibleColumns) === 0)
                        <div class="px-5 py-8 text-center text-slate-500">
                            Selecciona al menos una columna para ver la tabla o exportar.
                        </div>
                        @else
                        <div class="list-results-mobile grid gap-3 bg-slate-50 px-3 py-3 md:hidden">
                            @forelse ($results as $user)
                            <article class="min-w-0 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="grid min-w-0 gap-3">
                                    @foreach ($visibleColumns as $columnKey)
                                    @if (isset($columnOptions[$columnKey]))
                                    <div class="grid min-w-0 gap-1 border-b border-slate-100 pb-3 last:border-b-0 last:pb-0">
                                        <span class="text-[11px] font-semibold uppercase text-slate-400">{{ $columnOptions[$columnKey] }}</span>

                                        <div class="min-w-0 text-sm font-medium text-slate-700">
                                            @if ($columnKey === 'profile_photo')
                                            <div class="flex items-center gap-3">
                                                @if (!empty($user['profile_photo_url']))
                                                <img class="h-10 w-10 shrink-0 rounded-full object-cover" src="{{ $user['profile_photo_url'] }}" alt="Foto de {{ $user['full_name'] ?? 'perfil' }}">
                                                @else
                                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-200 text-xs font-semibold text-slate-700">
                                                    {{ $user['profile_initials'] ?? 'US' }}
                                                </span>
                                                @endif

                                                <span class="min-w-0 truncate">{{ $user['full_name'] ?? '-' }}</span>
                                            </div>
                                            @elseif ($columnKey === 'referrals_count')
                                            <div class="flex min-w-0 flex-wrap items-center gap-2">
                                                @if (($user['referrals_count'] ?? 0) > 0)
                                                <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">
                                                    Refirio {{ $user['referrals_count'] }}
                                                </span>
                                                <button
                                                    type="button"
                                                    class="button btn-secondary !p-2"
                                                    title="Ver personas referidas"
                                                    wire:click="showReferredUsers({{ $user['id'] }})">
                                                    <x-icons.eye :size="16" />
                                                </button>
                                                @else
                                                <span>-</span>
                                                @endif
                                            </div>
                                            @elseif ($columnKey === 'referred_by')
                                            <div class="flex min-w-0 flex-wrap items-center gap-2">
                                                <span class="min-w-0 truncate">{{ $user['referred_by'] ?? '-' }}</span>
                                                @if (!empty($user['referred_by_id']))
                                                <button
                                                    type="button"
                                                    class="button btn-secondary !p-2"
                                                    title="Ver quien refirio"
                                                    wire:click="showReferrerOf({{ $user['id'] }})">
                                                    <x-icons.eye :size="16" />
                                                </button>
                                                @endif
                                            </div>
                                            @else
                                            <span class="block min-w-0 break-words">{{ $user[$columnKey] ?? '-' }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    @endif
                                    @endforeach
                                </div>
                            </article>
                            @empty
                            <div class="rounded-xl border border-dashed border-slate-300 bg-white px-5 py-8 text-center text-slate-500">
                                No se encontraron resultados.
                            </div>
                            @endforelse
                        </div>

                        <div class="list-results-scroll hidden pb-2 md:block">
                            <table class="list-results-table {{ count($visibleColumns) <= 6 ? 'list-results-table--fit' : '' }} text-sm whitespace-nowrap">
                                <thead>
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
                                            @if ($columnKey === 'profile_photo')
                                            <div class="flex items-center gap-2">
                                                @if (!empty($user['profile_photo_url']))
                                                <img class="h-9 w-9 rounded-full object-cover" src="{{ $user['profile_photo_url'] }}" alt="Foto de {{ $user['full_name'] ?? 'perfil' }}">
                                                @else
                                                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-200 text-xs font-semibold text-slate-700">
                                                    {{ $user['profile_initials'] ?? 'US' }}
                                                </span>
                                                @endif
                                            </div>
                                            @elseif ($columnKey === 'referrals_count')
                                            <div class="flex items-center gap-2">
                                                @if (($user['referrals_count'] ?? 0) > 0)
                                                <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">
                                                    Refirio {{ $user['referrals_count'] }}
                                                </span>
                                                <button
                                                    type="button"
                                                    class="button btn-secondary !p-2"
                                                    title="Ver personas referidas"
                                                    wire:click="showReferredUsers({{ $user['id'] }})">
                                                    <x-icons.eye :size="16" />
                                                </button>
                                                @else
                                                <span>-</span>
                                                @endif
                                            </div>
                                            @elseif ($columnKey === 'referred_by')
                                            <div class="flex items-center gap-2">
                                                <span class="truncate">{{ $user['referred_by'] ?? '-' }}</span>
                                                @if (!empty($user['referred_by_id']))
                                                <button
                                                    type="button"
                                                    class="button btn-secondary !p-2"
                                                    title="Ver quien refirio"
                                                    wire:click="showReferrerOf({{ $user['id'] }})">
                                                    <x-icons.eye :size="16" />
                                                </button>
                                                @endif
                                            </div>
                                            @else
                                            {{ $user[$columnKey] ?? '-' }}
                                            @endif
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

                        <div class="list-results-pagination border-t border-slate-200 px-5 py-4">
                            <x-pagination :paginator="$results" :livewire="true" />
                        </div>
                        @endif
                    </div>
                </div>

                @if ($showMap)
                <div class="area-2 container-v min-w-0 mt-6 overflow-hidden">
                    <div class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white">
                        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h4 class="font-semibold text-slate-900">Geolocalizacion</h4>
                                <p class="text-sm text-slate-500">
                                    Mostrando solo las personas encontradas con los filtros actuales.
                                </p>
                            </div>
                        </div>

                        @if (count($mapPoints) > 0)
                        <div class="max-w-full overflow-x-auto">
                            <div
                                wire:ignore
                                data-list-location-map
                                data-payload='@json($mapPayload)'
                                class="h-[540px] min-h-[420px] min-w-[640px] w-full bg-slate-100 sm:min-w-0"></div>
                        </div>
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

            <livewire:supporters.referral-details-modal :campaign="$campaign" />

        </div>
    </article>
</section>
