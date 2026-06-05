<section class="dashboard__main__section">
    <div class="breadcrumbs">
        {{ $campaign->name }} / Comites
    </div>

    <article class="dashboard__main__section__article">
        <div class="flex flex-col gap-3 md:flex-row md:justify-end">
            @if ($showFilters)
            <button type="button" class="btn-secondary" wire:click="returnToCommittees">
                Volver a Comites
            </button>
            @else
            <button type="button" class="btn-secondary" wire:click="toggleFilters">
                <x-icons.search /> Filtrar Personas
            </button>

            <button type="button" class="btn-primary" wire:click="addCommittee">
                <x-icons.add-fill /> Agregar Comite
            </button>
            @endif
        </div>

        <div class="container-v">
            @if (session()->has('success'))
            <x-toast.success-toast :message="session('success')" />
            @endif

            @if (session()->has('error'))
            <x-toast.error-toast :message="session('error')" />
            @endif
        </div>

        @unless ($showFilters)
        <div class="container-v area-2">
            <div class="group-form-v">
                <div class="group-form-h gap-y-4">
                    <input
                        type="text"
                        id="committee-search"
                        wire:model.live.debounce.400ms="search"
                        class="!py-3"
                        placeholder="Digite el nombre del comite a buscar">
                    <div class="items-end">
                        <button type="button" class="btn-primary !flex-nowrap" wire:click="$refresh">
                            Buscar <x-icons.search />
                        </button>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                @forelse ($committees as $committee)
                <details
                    wire:key="committee-accordion-{{ $committee->id }}"
                    class="group overflow-hidden rounded-[24px] bg-white shadow-[0_12px_30px_-28px_rgba(15,23,42,0.85)] ring-1 ring-slate-200 transition open:bg-white open:ring-primary/20 open:shadow-[0_20px_45px_-32px_rgba(244,63,94,0.45)]">
                    <summary class="list-none cursor-pointer rounded-[24px] border border-primary p-0 transition hover:border-primary">
                        <div class="container-h p-5 md:p-6">
                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h4 class="text-lg font-semibold text-slate-800">{{ $committee->name }}</h4>
                                </div>

                                <div class="mt-4 flex flex-wrap gap-2 text-sm">
                                    @forelse ($committee->administrators as $administrator)
                                    <span class="rounded-full bg-white px-3 py-1 text-slate-600 ring-1 ring-slate-200">
                                        Responsable: {{ $administrator->fullName }}
                                    </span>
                                    @empty
                                    <span class="rounded-full bg-white px-3 py-1 text-slate-600 ring-1 ring-slate-200">
                                        Sin administradores asignados
                                    </span>
                                    @endforelse
                                </div>
                            </div>

                            <div class="container-v md:items-end">
                                <span class="item">
                                    Ver detalle
                                </span>

                                <div class="container-h">
                                    <button
                                        type="button"
                                        class="button btn-secondary"
                                        title="Editar"
                                        wire:click="editCommittee({{ $committee->id }})">
                                        <x-icons.edit-2-fill :size="16" />
                                    </button>
                                    <button
                                        type="button"
                                        class="button btn-secondary"
                                        title="{{ !empty($committee->is_active) ? 'Inactivar' : 'Activar' }}"
                                        wire:click="toggleCommitteeStatus({{ $committee->id }})">
                                        @if (!empty($committee->is_active))
                                        <x-icons.close :size="16" />
                                        @else
                                        <x-icons.check-fill :size="16" />
                                        @endif
                                    </button>
                                    <button
                                        type="button"
                                        class="button btn-secondary"
                                        title="Ver personas"
                                        wire:click="showCommitteeMembers({{ $committee->id }})">
                                        <x-icons.user-3-fill :size="16" />
                                    </button>
                                    <button
                                        type="button"
                                        class="button btn-secondary text-red-500"
                                        title="Eliminar"
                                        wire:click="confirmDeleteCommittee({{ $committee->id }})">
                                        <x-icons.trash-outline :size="16" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </summary>

                    <div class="bg-white px-5 pb-5 md:px-6 md:pb-6">
                        <div class="h-px bg-gradient-to-r from-transparent via-slate-200 to-transparent"></div>

                        <div class="mt-5 grid gap-5">
                            <div class="space-y-3">
                                <p class="text-sm leading-6 text-slate-700">
                                    {{ $committee->description ?? 'Este comite aun no tiene informacion detallada.' }}
                                </p>
                                <p class="text-sm text-slate-500">
                                    {{ $committee->users_count }} personas vinculadas al comite.
                                </p>
                            </div>
                        </div>
                    </div>
                </details>
                @empty
                <div class="rounded-[24px] bg-white px-6 py-12 text-center text-sm text-slate-500 ring-1 ring-slate-200">
                    No se encontraron comites registrados.
                </div>
                @endforelse
            </div>

            <x-pagination :paginator="$committees" :livewire="true" />
        </div>
        @endunless

        @if ($showFilters)
        <div class="container-v area-2">
            <div class="relative">
                <div
                    wire:loading
                    wire:target="applyFilters,assignFilteredUsersToCommittee,clearFilters,selectAllResults,clearSelectedResults"
                    class="absolute inset-0 z-30 rounded-2xl bg-white/70 backdrop-blur-sm">
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 space-y-5">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="group-form-v">
                            <label for="committee_profile_photo_filter">Foto de perfil</label>
                            <select id="committee_profile_photo_filter" wire:model="profile_photo_filter">
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
                                if ($el.querySelector('#committee-filter-select')?.tomselect) {
                                    $el.querySelector('#committee-filter-select').tomselect.destroy();
                                }

                                new TomSelect('#committee-filter-select', {
                                    valueField: 'id',
                                    labelField: 'name',
                                    searchField: 'name',
                                    options: @js(
                                        collect($committeeOptions)->map(fn($c) => [
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
                            <label for="committee-filter-select">Comites</label>
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" wire:model="sw_committees">
                                Excluir
                            </label>
                        </div>

                        <div class="tom-bootstrap w-full" wire:ignore>
                            <select
                                id="committee-filter-select"
                                multiple
                                class="form-select clear"
                                @disabled(collect($committeeOptions)->isEmpty())>
                            </select>
                        </div>
                    </div>

                    <div
                        class="group-form-v"
                        x-data
                        x-init="
                                if ($el.querySelector('#role-filter-select')?.tomselect) {
                                    $el.querySelector('#role-filter-select').tomselect.destroy();
                                }

                                new TomSelect('#role-filter-select', {
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
                            <label for="role-filter-select">Roles</label>
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" wire:model="sw_roles">
                                Excluir
                            </label>
                        </div>

                        <div class="tom-bootstrap w-full" wire:ignore>
                            <select
                                id="role-filter-select"
                                multiple
                                class="form-select clear"
                                @disabled(collect($roles)->isEmpty())>
                            </select>
                        </div>
                    </div>

                    <div
                        class="group-form-v"
                        wire:key="committee-referrals"
                        x-data
                        x-init="
                                const select = $el.querySelector('#committee-referidos-select');
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
                            <label for="committee-referidos-select">Personas referidas por</label>
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" wire:model="sw_refers">
                                Excluir
                            </label>
                        </div>

                        <div class="tom-bootstrap w-full" wire:ignore>
                            <select
                                id="committee-referidos-select"
                                multiple
                                class="form-select clear">
                            </select>
                        </div>
                        <p class="text-sm text-slate-500">Escribe al menos 2 caracteres para buscar personas de la campaña.</p>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-slate-100 pt-4 md:flex-row md:items-center md:justify-between">
                        <div class="text-sm text-slate-500">
                            @if ($hasSearched)
                            {{ $totalResults }} resultado(s) encontrado(s)
                            @else
                            Presiona buscar para consultar personas.
                            @endif
                        </div>

                        <div class="flex flex-col gap-3 md:flex-row">
                            <button type="button" class="btn-secondary" wire:click="clearFilters">
                                Limpiar filtros
                            </button>

                            <button type="button" class="btn-secondary" wire:click="applyFilters">
                                Buscar
                            </button>
                        </div>
                    </div>
                </div>

                @if ($hasSearched)
                <div class="mt-5 rounded-2xl border border-slate-200 bg-white p-5 space-y-5">
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
                        class="space-y-4">
                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <div class="flex flex-col gap-3 md:flex-row md:items-center">
                                <p class="text-sm text-slate-500">
                                    <span x-text="selectedColumns.length"></span>/5 columnas visibles
                                </p>

                                <label class="flex items-center gap-2 text-sm text-slate-600">
                                    <span>Ver</span>
                                    <select class="!w-24 !rounded-lg !py-2" wire:model.live="perPage">
                                        @foreach ($perPageOptions as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                        @endforeach
                                    </select>
                                    <span>por pagina</span>
                                </label>
                            </div>

                            <div class="flex flex-col gap-3 md:flex-row">
                                <button type="button" class="btn-secondary" wire:click="selectAllResults">
                                    Seleccionar todos
                                </button>

                                <button type="button" class="btn-secondary" wire:click="clearSelectedResults">
                                    Limpiar seleccion
                                </button>
                            </div>
                        </div>

                        <p
                            x-cloak
                            x-show="limitMessage"
                            class="text-sm font-medium text-red-600">
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

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm whitespace-nowrap">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Agregar</th>
                                    @foreach ($visibleColumns as $columnKey)
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600">
                                        {{ $columnOptions[$columnKey] }}
                                    </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($results as $user)
                                <tr class="border-t border-slate-100 hover:bg-slate-50">
                                    <td class="px-4 py-3 text-slate-700">
                                        <input
                                            type="checkbox"
                                            value="{{ $user['id'] }}"
                                            wire:model.live="selected_result_ids">
                                    </td>
                                    @foreach ($visibleColumns as $columnKey)
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
                                            <span>{{ $user['referred_by'] ?? '-' }}</span>
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
                                    @endforeach
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="{{ count($visibleColumns) + 1 }}" class="px-4 py-8 text-center text-slate-500">
                                        No se encontraron resultados.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <x-pagination :paginator="$results" :livewire="true" />

                    @can('manageCommittees', $campaign)
                    <div class="grid grid-cols-1 gap-3 border-t border-slate-100 pt-4 md:grid-cols-[minmax(0,1fr)_auto] md:items-end">
                        <div class="group-form-v">
                            <label for="target_committee_id">Agregar seleccion a comite</label>
                            <select
                                id="target_committee_id"
                                wire:model="target_committee_id"
                                @disabled(collect($committeeOptions)->isEmpty())>
                                <option value="">Seleccione un comite</option>
                                @foreach ($committeeOptions as $committee)
                                <option value="{{ $committee->id }}">{{ $committee->name }}{{ $committee->is_active ? '' : ' (Inactivo)' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button
                            type="button"
                            class="btn-primary"
                            wire:click="assignFilteredUsersToCommittee"
                            @disabled(collect($committeeOptions)->isEmpty())>
                            Agregar al comite
                        </button>
                    </div>
                    @endcan
                </div>
                @endif
            </div>
        </div>
        @endif
    </article>

    <livewire:committee.add-committee-modal :campaign="$campaign" />
    <livewire:committee.edit-committee-modal :campaign="$campaign" />
    <livewire:committee.show-committee-members-modal :campaign="$campaign" />
    <livewire:supporters.referral-details-modal :campaign="$campaign" />
</section>
