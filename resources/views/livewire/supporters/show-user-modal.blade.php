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
            <div wire:loading wire:target="saveUser,startEditing,cancelEdit" class="absolute inset-0 z-20 cursor-progress"></div>

            <button type="button" class="button modal-close" @click="show = false" wire:click="closeModal">
                <x-icons.close />
            </button>

            <div class="modal-inner__data space-y-5">
                <header class="section-header">
                    <div class="section-header__title">
                        <hgroup>
                            <h5 class="text-grey-400">Detalles personales y de ubicación</h5>
                            <h3 class="text-grey-400">Información del Usuario</h3>
                        </hgroup>
                    </div>
                    <hr>
                </header>

                @if (session()->has('success'))
                    <x-toast.success-toast :message="session('success')" />
                @endif

                @if ($user)
                    @php
                        $profile = $user->foreing_aditional_info;
                        $departmentName = data_get(json_decode($profile?->department, true), 'name', '-');
                        $municipalityName = data_get(json_decode($profile?->municipality, true), 'name', '-');
                    @endphp

                    @if ($isEditing)
                        <form wire:submit="saveUser" class="space-y-5">
                            <div class="grop-columns-2">
                                <div class="container-v">
                                    <div class="group-form-v">
                                        <label for="doc_type">Tipo de Documento<span class="text-red-500">*</span></label>
                                        <select id="doc_type" wire:model="doc_type">
                                            <option value="" hidden>Seleccione</option>
                                            @foreach ($documentTypes as $documentType)
                                                <option value="{{ $documentType->id }}">{{ $documentType->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('doc_type')
                                        <x-toast.error-toast :message="$message" />
                                    @enderror
                                </div>

                                <div class="container-v">
                                    <div class="group-form-v">
                                        <label for="document_number">Número de Documento<span class="text-red-500">*</span></label>
                                        <input id="document_number" type="text" wire:model="document_number">
                                    </div>
                                    @error('document_number')
                                        <x-toast.error-toast :message="$message" />
                                    @enderror
                                </div>
                            </div>

                            <div class="grop-columns-2">
                                <div class="container-v">
                                    <div class="group-form-v">
                                        <label for="first_name">Primer nombre<span class="text-red-500">*</span></label>
                                        <input id="first_name" type="text" wire:model="first_name">
                                    </div>
                                    @error('first_name')
                                        <x-toast.error-toast :message="$message" />
                                    @enderror
                                </div>

                                <div class="container-v">
                                    <div class="group-form-v">
                                        <label for="middle_name">Segundo nombre</label>
                                        <input id="middle_name" type="text" wire:model="middle_name">
                                    </div>
                                    @error('middle_name')
                                        <x-toast.error-toast :message="$message" />
                                    @enderror
                                </div>
                            </div>

                            <div class="grop-columns-2">
                                <div class="container-v">
                                    <div class="group-form-v">
                                        <label for="paternal_surname">Primer apellido<span class="text-red-500">*</span></label>
                                        <input id="paternal_surname" type="text" wire:model="paternal_surname">
                                    </div>
                                    @error('paternal_surname')
                                        <x-toast.error-toast :message="$message" />
                                    @enderror
                                </div>

                                <div class="container-v">
                                    <div class="group-form-v">
                                        <label for="maternal_surname">Segundo apellido</label>
                                        <input id="maternal_surname" type="text" wire:model="maternal_surname">
                                    </div>
                                    @error('maternal_surname')
                                        <x-toast.error-toast :message="$message" />
                                    @enderror
                                </div>
                            </div>

                            <div class="grop-columns-2">
                                <div class="container-v">
                                    <div class="group-form-v">
                                        <label for="celphone">Celular<span class="text-red-500">*</span></label>
                                        <input id="celphone" type="text" wire:model="celphone">
                                    </div>
                                    @error('celphone')
                                        <x-toast.error-toast :message="$message" />
                                    @enderror
                                </div>

                                <div class="container-v">
                                    <div class="group-form-v">
                                        <label for="email">Email<span class="text-red-500">*</span></label>
                                        <input id="email" type="email" wire:model="email">
                                    </div>
                                    @error('email')
                                        <x-toast.error-toast :message="$message" />
                                    @enderror
                                </div>
                            </div>

                            @if ($profile)
                                <hr>

                                <div class="grop-columns-2">
                                    <div class="container-v">
                                        <div class="group-form-v">
                                            <label for="gender">Género<span class="text-red-500">*</span></label>
                                            <select id="gender" wire:model="gender">
                                                <option value="" hidden>Seleccione</option>
                                                @foreach ($genders as $item)
                                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @error('gender')
                                            <x-toast.error-toast :message="$message" />
                                        @enderror
                                    </div>

                                    <div class="container-v">
                                        <div class="group-form-v">
                                            <label for="birth_date">Fecha de nacimiento</label>
                                            <input id="birth_date" type="date" wire:model="birth_date">
                                        </div>
                                        @error('birth_date')
                                            <x-toast.error-toast :message="$message" />
                                        @enderror
                                    </div>
                                </div>

                                <div class="grop-columns-2">
                                    <div class="container-v">
                                        <div class="group-form-v">
                                            <label for="age_id">Rango de Edad<span class="text-red-500">*</span></label>
                                            <select id="age_id" wire:model="age_id">
                                                <option value="" hidden>Seleccione</option>
                                                @foreach ($ageRanges as $ageRange)
                                                    <option value="{{ $ageRange->id }}">{{ $ageRange->range }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @error('age_id')
                                            <x-toast.error-toast :message="$message" />
                                        @enderror
                                    </div>

                                    <div class="container-v">
                                        <div class="group-form-v">
                                            <label for="occupation">Ocupación<span class="text-red-500">*</span></label>
                                            <select id="occupation" wire:model="occupation">
                                                <option value="" hidden>Seleccione</option>
                                                @foreach ($occupations as $item)
                                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @error('occupation')
                                            <x-toast.error-toast :message="$message" />
                                        @enderror
                                    </div>

                                    <div class="container-v">
                                        <div class="group-form-v">
                                            <label for="vehicle">Cuenta con Vehículo<span class="text-red-500">*</span></label>
                                            <select id="vehicle" wire:model="vehicle">
                                                <option value="" hidden>Seleccione</option>
                                                <option value="1">Sí</option>
                                                <option value="0">No</option>
                                            </select>
                                        </div>
                                        @error('vehicle')
                                            <x-toast.error-toast :message="$message" />
                                        @enderror
                                    </div>
                                </div>

                                <div class="container-v space-y-2">
                                    <label>Zona<span class="text-red-500">*</span></label>
                                    <div class="space-x-4 space-y-2">
                                        <label class="cursor-pointer">
                                            <input type="radio" value="rural" wire:model="zone"><span class="pl-1">Rural</span>
                                        </label>
                                        <label class="cursor-pointer">
                                            <input type="radio" value="urbana" wire:model="zone"><span class="pl-1">Urbana</span>
                                        </label>
                                    </div>
                                    @error('zone')
                                        <x-toast.error-toast :message="$message" />
                                    @enderror
                                </div>

                                <livewire:components.location-selector
                                    :initial-department-id="data_get($department, 'id')"
                                    :initial-municipality-id="data_get($municipality, 'id')"
                                    :key="'location-selector-'.$user->id.'-'.data_get($department, 'id').'-'.data_get($municipality, 'id')" />

                                <div class="grop-columns-2">
                                    <div>
                                        @error('department')
                                            <x-toast.error-toast :message="$message" />
                                        @enderror
                                    </div>
                                    <div>
                                        @error('municipality')
                                            <x-toast.error-toast :message="$message" />
                                        @enderror
                                    </div>
                                </div>

                                <div class="grop-columns-2">
                                    <div class="container-v">
                                        <div class="group-form-v">
                                            <label for="neighborhood">Barrio/Vereda<span class="text-red-500">*</span></label>
                                            <input id="neighborhood" type="text" wire:model="neighborhood">
                                        </div>
                                        @error('neighborhood')
                                            <x-toast.error-toast :message="$message" />
                                        @enderror
                                    </div>

                                    <div class="container-v">
                                        <div class="group-form-v">
                                            <label for="district">Comuna</label>
                                            <input id="district" type="text" wire:model="district">
                                        </div>
                                        @error('district')
                                            <x-toast.error-toast :message="$message" />
                                        @enderror
                                    </div>
                                </div>
                            @else
                                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                                    Este usuario no tiene perfil adicional de ubicación registrado. Desde este modal puedes editar sus datos básicos.
                                </div>
                            @endif

                            <hr>

                            <div class="flex flex-col gap-3 w-full md:flex-row md:justify-between md:items-center">
                                <span>Información Actualizada el: {{ $profile?->updated_at?->format('d/m/Y H:i') ?? $user->updated_at->format('d/m/Y H:i') }}</span>
                                <div class="flex gap-2">
                                    <button type="button" class="btn-secondary !border-gray-200 !text-gray-400" wire:click="cancelEdit">
                                        Cancelar
                                    </button>
                                    <button type="submit" class="btn-primary">
                                        Guardar cambios
                                    </button>
                                </div>
                            </div>
                        </form>
                    @else
                        <div class="grop-columns-2">
                            <div class="container-v">
                                <h4 class="flex gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 640 640" class="text-primary">
                                        <path fill="currentColor" d="M80 480V224h480v256c0 8.8-7.2 16-16 16H352c0-44.2-35.8-80-80-80h-64c-44.2 0-80 35.8-80 80H96c-8.8 0-16-7.2-16-16M96 96c-35.3 0-64 28.7-64 64v320c0 35.3 28.7 64 64 64h448c35.3 0 64-28.7 64-64V160c0-35.3-28.7-64-64-64zm144 280c30.9 0 56-25.1 56-56s-25.1-56-56-56s-56 25.1-56 56s25.1 56 56 56m168-104c-13.3 0-24 10.7-24 24s10.7 24 24 24h80c13.3 0 24-10.7 24-24s-10.7-24-24-24zm0 96c-13.3 0-24 10.7-24 24s10.7 24 24 24h80c13.3 0 24-10.7 24-24s-10.7-24-24-24z" />
                                    </svg>
                                    Datos Personales
                                </h4>
                                <ul class="list-vertical wrap-primary">
                                    <li class="!border-gray-200"><div class="flex items-start !px-4 !py-1"><div class="flex-1 min-w-0"><p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Tipo de Documento</p><p class="text-gray-800 font-medium">{{ $user->foreign_document_type->name }}</p></div></div></li>
                                    <li class="!border-gray-200"><div class="flex items-start !px-4 !py-1"><div class="flex-1 min-w-0"><p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Número de Documento</p><p class="text-gray-800 font-medium">{{ $user->document_number }}</p></div></div></li>
                                    <li class="!border-gray-200"><div class="flex items-start !px-4 !py-1"><div class="flex-1 min-w-0"><p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Primer nombre</p><p class="text-gray-800 font-medium">{{ $user->first_name }}</p></div></div></li>
                                    <li class="!border-gray-200"><div class="flex items-start !px-4 !py-1"><div class="flex-1 min-w-0"><p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Segundo nombre</p><p class="text-gray-800 font-medium">{{ $user->middle_name ?? '-' }}</p></div></div></li>
                                    <li class="!border-gray-200"><div class="flex items-start !px-4 !py-1"><div class="flex-1 min-w-0"><p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Primer Apellido</p><p class="text-gray-800 font-medium">{{ $user->paternal_surname }}</p></div></div></li>
                                    <li class="!border-gray-200"><div class="flex items-start !px-4 !py-1"><div class="flex-1 min-w-0"><p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Segundo Apellido</p><p class="text-gray-800 font-medium">{{ $user->maternal_surname ?? '-' }}</p></div></div></li>
                                </ul>
                            </div>

                            <div class="container-v">
                                <h4 class="flex gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="text-primary">
                                        <path fill="currentColor" d="M6.54 5c.06.89.21 1.76.45 2.59l-1.2 1.2c-.41-1.2-.67-2.47-.76-3.79zm9.86 12.02c.85.24 1.72.39 2.6.45v1.49c-1.32-.09-2.59-.35-3.8-.75zM7.5 3H4c-.55 0-1 .45-1 1c0 9.39 7.61 17 17 17c.55 0 1-.45 1-1v-3.49c0-.55-.45-1-1-1c-1.24 0-2.45-.2-3.57-.57a.8.8 0 0 0-.31-.05c-.26 0-.51.1-.71.29l-2.2 2.2a15.15 15.15 0 0 1-6.59-6.59l2.2-2.2c.28-.28.36-.67.25-1.02A11.4 11.4 0 0 1 8.5 4c0-.55-.45-1-1-1" />
                                    </svg>
                                    Contacto y Ubicación
                                </h4>
                                <ul class="list-vertical wrap-primary">
                                    <li class="!border-gray-200"><div class="flex items-start !px-4 !py-1"><div class="flex-1 min-w-0"><p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Celular</p><p class="text-gray-800 font-medium">{{ $user->celphone }}</p></div></div></li>
                                    <li class="!border-gray-200"><div class="flex items-start !px-4 !py-1"><div class="flex-1 min-w-0"><p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Email</p><p class="text-gray-800 font-medium">{{ $user->email }}</p></div></div></li>
                                    <li class="!border-gray-200"><div class="flex items-start !px-4 !py-1"><div class="flex-1 min-w-0"><p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Genero</p><p class="text-gray-800 font-medium">{{ $profile?->foreign_gender->name ?? '-' }}</p></div></div></li>
                                    <li class="!border-gray-200"><div class="flex items-start !px-4 !py-1"><div class="flex-1 min-w-0"><p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Fecha de nacimiento</p><p class="text-gray-800 font-medium">{{ $profile?->birth_date ? \Carbon\Carbon::parse($profile->birth_date)->format('d/m/Y') : '-' }}</p></div></div></li>
                                    <li class="!border-gray-200"><div class="flex items-start !px-4 !py-1"><div class="flex-1 min-w-0"><p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Ocupación</p><p class="text-gray-800 font-medium">{{ $profile?->foreign_occupations->name ?? '-' }}</p></div></div></li>
                                    <li class="!border-gray-200"><div class="flex items-start !px-4 !py-1"><div class="flex-1 min-w-0"><p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Rango de Edad</p><p class="text-gray-800 font-medium">{{ $profile?->foreign_range_age->range ?? '-' }}</p></div></div></li>
                                    <li class="!border-gray-200"><div class="flex items-start !px-4 !py-1"><div class="flex-1 min-w-0"><p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Cuenta con Vehículo</p><p class="text-gray-800 font-medium">{{ $profile ? ($profile->vehicle ? 'Si' : 'No') : '-' }}</p></div></div></li>
                                </ul>
                            </div>
                        </div>

                        <hr>

                        <div class="container-v">
                            <h4 class="flex gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="text-primary">
                                    <path fill="currentColor" d="M12 11.5A2.5 2.5 0 0 1 9.5 9A2.5 2.5 0 0 1 12 6.5A2.5 2.5 0 0 1 14.5 9a2.5 2.5 0 0 1-2.5 2.5M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7" />
                                </svg>
                                Ubicación Detallada
                            </h4>
                            <div class="grop-columns-2">
                                <div class="container-v">
                                    <ul class="list-vertical wrap-primary">
                                        <li class="!border-gray-200"><div class="flex items-start !px-4 !py-1"><div class="flex-1 min-w-0"><p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Zona</p><p class="text-gray-800 font-medium">{{ $profile?->zone ?? '-' }}</p></div></div></li>
                                        <li class="!border-gray-200"><div class="flex items-start !px-4 !py-1"><div class="flex-1 min-w-0"><p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Municipio</p><p class="text-gray-800 font-medium">{{ $municipalityName }}</p></div></div></li>
                                    </ul>
                                </div>
                                <div class="container-v">
                                    <ul class="list-vertical wrap-primary">
                                        <li class="!border-gray-200"><div class="flex items-start !px-4 !py-1"><div class="flex-1 min-w-0"><p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Departamento</p><p class="text-gray-800 font-medium">{{ $departmentName }}</p></div></div></li>
                                        <li class="!border-gray-200"><div class="flex items-start !px-4 !py-1"><div class="flex-1 min-w-0"><p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Comuna</p><p class="text-gray-800 font-medium">{{ $profile?->district_commune ?? '-' }}</p></div></div></li>
                                    </ul>
                                </div>
                            </div>
                            <ul class="list-vertical wrap-primary">
                                <li class="!border-gray-200"><div class="flex items-start !px-4 !py-1"><div class="flex-1 min-w-0"><p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Barrio/Vereda</p><p class="text-gray-800 font-medium">{{ $profile?->neighborhood_village_name ?? '-' }}</p></div></div></li>
                            </ul>
                        </div>

                        <hr>

                        <div class="flex flex-col gap-3 w-full md:flex-row md:justify-between md:items-center">
                            <span>Información Actualizada el: {{ $profile?->updated_at?->format('d/m/Y H:i') ?? $user->updated_at->format('d/m/Y H:i') }}</span>
                            @can('referSupporters', $campaign)
                                <button type="button" class="btn-primary" wire:click="startEditing">
                                    Editar
                                </button>
                            @endcan
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
