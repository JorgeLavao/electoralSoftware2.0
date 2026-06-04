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
                <h2 class="text-lg font-semibold">Selecciona una campana</h2>
            </div>
        @endif

        <div class="min-w-0 overflow-hidden rounded-2xl border border-gray-100 bg-white p-4 md:p-5">
            <div class="flex flex-col gap-3 border-b border-gray-100 pb-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-xl font-semibold">Roles</h3>
                    <span class="text-sm text-gray-400">Crear, editar, administrar o eliminar</span>
                </div>
                <button type="button" class="btn-primary" wire:click="openRoleModal" @disabled(! $currentCampaign)>
                    <x-icons.add-fill /> Agregar rol
                </button>
            </div>

            <div class="mt-4 max-w-full overflow-x-auto">
                <table class="min-w-[560px] w-full text-left">
                    <thead>
                        <tr class="border-b">
                            <th class="py-3 pr-4">Rol</th>
                            <th class="py-3 pr-4">Usuarios</th>
                            <th class="py-3 pr-4">Permisos</th>
                            <th class="py-3 pr-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                @forelse ($roles as $role)
                        <tr class="border-b {{ $selectedRoleId === $role->id ? 'bg-gray-50' : '' }}">
                            <td class="py-3 pr-4">
                                <strong class="block truncate">{{ $role->name }}</strong>
                            </td>
                            <td class="py-3 pr-4">{{ $role->users_count }}</td>
                            <td class="py-3 pr-4">{{ $role->permissions->count() }}</td>
                            <td class="py-3 pr-4">
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
                            </td>
                        </tr>
                @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-sm text-gray-400">No hay roles.</td>
                        </tr>
                @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($selectedRoleId)
            <div class="min-w-0 overflow-hidden rounded-2xl border border-gray-100 bg-white p-4 md:p-5">
                <div class="flex flex-col gap-3 border-b border-gray-100 pb-4 md:flex-row md:items-center md:justify-between">
                    <div class="min-w-0">
                        <h3 class="truncate text-xl font-semibold">{{ $editingRoleName }}</h3>
                        <span class="text-sm text-gray-400">Administrar rol</span>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="btn-primary" wire:click="saveRole">
                            <x-icons.save /> Guardar
                        </button>
                    </div>
                </div>

                <div class="mt-5 grid min-w-0 gap-5 lg:grid-cols-[minmax(260px,360px)_1fr]">
                    <div class="grid min-w-0 gap-4 content-start">
                        <div class="grid min-w-0 gap-3">
                            <div class="flex items-center justify-between">
                                <h3 class="text-base font-semibold">Usuarios</h3>
                                <span class="text-sm text-gray-400">{{ count($roleUserIds) }}</span>
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
                                        <label wire:key="role-user-result-{{ $selectedRoleId }}-{{ $user->id }}" class="flex min-w-0 items-start gap-3 rounded-xl border {{ in_array((string) $user->id, $roleUserIds, true) ? 'border-primary' : 'border-gray-100' }} p-3">
                                            <input type="checkbox" value="{{ $user->id }}" wire:model.live="roleUserIds" @checked(in_array((string) $user->id, $roleUserIds, true))>
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

                            @if ($selectedRoleUsers->isNotEmpty())
                                <div class="grid gap-2">
                                    @foreach ($selectedRoleUsers as $user)
                                        <label wire:key="selected-role-user-{{ $selectedRoleId }}-{{ $user->id }}" class="flex min-w-0 items-start gap-3 rounded-xl border border-primary p-3">
                                            <input type="checkbox" value="{{ $user->id }}" wire:model.live="roleUserIds" @checked(in_array((string) $user->id, $roleUserIds, true))>
                                            <span class="min-w-0">
                                                <span class="block truncate text-sm font-semibold">{{ $user->fullName ?: $user->email }}</span>
                                                <span class="block truncate text-xs text-gray-400">{{ $user->email }}</span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="grid min-w-0 gap-3 content-start">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-semibold">Permisos</h3>
                            <span class="text-sm text-gray-400">{{ count($rolePermissionIds) }}</span>
                        </div>

                        <div class="grid min-w-0 gap-2">
                            @foreach ($permissionGroups as $group)
                                @php
                                    $ids = $group['permissions']->pluck('id')->map(fn ($id) => (string) $id)->all();
                                    $allSelected = count(array_diff($ids, $rolePermissionIds)) === 0;
                                @endphp

                                <details class="min-w-0 rounded-xl border border-gray-100 bg-white p-3">
                                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3">
                                        <span class="truncate text-sm font-semibold">{{ $group['group_label'] }}</span>
                                        <label class="flex items-center gap-2 text-xs text-gray-400" onclick="event.stopPropagation()">
                                            <input type="checkbox" @checked($allSelected) wire:change="toggleRoleModule('{{ $group['group_key'] }}', $event.target.checked)">
                                            Todos
                                        </label>
                                    </summary>

                                    <div class="mt-3 grid min-w-0 gap-2 sm:grid-cols-2 xl:grid-cols-3">
                                        @foreach ($group['permissions'] as $permission)
                                            <label class="flex min-w-0 items-center gap-2 rounded-lg border border-gray-100 p-2">
                                                <input type="checkbox" value="{{ $permission->id }}" wire:model.live="rolePermissionIds">
                                                <span class="truncate text-sm">{{ $permission->description ?: $permission->name }}</span>
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
