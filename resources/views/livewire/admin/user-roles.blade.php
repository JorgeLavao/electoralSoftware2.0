<section class="dashboard__main__section">
    <div class="breadcrumbs">Administracion / Roles y permisos</div>

    <article class="dashboard__main__section__article">
        @if (session()->has('success'))
            <x-toast.success-toast :message="session('success')" />
        @endif

        @error('role')
            <x-toast.error-toast :message="$message" />
        @enderror

        @if (! $currentCampaign)
            <div class="rounded-2xl border border-primary bg-white p-4">
                <h2 class="text-lg font-semibold">Selecciona una campaña</h2>
            </div>
        @endif

        <div class="min-w-0 overflow-hidden rounded-2xl border border-gray-100 bg-white p-4 shadow-sm md:p-5">
            <div class="flex flex-col gap-3 border-b border-gray-100 pb-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <h3 class="text-xl font-semibold text-slate-900">Roles</h3>
                    <span class="text-sm text-gray-400">Crear, editar, administrar o eliminar roles de la campaña</span>
                </div>
                <button type="button" class="btn-primary w-full justify-center sm:w-auto" wire:click="openRoleModal" @disabled(! $currentCampaign)>
                    <x-icons.add-fill /> Agregar rol
                </button>
            </div>

            <div class="mt-4 grid gap-2">
                @forelse ($roles as $role)
                    <div
                        wire:key="role-row-{{ $role->id }}"
                        class="grid min-w-0 gap-3 rounded-xl border p-3 transition hover:border-slate-200 hover:bg-slate-50 md:grid-cols-[minmax(220px,1fr)_140px_140px_auto] md:items-center {{ $selectedRoleId === $role->id ? 'border-primary bg-primary/5' : 'border-gray-100 bg-white' }}">
                        <div class="min-w-0">
                            <strong class="block truncate text-sm text-slate-900">{{ $role->name }}</strong>
                            <span class="text-xs text-gray-400">Rol de campaña</span>
                        </div>
                        <div class="flex items-center justify-between gap-2 rounded-lg bg-white px-3 py-2 text-sm md:block md:bg-transparent md:p-0">
                            <span class="text-xs font-medium uppercase tracking-normal text-gray-400">Usuarios</span>
                            <span class="font-semibold text-slate-700 md:block">{{ $role->users_count }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-2 rounded-lg bg-white px-3 py-2 text-sm md:block md:bg-transparent md:p-0">
                            <span class="text-xs font-medium uppercase tracking-normal text-gray-400">Permisos</span>
                            <span class="font-semibold text-slate-700 md:block">{{ $role->permissions->count() }}</span>
                        </div>
                        <div class="flex flex-wrap justify-end gap-2">
                            <button type="button" class="button btn-secondary" title="Administrar" wire:click="selectRole({{ $role->id }})">
                                <x-icons.user-3-fill :size="16" />
                            </button>
                            <button type="button" class="button btn-secondary" title="Editar" wire:click="openEditRoleModal({{ $role->id }})">
                                <x-icons.edit-2-fill :size="16" />
                            </button>
                            <button
                                type="button"
                                class="button btn-secondary"
                                title="Eliminar"
                                wire:click="confirmDeleteRole({{ $role->id }})">
                                <x-icons.trash-outline :size="16" />
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-gray-200 p-6 text-center text-sm text-gray-400">No hay roles.</div>
                @endforelse
            </div>
        </div>

        @if ($selectedRoleId)
            <div class="min-w-0 overflow-hidden rounded-2xl border border-gray-100 bg-white p-4 shadow-sm md:p-5">
                <div class="flex flex-col gap-3 border-b border-gray-100 pb-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="min-w-0">
                        <h3 class="truncate text-xl font-semibold text-slate-900">{{ $editingRoleName }}</h3>
                        <span class="text-sm text-gray-400">Administrar usuarios y permisos del rol</span>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="btn-primary w-full justify-center sm:w-auto" wire:click="saveRole">
                            <x-icons.save /> Guardar
                        </button>
                    </div>
                </div>

                <div class="mt-5 grid min-w-0 gap-5 xl:grid-cols-[minmax(300px,420px)_1fr]">
                    <div class="grid min-w-0 gap-4 content-start">
                        <div class="grid min-w-0 gap-3">
                            <div class="flex items-center justify-between">
                                <h3 class="text-base font-semibold text-slate-900">Usuarios asignados</h3>
                                <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-500">{{ $selectedRoleUsersCount }}</span>
                            </div>

                            <div class="group-form-v">
                                <label for="role_user_search">Buscar</label>
                                <input
                                    id="role_user_search"
                                    type="text"
                                    wire:model.live.debounce.350ms="roleUserSearch"
                                    placeholder="Nombre, correo o documento">
                            </div>

                            @if ($roleUserResults->isNotEmpty())
                                <div class="grid gap-2">
                                    @foreach ($roleUserResults as $user)
                                        <label wire:key="role-user-result-{{ $selectedRoleId }}-{{ $user->id }}" class="flex min-w-0 items-start gap-3 rounded-xl border {{ in_array((string) $user->id, $roleUserIds, true) ? 'border-primary bg-primary/5' : 'border-gray-100 bg-white' }} p-3 shadow-sm">
                                            <input type="checkbox" @checked(in_array((string) $user->id, $roleUserIds, true)) wire:change="toggleRoleUser({{ $user->id }}, $event.target.checked)">
                                            <span class="min-w-0">
                                                <span class="block truncate text-sm font-semibold">{{ $user->fullName ?: $user->email }}</span>
                                                <span class="block truncate text-xs text-gray-400">{{ $user->document_number ?: $user->email }}</span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            @elseif (mb_strlen(trim($roleUserSearch)) >= 2)
                                <div class="rounded-xl border border-gray-100 p-3 text-center text-sm text-gray-400">Sin resultados</div>
                            @endif

                            @if ($selectedRoleUsers->count() > 0)
                                <div class="grid gap-2 rounded-xl border border-gray-100 bg-slate-50 p-2">
                                    @foreach ($selectedRoleUsers as $user)
                                        <label wire:key="selected-role-user-{{ $selectedRoleId }}-{{ $user->id }}" class="flex min-w-0 items-start gap-3 rounded-lg border border-primary bg-white p-3">
                                            <input type="checkbox" @checked(! in_array((string) $user->id, $roleRemovedUserIds, true)) wire:change="toggleRoleUser({{ $user->id }}, $event.target.checked)">
                                            <span class="min-w-0">
                                                <span class="block truncate text-sm font-semibold">{{ $user->fullName ?: $user->email }}</span>
                                                <span class="block truncate text-xs text-gray-400">{{ $user->email }}</span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>

                                @if ($selectedRoleUsers->hasPages())
                                    <div class="rounded-xl border border-gray-100 bg-white p-3">
                                        {{ $selectedRoleUsers->links('vendor.livewire.tailwind') }}
                                    </div>
                                @endif
                            @else
                                <div class="rounded-xl border border-dashed border-gray-200 p-4 text-center text-sm text-gray-400">
                                    Este rol no tiene usuarios asignados.
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="grid min-w-0 gap-4 content-start">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <h3 class="text-base font-semibold text-slate-900">Permisos del rol</h3>
                                    <p class="text-sm text-slate-500">Activa los accesos que tendrá este rol dentro de la campaña.</p>
                                </div>
                                <span class="inline-flex w-fit items-center rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200">
                                    {{ count($rolePermissionIds) }} seleccionados
                                </span>
                            </div>
                        </div>

                        <div class="grid min-w-0 gap-4 lg:grid-cols-2">
                            @foreach ($permissionGroups as $group)
                                @php
                                    $ids = $group['permissions']->pluck('id')->map(fn ($id) => (string) $id)->all();
                                    $allSelected = count(array_diff($ids, $rolePermissionIds)) === 0;
                                    $selectedCount = count(array_intersect($ids, $rolePermissionIds));
                                @endphp

                                <details class="group min-w-0 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 bg-slate-50 px-4 py-3 transition hover:bg-slate-100">
                                        <div class="flex min-w-0 items-center gap-2">
                                            <span class="shrink-0 text-slate-400 transition group-open:rotate-90">
                                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707A1 1 0 018.707 5.293l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                            <span class="min-w-0">
                                                <span class="block truncate text-sm font-semibold text-slate-900">{{ $group['group_label'] }}</span>
                                                <span class="text-xs text-slate-500">{{ $selectedCount }}/{{ count($ids) }} permisos activos</span>
                                            </span>
                                        </div>

                                        <label class="inline-flex shrink-0 items-center gap-2 rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200" onclick="event.stopPropagation()">
                                            <input class="shrink-0" type="checkbox" @checked($allSelected) wire:change="toggleRoleModule('{{ $group['group_key'] }}', $event.target.checked)">
                                            Todos
                                        </label>
                                    </summary>

                                    <div class="grid min-w-0 gap-2 p-3">
                                        @foreach ($group['permissions'] as $permission)
                                            @php
                                                $permissionId = (string) $permission->id;
                                                $permissionSelected = in_array($permissionId, $rolePermissionIds, true);
                                            @endphp

                                            <label class="flex min-w-0 cursor-pointer items-start gap-3 rounded-lg border p-3 transition {{ $permissionSelected ? 'border-primary bg-primary/5' : 'border-slate-100 bg-white hover:border-slate-200 hover:bg-slate-50' }}">
                                                <input class="mt-0.5 shrink-0" type="checkbox" value="{{ $permission->id }}" wire:model.live="rolePermissionIds">
                                                <span class="min-w-0">
                                                    <span class="block text-sm font-medium leading-5 text-slate-800">{{ $permission->description ?: $permission->name }}</span>
                                                    <span class="block truncate text-xs text-slate-400">{{ $permission->name }}</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </details>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </article>

    <div class="modal-container {{ $showRoleModal ? 'show' : '' }}">
        <div class="modal-inner modal-sm">
            <button type="button" class="modal-close" wire:click="closeRoleModal">
                <x-icons.close />
            </button>

            <div class="modal-inner__data">
                <h3 class="text-xl font-semibold">{{ $roleModalMode === 'edit' ? 'Editar rol' : 'Agregar rol' }}</h3>

                <form wire:submit.prevent="createRole" class="mt-5 grid gap-4">
                    <div class="group-form-v">
                        <label for="new_role_name">Nombre</label>
                        <input id="new_role_name" type="text" wire:model="newRoleName" placeholder="Nombre del rol">
                        @error('newRoleName') <span class="text-sm text-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" class="btn-secondary" wire:click="closeRoleModal">Cancelar</button>
                        <button type="submit" class="btn-primary ml-auto">
                            <x-icons.save /> {{ $roleModalMode === 'edit' ? 'Actualizar' : 'Guardar' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
