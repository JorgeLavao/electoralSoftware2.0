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
                                <h3 class="text-grey-400">{{ $totalRows }} resultado(s)</h3>
                                <p>{{ $title }}</p>
                            </hgroup>
                        </div>
                        <hr>
                    </header>

                    <div class="container-v area-2">
                        <div class="container-v">
                            <div class="group-form-h gap-y-4">
                                <input
                                    id="referral-details-search"
                                    type="text"
                                    wire:model="search"
                                    class="!py-3"
                                    placeholder="Digite el nombre o numero de documento a buscar">
                                <div class="items-end">
                                    <button type="button" class="btn-primary !flex-nowrap" wire:click="searchRows">
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
                                            <th class="px-3 py-3 font-semibold">No. de Contacto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($rows as $row)
                                            <tr class="border-b border-slate-100 text-sm text-slate-600" wire:key="referral-detail-{{ $mode }}-{{ $row->id }}">
                                                <td class="px-3 py-4">{{ $row->document_number ?: 'Sin registro' }}</td>
                                                <td class="px-3 py-4">{{ $row->fullName }}</td>
                                                <td class="px-3 py-4">{{ $row->celphone ?: 'Sin registro' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="px-3 py-8 text-center text-sm text-gray-400">
                                                    No hay registros para mostrar.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if ($rows)
                                <x-pagination :paginator="$rows" :livewire="true" />
                            @endif
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
