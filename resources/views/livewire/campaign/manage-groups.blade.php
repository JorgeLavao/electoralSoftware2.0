<section class="dashboard__main__section">
    <div class="breadcrumbs">
        {{ $campaign->name }} / Grupos de la Campaña
    </div>

    <div class="mb-4 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h3 class="text-xl font-semibold">Grupos de la Campaña</h3>
        </div>

        <a href="{{ route('campaign.groups.create', $campaign->code) }}" class="button btn-primary">
            <x-icons.add-fill /> Crear grupo
        </a>
    </div>

    <div class="container-v mb-4">
        @if (session()->has('success'))
        <x-toast.success-toast :message="session('success')" />
        @endif
    </div>

    <div class="container-v area-2">
        <div class="grop-columns-3">
            <div class="container-v">
                <div class="group-form-v">
                    <label for="search">Buscar grupo</label>
                    <input id="search" type="text" wire:model.live.debounce.400ms="search" placeholder="Nombre, encargado, zona o contenido">
                </div>
            </div>



            <div class="container-v">
                <div class="group-form-v">
                    <label for="statusFilter">Estado</label>
                    <select id="statusFilter" wire:model.live="statusFilter">
                        <option value="all">Todos</option>
                        <option value="active">Activos</option>
                        <option value="inactive">Inactivos</option>
                    </select>
                </div>
            </div>
        </div>

        <label class="mt-3 flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" wire:model.live="showHidden">
            Mostrar grupos ocultos
        </label>
    </div>
    <br>
    <div class="space-y-4">
        @forelse ($groups as $group)
        <details
            wire:key="group-accordion-{{ $group->id }}"
            class="group overflow-hidden rounded-[24px] bg-slate-50/80 shadow-[0_12px_30px_-28px_rgba(15,23,42,0.85)] ring-1 ring-slate-200 transition open:bg-white open:ring-primary/20 open:shadow-[0_20px_45px_-32px_rgba(244,63,94,0.45)]">
            <summary class="list-none border border-primary cursor-pointer p-0 rounded-[24px] transition hover:border-primary">
                <div class="flex flex-col gap-5 p-5 md:flex-row md:items-start md:justify-between md:p-6">
                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h4 class="text-lg font-semibold text-slate-800">{{ $group->name }}</h4>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2 text-sm">
                            @if ($group->responsible_name)
                            <span class="rounded-full bg-white px-3 py-1 text-slate-600 ring-1 ring-slate-200">
                                Encargado: {{ $group->responsible_name }}
                            </span>
                            @endif


                            @if ($group->mode === 'supporters')
                            <span class="rounded-full bg-white px-3 py-1 text-slate-600 ring-1 ring-slate-200">
                                {{ $group->users_count }} simpatizantes
                            </span>
                            @endif
                        </div>

                        @if ($group->description)
                        @endif
                    </div>

                    <div class="flex items-center justify-between gap-4 md:flex-col md:items-end">
                        <span class="rounded-full bg-white px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400 ring-1 ring-slate-200">
                            Ver detalle
                        </span>

                        <div class="flex items-center gap-2">
                            <a
                                href="{{ route('campaign.groups.edit', [$campaign->code, $group->id]) }}"
                                class="rounded-full bg-white p-2.5 text-primary shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:bg-primary hover:text-white"
                                title="Editar">
                                <x-icons.edit-2-fill :size="16" />
                            </a>
                            <a

                                class="rounded-full bg-white p-2.5 text-primary shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:bg-primary hover:text-white"
                                wire:click="toggleHidden({{ $group->id }})"
                                title="Ocultar o mostrar">
                                @if ($group->is_hidden)
                                <x-icons.eye :size="16" />
                                @else
                                <x-icons.eye-closed :size="16" />
                                @endif
                            </a>

                            <a

                                class="rounded-full bg-white p-2.5 text-primary shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:bg-primary hover:text-white"
                                wire:click="toggleActive({{ $group->id }})"
                                title="Activar o inactivar">
                                @if ($group->is_active)
                                <x-icons.close :size="16" />
                                @else
                                <x-icons.check-fill :size="16" />
                                @endif
                            </a>
                        </div>
                    </div>
                </div>
            </summary>

            <div class="bg-white px-5 pb-5 md:px-6 md:pb-6">
                <div class="h-px bg-gradient-to-r from-transparent via-slate-200 to-transparent"></div>

                <div class="mt-5 grid gap-5">
                    <div class="space-y-5">
                        @if ($group->mode === 'strategies')
                        <div class="rounded-[24px]">
                            @php
                            $strategyItems = collect(preg_split('/\r\n|\r|\n/', (string) $group->strategy_content))
                            ->map(fn ($item) => trim($item))
                            ->filter()
                            ->values();
                            @endphp

                            @if ($strategyItems->isNotEmpty())
                            <div class="grid gap-3">
                                @foreach ($strategyItems as $index => $item)
                                <div>
                                    <div class="flex gap-3">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-pink-200 text-sm font-semibold text-pink-600 shadow-sm ring-1 ring-pink-200">
                                            {{ $index + 1 }}
                                        </span>
                                        <p class="pt-1 text-sm leading-6 text-slate-700">{{ $item }}</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @elseif ($group->strategy_content)
                            <div class="rounded-[20px] bg-white/90 p-4 text-sm leading-6 text-slate-700 shadow-sm ring-1 ring-pink-100">
                                {!! nl2br(e($group->strategy_content)) !!}
                            </div>
                            @else
                            <p class="text-sm text-slate-500">Este grupo todavía no tiene estrategias escritas.</p>
                            @endif
                        </div>
                        @endif

                        @if ($group->mode === 'supporters')
                        <div>
                            <div class="mb-4 flex items-center justify-between gap-3">
                                <h5>Simpatizantes Asignados</h5>
                                <span>
                                    {{ $group->users_count }} personas
                                </span>
                            </div>

                            @if ($group->users->isNotEmpty())
                            <div class="grid gap-3 md:grid-cols-2">
                                @foreach ($group->users as $user)
                                <div>
                                    <div class="flex items-start gap-3">
                                        <div class="min-w-0">
                                            <div class="font-semibold text-slate-800">{{ $user->fullName }}</div>
                                            <div class="text-sm text-slate-500">{{ $user->document_number }}</div>
                                            @if ($user->celphone)
                                            <div class="mt-1 text-xs text-slate-400">{{ $user->celphone }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <p class="text-sm text-slate-500">Este grupo todavía no tiene simpatizantes asignados.</p>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </details>
        @empty
        <div class="rounded-[24px] bg-slate-50 px-6 py-12 text-center text-sm text-slate-500 ring-1 ring-slate-200">
            No hay grupos con los filtros actuales.
        </div>
        @endforelse
    </div>

    <x-pagination :paginator="$groups" :livewire="true" />
    </div>
</section>
