<section class="dashboard__main__section">
    <div class="breadcrumbs">
        <a href="{{ route('list.index', session('current_campaign')->code) }}" wire:navigate>Listados</a>
        / Guardados
    </div>
    <article class="dashboard__main__section__article mb-24">
        @if (session()->has('success'))
        <div>
            <x-toast.success-toast :message="session('success')" />
        </div>
        @endif
        <div class="relative">
            <div wire:loading class="absolute inset-0 z-20 cursor-progress"></div>

            @can('createLists', $campaign)
            <div class="flex justify-end md:mb-4">
                <a href="{{ route('list.index', session('current_campaign')->code) }}" class="button btn-primary" wire:navigate>
                    <x-icons.add-fill /> Nuevo Listado
                </a>
            </div>
            @endcan

            <div class="mx-auto mb-6 w-full max-w-5xl rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <h4 class="mb-4 font-semibold text-slate-900">Buscar</h4>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-[minmax(220px,1.3fr)_minmax(160px,0.8fr)_minmax(160px,0.8fr)_auto] md:items-end">
                    <div class="group-form-v min-w-0">
                        <label for="name">Por Nombre</label>
                        <input type="text" id="name" wire:model.live.debounce.400ms="searchName" placeholder="Digite el nombre a buscar">
                    </div>

                    <div class="group-form-v min-w-0">
                        <label for="start_date">Desde</label>
                        <input type="date" id="start_date" wire:model.live="start_date">
                    </div>

                    <div class="group-form-v min-w-0">
                        <label for="end_date">Hasta</label>
                        <input type="date" id="end_date" wire:model.live="end_date">
                    </div>

                    <button type="button" class="btn-primary flex w-full items-center justify-center gap-2 whitespace-nowrap md:w-auto" wire:click="$refresh">
                        <x-icons.search /> Buscar
                    </button>
                </div>
            </div>

            <ul class="list-vertical wrap-primary">
                @forelse ($lists as $list)
                <li class="mb-3 overflow-hidden rounded-xl border" wire:key="list-{{ $list->id }}">
                    <div class="flex cursor-pointer items-center justify-between bg-white p-3 transition hover:bg-gray-50" wire:click="toggleList({{ $list->id }})">
                        <div class="flex items-center gap-3">
                            <span class="font-semibold text-gray-800">
                                {{ $list->created_at->format('d/m/Y') }} - {{ $list->name }}
                            </span>
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="rounded-full bg-gray-100 px-2 py-1 text-sm">
                                {{ $list->foreign_users_count ?? $list->foreign_users()->count() }} integrantes
                            </span>

                            <span class="transition-transform duration-200 {{ $openListId === $list->id ? 'rotate-180' : '' }}">
                                ▼
                            </span>

                            <div x-data="{ open: false }" class="relative">
                                <button @click.stop="open = !open" type="button" class="rounded p-1 hover:bg-gray-200">
                                    <x-icons.more-vert />
                                </button>

                                <div x-show="open" @click.outside="open = false" class="absolute right-0 z-10 mt-2 w-40 rounded-lg border bg-white shadow-lg">
                                    <ul class="py-2 text-sm">
                                        @can('exportLists', $campaign)
                                        <li>
                                            <button type="button" class="w-full px-3 py-2 text-left hover:bg-gray-100" wire:click.stop="exportList({{ $list->id }}, 'xlsx')">
                                                Excel
                                            </button>
                                        </li>
                                        <li>
                                            <button type="button" class="w-full px-3 py-2 text-left hover:bg-gray-100" wire:click.stop="exportList({{ $list->id }}, 'pdf')">
                                                PDF
                                            </button>
                                        </li>
                                        @endcan

                                        @can('updateLists', $campaign)
                                        <li>
                                            <a href="{{ route('list.edit', [$campaign->code, $list->id]) }}" class="block px-3 py-2 text-left hover:bg-gray-100" wire:navigate>
                                                Editar
                                            </a>
                                        </li>

                                        @if (! $list->status)
                                        <li>
                                            <button type="button" class="w-full px-3 py-2 text-left text-green-600 hover:bg-gray-100" wire:click.stop="activeList({{ $list->id }})">
                                                Activar
                                            </button>
                                        </li>
                                        @else
                                        <li>
                                            <button type="button" class="w-full px-3 py-2 text-left text-yellow-600 hover:bg-gray-100" wire:click.stop="inactiveList({{ $list->id }})">
                                                Inactivar
                                            </button>
                                        </li>
                                        @endif
                                        @endcan

                                        @can('deleteLists', $campaign)
                                        <li>
                                            <button type="button" class="w-full px-3 py-2 text-left text-red-500 hover:bg-red-50" wire:click.stop="confirmDelete({{ $list->id }})">
                                                Eliminar
                                            </button>
                                        </li>
                                        @endcan
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($openListId === $list->id)
                    <div class="border-t bg-gray-50 p-3">
                        <h4 class="mb-2 font-semibold text-gray-700">Integrantes</h4>

                        <div class="max-h-60 overflow-y-auto">
                            @forelse ($listUsers as $user)
                            <div class="flex items-center justify-between border-b py-2">
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
                            <p class="py-3 text-center text-gray-400">
                                Sin usuarios
                            </p>
                            @endforelse
                        </div>
                    </div>
                    @endif
                </li>
                @empty
                <li class="rounded-xl border bg-white p-4 text-center text-gray-500">
                    No se encontraron listados registrados.
                </li>
                @endforelse
            </ul>

            <x-pagination :paginator="$lists" :livewire="true" />
        </div>
    </article>
</section>
