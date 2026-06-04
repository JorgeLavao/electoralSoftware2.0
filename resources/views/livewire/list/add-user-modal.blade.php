<div
    x-data="{
        show: @entangle('showModal'),
        init() {
            this.$watch('show', (value) => {
                if (value) {
                    document.body.classList.add('modal-open');
                } else {
                    document.body.classList.remove('modal-open');
                }
            });
        }
    }"
    wire:ignore.self>
    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        :class="{ 'show': show }"
        class="modal-container"
        tabindex="-1"
        @click="show = false">
        <div class="modal-inner modal-md" @click.stop>
            <div wire:loading wire:target="search,save" class="absolute inset-0 z-20 cursor-progress"></div>

            <button type="button" class="button modal-close" @click="show = false" wire:click="closeModal">
                <x-icons.close />
            </button>

            <div class="modal-inner__data space-y-5">

                {{-- Header --}}
                <header class="section-header">
                    <hgroup>
                        <h5 class="text-grey-400">Listado</h5>
                        <h3 class="text-grey-400">{{ $list->name ?? '' }}</h3>
                    </hgroup>
                    <hr>
                </header>

                <h4 class="text-grey-400">Agregar Integrante</h4>
                <div class="group-form-v">
                    <div class="group-form-h gap-y-4">
                        <input type="text" id="search" class="!py-3" wire:loading.attr="disabled" wire:model="searchInput" placeholder="Digite el Nombre o Número de documento a Buscar">
                        <div class="items-end">
                            <button type="button" class="btn-primary !flex-nowrap" wire:click="search">
                                Buscar <x-icons.search />
                            </button>
                        </div>
                    </div>
                </div>

                <div class="bg-white container-v">
                    <h4>Resultados</h4>

                    <table class="responsive w-full">
                        <thead>
                            <tr>
                                <th>Estatus</th>
                                <th>Documento</th>
                                <th>Nombre</th>
                                <th>Contacto</th>
                                <th>Acción</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($users as $user)
                            <tr>
                                <td data-label="Estatus">
                                    @if ($user->campaign_validate)
                                    <div class="rounded-xl border border-valid px-2 py-1 text-valid">
                                        <x-icons.check-fill />
                                    </div>
                                    @else
                                    <div class="rounded-xl border border-invalid px-2 py-1 text-invalid">
                                        <x-icons.alert-line />
                                    </div>
                                    @endif
                                </td>
                                <td data-label="Documento">{{ $user->document_number }}</td>
                                <td data-label="Nombre">{{ $user->fullName }}</td>
                                <td data-label="Contacto">{{ $user->celphone }}</td>
                                <td data-label="Accion">
                                    <button type="button" class="text-primary border-primary" wire:click="addUser({{ $user->id }})">
                                        <x-icons.add-fill /> Agregar
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-gray-500">No se encontraron usuarios.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    @if (! empty($addUsers))
                    <hr>
                    <h4>Usuarios a Agregar</h4>
                    <table class="responsive w-full">
                        <thead>
                            <tr>
                                <th class="w-[30px]"></th>
                                <th>Documento</th>
                                <th>Nombre</th>
                                <th>Contacto</th>
                                <th class="w-[135px]">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($arrayUSers as $user)
                            <tr>
                                <td data-label="Estatus">
                                    @if ($user->campaign_validate)
                                    <div class="rounded-xl border border-valid px-2 py-1 text-valid"><x-icons.check-fill /></div>
                                    @else
                                    <div class="rounded-xl border border-invalid px-2 py-1 text-invalid"><x-icons.alert-line /></div>
                                    @endif
                                </td>
                                <td data-label="Documento">{{ $user->document_number }}</td>
                                <td data-label="Nombre">{{ $user->fullName }}</td>
                                <td data-label="Contacto">{{ $user->celphone }}</td>
                                <td data-label="Accion">
                                    <button type="button" class="text-primary border-primary" wire:click="delUser({{ $user->id }})">
                                        <x-icons.trash-outline /> Quitar
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>

                <hr>

                <div class="flex w-full flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <button type="button" class="btn-secondary !border-gray-200 !text-gray-400" wire:click="closeModal">
                        <x-icons.close /> Cancelar
                    </button>
                    <button type="button" class="btn-primary" wire:click="saveList">
                        <x-icons.save /> Guardar
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>
