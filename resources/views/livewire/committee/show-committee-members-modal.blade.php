<div
    x-data="{ open: @entangle('showModal') }"
    x-effect="document.body.classList.toggle('modal-open', open)">
    @if ($showModal)
        <div class="modal-container show" wire:click="closeModal">
            <div class="modal-inner modal-md" x-on:click.stop>
                <button
                    type="button"
                    class="button modal-close"
                    wire:click="closeModal">
                    <x-icons.close />
                </button>

                <div class="modal-inner__data space-y-5">
                    <header class="section-header">
                        <div class="section-header__title">
                            <hgroup>
                                <h3 class="text-grey-400">{{ $totalMembers }} Integrantes</h3>
                                <p>{{ $committee?->name }}</p>
                            </hgroup>
                        </div>
                        <hr>
                    </header>

                    <div class="container-v area-2">
                        <div class="container-v">
                            <div class="group-form-h gap-y-4">
                                <input
                                    id="committee-members-search"
                                    type="text"
                                    wire:model="memberSearch"
                                    class="!py-3"
                                    placeholder="Digite el Nombre o Numero de Documento a Buscar">
                                <div class="items-end">
                                    <button type="button" class="btn-primary !flex-nowrap" wire:click="searchMembers">
                                        Buscar <x-icons.search />
                                    </button>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full text-left">
                                    <thead>
                                        <tr class="border-b border-slate-200 text-sm text-slate-700">
                                            <th class="px-3 py-3 font-semibold">No. de Documento</th>
                                            <th class="px-3 py-3 font-semibold">Nombre</th>
                                            <th class="px-3 py-3 font-semibold">Rol</th>
                                            <th class="px-3 py-3 font-semibold">No. de Contacto</th>
                                            <th class="px-3 py-3 font-semibold text-right">Accion</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($members as $member)
                                            <tr class="border-b border-slate-100 text-sm text-slate-600" wire:key="committee-member-{{ $member->id }}">
                                                <td class="px-3 py-4">{{ $member->document_number ?: 'Sin registro' }}</td>
                                                <td class="px-3 py-4">{{ $member->fullName }}</td>
                                                <td class="px-3 py-4">
                                                    {{ $member->pivot->role === 'administrator' ? 'Administrador' : 'Miembro' }}
                                                </td>
                                                <td class="px-3 py-4">{{ $member->celphone ?: 'Sin registro' }}</td>
                                                <td class="px-3 py-4 text-right">
                                                    @can('manageCommittees', $campaign)
                                                        <button
                                                            type="button"
                                                            class="button btn-secondary text-red-500"
                                                            wire:click="confirmRemoveMember({{ $member->id }})">
                                                            Expulsar
                                                        </button>
                                                    @endcan
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-3 py-8 text-center text-sm text-gray-400">
                                                    Este comite aun no tiene personas asignadas.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <x-pagination :paginator="$members" :livewire="true" />
                        </div>
                    </div>

                    <hr />

                    <div class="flex w-full justify-end">
                        <button type="button" class="btn-secondary" wire:click="closeModal">
                            <x-icons.close /> Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
