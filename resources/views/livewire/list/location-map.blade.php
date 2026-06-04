<section class="dashboard__main__section">
    <div class="breadcrumbs">
        <a href="{{ route('list.index', session('current_campaign')->code) }}" wire:navigate>Listados</a>
        / Maps
    </div>

    <article class="dashboard__main__section__article mb-24">
        <div class="relative">
            <div wire:loading class="absolute inset-0 z-20 cursor-progress rounded-2xl bg-white/40"></div>

            <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <h4 class="text-2xl font-bold text-gray-900">Geolocalización</h4>
                    <p class="mt-1 text-sm text-gray-500">
                        Visualiza en Google Maps la ubicacion de simpatizantes, lideres y coordinadores.
                    </p>
                </div>
            </div>

            <div class="mb-5 min-w-0 overflow-hidden rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <h4 class="mb-4 text-base font-bold text-gray-900">Filtros</h4>
                <div class="grid gap-3 md:grid-cols-3 xl:grid-cols-6">
                    <div class="group-form-v">
                        <label for="map-search">Nombre</label>
                        <input type="text" id="map-search" wire:model="search" placeholder="Nombre, documento o telefono">
                    </div>

                    <div class="group-form-v">
                        <label for="map-department">Departamento</label>
                        <select id="map-department" wire:model="department">
                            <option value="">Todos</option>
                            @foreach ($filterOptions['departments'] ?? [] as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="group-form-v">
                        <label for="map-municipality">Municipio</label>
                        <select id="map-municipality" wire:model="municipality">
                            <option value="">Todos</option>
                            @foreach ($filterOptions['municipalities'] ?? [] as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="group-form-v">
                        <label for="map-role">Rol</label>
                        <select id="map-role" wire:model="role">
                            <option value="">Todos</option>
                            @foreach ($filterOptions['roles'] ?? [] as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="group-form-v">
                        <label for="map-date-from">Desde</label>
                        <input type="date" id="map-date-from" wire:model="dateFrom">
                    </div>

                    <div class="group-form-v">
                        <label for="map-date-to">Hasta</label>
                        <input type="date" id="map-date-to" wire:model="dateTo">
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap justify-end gap-2">
                    <button type="button" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50" wire:click="clearFilters">
                        Limpiar
                    </button>
                    <button type="button" class="button btn-primary" wire:click="applyFilters">
                        <x-icons.search /> Buscar
                    </button>
                </div>
            </div>

            <div class="grid min-w-0 gap-5 xl:grid-cols-[minmax(0,1fr)_320px]">
                <div class="min-w-0 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    

                    <div class="max-w-full overflow-x-auto">
                        <div
                            wire:ignore
                            data-list-location-map
                            data-payload='@json($mapPayload)'
                            class="h-[540px] min-h-[420px] min-w-[640px] w-full bg-gray-100 sm:min-w-0"
                        ></div>
                    </div>
                </div>

                <aside class="grid min-w-0 gap-5">
                    <div class="min-w-0 overflow-hidden rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                        <h3 class="mb-3 text-sm font-bold text-gray-900">Departamentos</h3>
                        <div class="grid gap-3">
                            @forelse ($departmentStats as $departmentItem)
                                <div>
                                    <div class="mb-1 flex items-center justify-between text-sm">
                                        <span class="font-semibold text-gray-700">{{ $departmentItem['name'] }}</span>
                                        <span class="font-bold text-gray-900">{{ $departmentItem['total'] }}</span>
                                    </div>
                                    <div class="h-2 overflow-hidden rounded-full bg-gray-100">
                                        <div class="h-full rounded-full bg-red-500" style="width: {{ $departmentItem['percentage'] }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No hay departamentos registrados.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="min-w-0 overflow-hidden rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                        <h3 class="mb-3 text-sm font-bold text-gray-900">Municipios</h3>
                        <div class="grid gap-3">
                            @forelse ($municipalityStats as $municipalityItem)
                                <div class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 text-sm">
                                    <span class="font-semibold text-gray-700">{{ $municipalityItem['name'] }}</span>
                                    <span class="font-bold text-gray-900">{{ $municipalityItem['total'] }}</span>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No hay municipios registrados.</p>
                            @endforelse
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </article>
</section>
