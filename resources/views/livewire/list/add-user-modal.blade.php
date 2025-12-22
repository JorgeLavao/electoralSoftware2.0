<div x-data="{ show: @entangle('showModal'),
    init() {
        this.$watch('show', (value) => {
            if (value) {
                document.body.classList.add('modal-open');
            } else {
                document.body.classList.remove('modal-open');
            }
        });
    }}" wire:ignore.self>
    <div x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        :class="{ 'show': show }" class="modal-container" tabindex="-1" @click="show = false">
        <div class="modal-inner modal-md" @click.stop>
            {{-- loading --}}
            <div wire:loading wire:target="search,save" class="absolute inset-0 z-20 cursor-progress"></div>
            <!-- Contenido del Modal -->
            <button type="button" class="button modal-close" @click="show = false" wire:click='closeModal'>
                <x-icons.close/>
            </button>
            <div class="modal-inner__data space-y-5">
                <header class="section-header">
                    <div class="section-header__title">
                        <hgroup>
                            <h5 class="text-grey-400">Listado</h5>
                            <h3 class="text-grey-400">Nombre de el listado</h3>
                        </hgroup>
                    </div>
                    <hr>
                </header>
                {{-- contenido --}}
                <h4 class="text-grey-400">Agregar Integrante</h4>
                <div class="group-form-v">
                    <div class="group-form-h gap-y-4">
                        <input type="text" id="search" class="!py-3" wire:loading.attr="disabled" wire:model="searchInput"
                            placeholder="Digite el Nombre o Número de documento a Buscar">
                        <div class="items-end">
                            <button type="button" class="btn-primary !flex-nowrap" wire:click='search'>
                                buscar <x-icons.search/>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="bg-white container-v">
                    <h4>Resultados</h4>
                    <table class="responsive w-full">
                        <thead>
                            <tr>
                                <th class="w-[30px]"></th>
                                <th>Documento</th>
                                <th>Nombre</th>
                                <th>Contacto</th>
                                <th class="w-[135px]"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                <tr>
                                    <td>
                                        @if ($user->campaign_validate)
                                            <div class="text-valid border-valid rounded-xl border py-1 px-2">
                                                <x-icons.check-fill />
                                            </div>
                                        @else
                                            <div class="text-invalid border-invalid rounded-xl border py-1 px-2">
                                                <x-icons.alert-line />
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $user->document_number }}</td>
                                    <td>{{ $user->fullName }}</td>
                                    <td>{{ $user->celphone }}</td>
                                    <td>
                                        <button type="button" class="text-primary border-primary" wire:click='addUser({{ $user->id }})'>
                                            <x-icons.add-fill /> Agregar
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-gray-500">
                                        No se encontraron usuarios.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    {{-- users add --}}
                    @if(!empty($addUsers))
                        <hr>
                        <h4>Usuarios a Agregar</h4>
                       <table class="responsive w-full">
                            <thead>
                                <tr>
                                    <th class="w-[30px]"></th>
                                    <th>Documento</th>
                                    <th>Nombre</th>
                                    <th>Contacto</th>
                                    <th class="w-[135px]"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($arrayUSers as $user)


                                    <tr>
                                        <td>
                                            @if ($user->campaign_validate)
                                                <div class="text-valid border-valid rounded-xl border py-1 px-2">
                                                    <x-icons.check-fill />
                                                </div>
                                            @else
                                                <div class="text-invalid border-invalid rounded-xl border py-1 px-2">
                                                    <x-icons.alert-line />
                                                </div>
                                            @endif
                                        </td>
                                        <td>{{ $user->document_number }}</td>
                                        <td>{{ $user->fullName }}</td>
                                        <td>{{ $user->celphone }}</td>
                                        <td>
                                            <button type="button" class="text-primary border-primary" wire:click='delUser({{ $user->id }})'>
                                                <x-icons.trash-outline/> Quitar
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
                <hr>
                <div class="flex flex-col gap-3 w-full md:flex-row md:justify-between md:items-center">
                    <button type="button" class="btn-secondary !text-gray-400 !border-gray-200" wire:click="closeModal">
                        <x-icons.close/> Cancelar
                    </button>
                    <button type="button" class="btn-primary" wire:click='saveList'>
                        <x-icons.save/>
                        Guardar
                    </button>
                </div>
            </div>

            </div>
        </div>
    </div>
</div>
