{{-- Alpine.js: Controla la visibilidad del modal --}}
<div x-data="{ 
    show: @entangle('showModal'),
    init() {
        this.$watch('show', (value) => {
            document.body.classList.toggle('modal-open', value);
        });
    }
}" wire:ignore.self>

    {{-- Overlay --}}
    <div x-show="show"
        x-transition
        :class="{ 'show': show }"
        class="modal-container"
        tabindex="-1"
        @click="show = false">

        {{-- Modal --}}
        <div class="modal-inner modal-md" @click.stop>

            {{-- Loading --}}
            <div wire:loading wire:target="search,saveList" class="absolute inset-0 z-20 cursor-progress"></div>

            {{-- Cerrar --}}
            <button type="button" class="button modal-close" @click="show = false" wire:click='closeModal'>
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

                {{-- Buscador --}}
                <h4 class="text-grey-400">Agregar Integrante</h4>
                <div class="group-form-h gap-y-4">
                    <input type="text"
                        class="!py-3"
                        wire:model="searchInput"
                        placeholder="Digite el nombre o documento">

                    <button type="button" class="btn-primary" wire:click='search'>
                        Buscar <x-icons.search />
                    </button>
                </div>

                {{-- TABLA RESULTADOS --}}
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
                                <td>
                                    @if ($user->campaign_validate)
                                    <div class="text-valid border-valid rounded-xl border px-2 py-1">
                                        <x-icons.check-fill />
                                    </div>
                                    @else
                                    <div class="text-invalid border-invalid rounded-xl border px-2 py-1">
                                        <x-icons.alert-line />
                                    </div>
                                    @endif
                                </td>

                                <td>{{ $user->document_number }}</td>
                                <td>{{ $user->fullName }}</td>
                                <td>{{ $user->celphone }}</td>

                                <td>
                                    <button type="button"
                                        class="text-primary border-primary"
                                        wire:click='addUser({{ $user->id }})'>
                                        <x-icons.add-fill /> Agregar
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-gray-500 py-4">
                                    No hay usuarios para mostrar
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- TABLA USUARIOS AGREGADOS --}}
                    @if(count($addUsers) > 0)
                    <hr>
                    <h4>Usuarios a Agregar</h4>

                    <table class="responsive w-full">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Documento</th>
                                <th>Nombre</th>
                                <th>Contacto</th>
                                <th>Acción</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($addUsers as $user)
                            <tr>
                                <td>
                                    @if ($user->campaign_validate)
                                    <div class="text-valid border-valid rounded-xl border px-2 py-1">
                                        <x-icons.check-fill />
                                    </div>
                                    @else
                                    <div class="text-invalid border-invalid rounded-xl border px-2 py-1">
                                        <x-icons.alert-line />
                                    </div>
                                    @endif
                                </td>

                                <td>{{ $user->document_number }}</td>
                                <td>{{ $user->fullName }}</td>
                                <td>{{ $user->celphone }}</td>

                                <td>
                                    <button type="button"
                                        class="text-primary border-primary"
                                        wire:click='delUser({{ $user->id }})'>
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

                {{-- BOTONES --}}
                <div class="flex flex-col md:flex-row justify-between gap-3">
                    <button type="button"
                        class="btn-secondary"
                        wire:click="closeModal">
                        Cancelar
                    </button>

                    <button type="button"
                        class="btn-primary"
                        wire:click='saveList'>
                        Guardar
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>