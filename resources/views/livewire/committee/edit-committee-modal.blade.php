<div
    x-data="{ open: @entangle('showModal') }"
    x-effect="document.body.classList.toggle('modal-open', open)"
>
    @if($showModal)
        <div class="modal-container show" wire:click="closeModal">
            <div class="modal-inner modal-md" x-on:click.stop>
                <button
                    type="button"
                    class="button modal-close"
                    wire:click="closeModal"
                >
                    <x-icons.close/>
                </button>

                <div class="modal-inner__data space-y-5">
                    <header class="section-header">
                        <div class="section-header__title">
                            <hgroup>
                                <h3 class="text-grey-400">{{ $committee?->name }} - Editar</h3>
                            </hgroup>
                        </div>
                        <hr>
                    </header>

                    <form wire:submit="updateCommittee" method="POST" class="space-y-5">
                        <div class="container-v">
                            <div class="group-form-v">
                                <label for="edit-committee-name">Nombre del Comite<span class="text-red-500">*</span></label>
                                <input type="text" id="edit-committee-name" wire:model="name" placeholder="Digite el nombre del comite" required>
                            </div>
                            @error('name') <x-toast.error-toast :message="$message" /> @enderror
                        </div>

                        <div class="container-v">
                            <div class="group-form-v">
                                <label for="edit-committee-description">Descripcion del Comite<span class="text-red-500">*</span></label>
                                <textarea id="edit-committee-description" rows="4" wire:model="description" placeholder="Describe el objetivo, alcance y funcionamiento del comite" required></textarea>
                            </div>
                            @error('description') <x-toast.error-toast :message="$message" /> @enderror
                        </div>

                        <div class="container-v">
                            <livewire:components.search-users
                                :user-ids="$admin_user_ids"
                                label="Administrador del Comite"
                                :search-url="route('campaign.users.search', $campaign->code)"
                                placeholder="Busca y selecciona administradores del comite..."
                                :min-search-length="2"
                            />
                            @error('admin_user_ids') <x-toast.error-toast :message="$message" /> @enderror
                        </div>

                        <div class="container-v area-2">
                            <div class="container-v">
                                <h4>Personas del Comite</h4>
                                <p>Busca personas de la campana y agrega o quita las necesarias del comite.</p>
                            </div>

                            <div class="grop-columns-2">
                                <div class="container-v">
                                    <div class="group-form-v">
                                        <label for="edit-available-people">Disponibles</label>
                                        <input
                                            id="edit-available-people"
                                            type="text"
                                            wire:model.live.debounce.400ms="available_search"
                                            placeholder="Buscar por nombre o documento">
                                    </div>

                                    <div class="container-v area-3">
                                        <div class="container-h justify-between">
                                            <span class="item item__secondary">Resultados</span>
                                            <span class="item item__secondary">{{ $availableUsers->count() }} visibles</span>
                                        </div>

                                        @if (strlen(trim($available_search)) < 2)
                                            <div class="item item__secondary justify-center text-gray-400">
                                                Escribe al menos 2 caracteres para buscar personas de la campana.
                                            </div>
                                        @else
                                            @forelse ($availableUsers as $person)
                                                <div class="container-h item item__secondary justify-between" wire:key="edit-available-user-{{ $person->id }}">
                                                    <div class="container-v gap-1">
                                                        <strong>{{ $person->fullName }}</strong>
                                                        <span class="text-sm text-gray-400">{{ $person->document_number }}</span>
                                                        <span class="text-sm text-gray-400">{{ $person->celphone }}</span>
                                                    </div>
                                                    <div class="ml-auto flex items-center">
                                                        <button type="button" class="btn-secondary" wire:click="addMember({{ $person->id }})">
                                                            Agregar
                                                        </button>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="item item__secondary justify-center text-gray-400">
                                                    No hay personas que coincidan con la busqueda.
                                                </div>
                                            @endforelse
                                        @endif

                                        <div class="item item__secondary justify-center text-gray-400">
                                            Refina la busqueda para ver mas resultados.
                                        </div>
                                    </div>
                                </div>

                                <div class="container-v">
                                    <div class="group-form-v">
                                        <label for="edit-committee-people">En el Comite</label>
                                        <input
                                            id="edit-committee-people"
                                            type="text"
                                            wire:model.live.debounce.400ms="committee_search"
                                            placeholder="Buscar entre las personas agregadas">
                                    </div>

                                    <div class="container-v area-3">
                                        <div class="container-h justify-between">
                                            <span class="item valid">Asignadas</span>
                                            <span class="item item__secondary">{{ count($member_ids) }} personas</span>
                                        </div>

                                        @forelse ($committeePeople as $person)
                                            <div class="container-h item item__secondary justify-between" wire:key="edit-committee-user-{{ $person->id }}">
                                                <div class="container-v gap-1">
                                                    <strong>{{ $person->fullName }}</strong>
                                                    <span class="text-sm text-gray-400">{{ $person->document_number }}</span>
                                                    <span class="text-sm text-gray-400">{{ $person->celphone }}</span>
                                                </div>
                                                <div class="ml-auto flex items-center">
                                                    <button type="button" class="btn-secondary" wire:click="removeMember({{ $person->id }})">
                                                        Quitar
                                                    </button>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="item item__secondary justify-center text-gray-400">
                                                Aun no hay personas asignadas a este comite.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>

                        @error('member_ids') <x-toast.error-toast :message="$message" /> @enderror

                        @if (session()->has('error'))
                            <x-toast.error-toast :message="session('error')" />
                        @endif

                        <hr/>

                        <div class="justify-between flex w-full">
                            <button type="button" class="btn-secondary" wire:click="closeModal">
                                <x-icons.close/> Cancelar
                            </button>
                            <button type="submit" class="btn-primary">
                                <x-icons.save/> Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
