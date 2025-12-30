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
            <div wire:loading class="absolute inset-0 z-20 cursor-progress"></div>
            <button type="button" class="button modal-close" @click="show = false" wire:click='closeModal'>
                <x-icons.close/>
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
                @if ($user)
                    <div class="grop-columns-2">
                        <div class="container-v">
                            <h4 class="flex gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 640 640" class="text-primary">
                                    <path fill="currentColor" d="M80 480V224h480v256c0 8.8-7.2 16-16 16H352c0-44.2-35.8-80-80-80h-64c-44.2 0-80 35.8-80 80H96c-8.8 0-16-7.2-16-16M96 96c-35.3 0-64 28.7-64 64v320c0 35.3 28.7 64 64 64h448c35.3 0 64-28.7 64-64V160c0-35.3-28.7-64-64-64zm144 280c30.9 0 56-25.1 56-56s-25.1-56-56-56s-56 25.1-56 56s25.1 56 56 56m168-104c-13.3 0-24 10.7-24 24s10.7 24 24 24h80c13.3 0 24-10.7 24-24s-10.7-24-24-24zm0 96c-13.3 0-24 10.7-24 24s10.7 24 24 24h80c13.3 0 24-10.7 24-24s-10.7-24-24-24z" />
                                </svg>
                                Datos Personales
                            </h4>
                            <ul class="list-vertical wrap-primary">
                                {{-- tipo documento --}}
                                <li class="!border-gray-200">
                                    <div class="flex items-start !px-4 !py-1">
                                        <div class="flex-shrink-0 mt-1 mr-3 text-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                                <path fill="currentColor" d="M15.75 13a.75.75 0 0 0-.75-.75H9a.75.75 0 0 0 0 1.5h6a.75.75 0 0 0 .75-.75m0 4a.75.75 0 0 0-.75-.75H9a.75.75 0 0 0 0 1.5h6a.75.75 0 0 0 .75-.75" />
                                                <path fill="currentColor" fill-rule="evenodd" d="M7 2.25A2.75 2.75 0 0 0 4.25 5v14A2.75 2.75 0 0 0 7 21.75h10A2.75 2.75 0 0 0 19.75 19V7.968c0-.381-.124-.751-.354-1.055l-2.998-3.968a1.75 1.75 0 0 0-1.396-.695zM5.75 5c0-.69.56-1.25 1.25-1.25h7.25v4.397c0 .414.336.75.75.75h3.25V19c0 .69-.56 1.25-1.25 1.25H7c-.69 0-1.25-.56-1.25-1.25z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Tipo de Documento</p>
                                            <p class="text-gray-800 font-medium">{{ $user->foreign_document_type->name }}</p>
                                        </div>
                                    </div>
                                </li>
                                {{-- numero documento --}}
                                <li class="!border-gray-200">
                                    <div class="flex items-start !px-4 !py-1">
                                        <div class="flex-shrink-0 mt-1 mr-3 text-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 56 56">
                                                <path fill="currentColor" d="M8.746 37.703h7.149l-2.133 10.594a4 4 0 0 0-.07.75c0 1.148.796 1.781 1.898 1.781c1.125 0 1.945-.61 2.18-1.758l2.296-11.367h11.086L29.02 48.297c-.07.234-.093.516-.093.75c0 1.148.797 1.781 1.922 1.781s1.945-.61 2.18-1.758L35.3 37.703h8.367c1.289 0 2.18-.937 2.18-2.203c0-1.031-.703-1.875-1.758-1.875h-7.946L38.63 21.25h8.203c1.29 0 2.18-.937 2.18-2.203c0-1.031-.703-1.875-1.758-1.875H39.45l1.922-9.445c.023-.141.07-.446.07-.75c0-1.149-.82-1.805-1.945-1.805c-1.312 0-1.898.726-2.133 1.828l-2.062 10.172H24.215l1.922-9.445c.023-.141.07-.446.07-.75c0-1.149-.844-1.805-1.945-1.805c-1.336 0-1.946.726-2.157 1.828l-2.062 10.172h-7.687c-1.29 0-2.18.984-2.18 2.273c0 1.055.703 1.805 1.758 1.805h7.289l-2.485 12.375h-7.57c-1.29 0-2.18.984-2.18 2.273c0 1.055.703 1.805 1.758 1.805m12.14-4.078l2.509-12.375H34.48l-2.508 12.375Z" />
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Número de Documento</p>
                                            <p class="text-gray-800 font-medium">{{ $user->document_number }}</p>
                                        </div>
                                    </div>
                                </li>
                                {{-- primer nombre --}}
                                <li class="!border-gray-200">
                                    <div class="flex items-start !px-4 !py-1">
                                        <div class="flex-shrink-0 mt-1 mr-3 text-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                                <circle cx="12" cy="6" r="4" fill="currentColor" />
                                                <path fill="currentColor" d="M20 17.5c0 2.485 0 4.5-8 4.5s-8-2.015-8-4.5S7.582 13 12 13s8 2.015 8 4.5" />
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Primer nombre</p>
                                            <p class="text-gray-800 font-medium">{{ $user->first_name }}</p>
                                        </div>
                                    </div>
                                </li>
                                {{-- segundo nombre --}}
                                <li class="!border-gray-200">
                                    <div class="flex items-start !px-4 !py-1">
                                        <div class="flex-shrink-0 mt-1 mr-3 text-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                                <circle cx="12" cy="6" r="4" fill="currentColor" />
                                                <path fill="currentColor" d="M20 17.5c0 2.485 0 4.5-8 4.5s-8-2.015-8-4.5S7.582 13 12 13s8 2.015 8 4.5" />
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Segundo nombre</p>
                                            <p class="text-gray-800 font-medium">{{ $user->middle_name ?? '-' }}</p>
                                        </div>
                                    </div>
                                </li>
                                {{-- primer apellido --}}
                                <li class="!border-gray-200">
                                    <div class="flex items-start !px-4 !py-1">
                                        <div class="flex-shrink-0 mt-1 mr-3 text-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                                <circle cx="12" cy="6" r="4" fill="currentColor" />
                                                <path fill="currentColor" d="M20 17.5c0 2.485 0 4.5-8 4.5s-8-2.015-8-4.5S7.582 13 12 13s8 2.015 8 4.5" />
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Primer Apellido</p>
                                            <p class="text-gray-800 font-medium">{{ $user->paternal_surname }}</p>
                                        </div>
                                    </div>
                                </li>
                                {{-- segundo apellido --}}
                                <li class="!border-gray-200">
                                    <div class="flex items-start !px-4 !py-1">
                                        <div class="flex-shrink-0 mt-1 mr-3 text-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                                <circle cx="12" cy="6" r="4" fill="currentColor" />
                                                <path fill="currentColor" d="M20 17.5c0 2.485 0 4.5-8 4.5s-8-2.015-8-4.5S7.582 13 12 13s8 2.015 8 4.5" />
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Segundo Apellido</p>
                                            <p class="text-gray-800 font-medium">{{ $user->maternal_surname ?? '-' }}</p>
                                        </div>
                                    </div>
                                </li>
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
                                {{-- celular --}}
                                <li class="!border-gray-200">
                                    <div class="flex items-start !px-4 !py-1">
                                        <div class="flex-shrink-0 mt-1 mr-3 text-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                                <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
                                                    <rect width="12.5" height="18.5" x="5.75" y="2.75" rx="3" />
                                                    <path d="M11 17.75h2" />
                                                </g>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Celular</p>
                                            <p class="text-gray-800 font-medium">{{ $user->celphone }}</p>
                                        </div>
                                    </div>
                                </li>
                                {{-- Email --}}
                                <li class="!border-gray-200">
                                    <div class="flex items-start !px-4 !py-1">
                                        <div class="flex-shrink-0 mt-1 mr-3 text-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 20 20">
                                                <path fill="currentColor" fill-rule="evenodd" d="m7.172 11.334l2.83 1.935l2.728-1.882l6.115 6.033q-.242.079-.512.08H1.667c-.22 0-.43-.043-.623-.12zM20 6.376v9.457c0 .247-.054.481-.15.692l-5.994-5.914zM0 6.429l6.042 4.132l-5.936 5.858A1.7 1.7 0 0 1 0 15.833zM18.333 2.5c.92 0 1.667.746 1.667 1.667v.586L9.998 11.648L0 4.81v-.643C0 3.247.746 2.5 1.667 2.5z" />
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Email</p>
                                            <p class="text-gray-800 font-medium">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </li>
                                {{-- genero --}}
                                <li class="!border-gray-200">
                                    <div class="flex items-start !px-4 !py-1">
                                        <div class="flex-shrink-0 mt-1 mr-3 text-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                                <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5">
                                                    <path d="M12 6.569a6 6 0 1 1-7.165-.256M8.25 17.25v6" />
                                                    <path d="M9.634 13.824a6 6 0 1 1 8.6-.9m-.491-7.932L21.75.75M18 .75h3.75V4.5M5.25 20.25h6" />
                                                </g>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Genero</p>
                                            <p class="text-gray-800 font-medium">{{ $user->foreing_aditional_info?->foreign_gender->name ?? '-' }}</p>
                                        </div>
                                    </div>
                                </li>
                                {{-- Ocupación --}}
                                <li class="!border-gray-200">
                                    <div class="flex items-start !px-4 !py-1">
                                        <div class="flex-shrink-0 mt-1 mr-3 text-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                                <path fill="currentColor" d="M3.385 19.385v-1h17.23v1zm3.23-3q-.69 0-1.152-.463T5 14.769V8.616q0-.691.463-1.153T6.616 7h3q0-.98.701-1.683q.702-.702 1.683-.702t1.683.702T14.385 7h3q.69 0 1.153.463T19 8.616v6.153q0 .69-.462 1.153t-1.153.463zm10.077-1h.693q.269 0 .442-.174q.173-.173.173-.442V8.616q0-.27-.173-.443T17.385 8h-.693zM10.5 7h3q0-.65-.425-1.075T12 5.5t-1.075.425T10.5 7m-3.192 8.385V8h-.692q-.27 0-.443.173T6 8.616v6.153q0 .27.173.443t.443.173zM8.192 8v7.385h7.616V8zm-.884 7.385h.884zm9.384 0h-.884zm-9.384 0H6zm.884 0h7.616zm8.5 0H18z" />
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Ocupación</p>
                                            <p class="text-gray-800 font-medium">{{ $user->foreing_aditional_info?->foreign_occupations->name ?? '-' }}</p>
                                        </div>
                                    </div>
                                </li>
                                {{-- edad --}}
                                <li class="!border-gray-200">
                                    <div class="flex items-start !px-4 !py-1">
                                        <div class="flex-shrink-0 mt-1 mr-3 text-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.5 5V3m-9 2V3M3.25 8h17.5M3 10.044c0-2.115 0-3.173.436-3.981a3.9 3.9 0 0 1 1.748-1.651C6.04 4 7.16 4 9.4 4h5.2c2.24 0 3.36 0 4.216.412c.753.362 1.364.94 1.748 1.65c.436.81.436 1.868.436 3.983v4.912c0 2.115 0 3.173-.436 3.981a3.9 3.9 0 0 1-1.748 1.651C17.96 21 16.84 21 14.6 21H9.4c-2.24 0-3.36 0-4.216-.412a3.9 3.9 0 0 1-1.748-1.65C3 18.128 3 17.07 3 14.955z" />
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Rango de Edad</p>
                                            <p class="text-gray-800 font-medium">{{ $user->foreing_aditional_info?->foreign_range_age->range ?? '-'}}</p>
                                        </div>
                                    </div>
                                </li>
                                {{-- vehiculo --}}
                                <li class="!border-gray-200">
                                    <div class="flex items-start !px-4 !py-1">
                                        <div class="flex-shrink-0 mt-1 mr-3 text-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 20 20">
                                                <path fill="currentColor" d="M5.518 6.026L4.86 8H8V5H6.942a1.5 1.5 0 0 0-1.424 1.026M4.528 9l-.004.01l-.266.066l-.122.03A1.5 1.5 0 0 0 3 10.562v1.688c0 .16.05.31.137.432A2.501 2.501 0 0 1 7.95 13h4.1a2.5 2.5 0 0 1 4.813-.318a.75.75 0 0 0 .137-.432v-1.213a1.5 1.5 0 0 0-1.114-1.45L13.686 9zm8.345-1l-1.239-2.228A1.5 1.5 0 0 0 10.324 5H9v3zM18 12.25a1.75 1.75 0 0 1-1.023 1.592A2.5 2.5 0 0 1 12.05 14h-4.1a2.5 2.5 0 0 1-4.927-.158A1.75 1.75 0 0 1 2 12.25v-1.688a2.5 2.5 0 0 1 1.747-2.384l.823-2.469A2.5 2.5 0 0 1 6.942 4h3.381a2.5 2.5 0 0 1 2.186 1.286l1.542 2.777l2.093.558A2.5 2.5 0 0 1 18 11.037zM5.5 12a1.5 1.5 0 1 0 0 3a1.5 1.5 0 0 0 0-3m9 0a1.5 1.5 0 1 0 0 3a1.5 1.5 0 0 0 0-3" />
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Cuenta con Vehículo</p>
                                            <p class="text-gray-800 font-medium">{{ $user->foreing_aditional_info?->vehice ? 'Si' : 'No' ?? '-' }}</p>
                                        </div>
                                    </div>
                                </li>
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
                                    {{-- zona --}}
                                    <li class="!border-gray-200">
                                        <div class="flex items-start !px-4 !py-1">
                                            <div class="flex-shrink-0 mt-1 mr-3 text-primary">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                                    <path fill="currentColor" d="M22 9L12 1L2 9v2h2v10h5v-4a3 3 0 1 1 6 0v4h5V11h2z" />
                                                </svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Zona</p>
                                                <p class="text-gray-800 font-medium">{{ $user->foreing_aditional_info?->zone ?? '-' }}</p>
                                            </div>
                                        </div>
                                    </li>
                                    {{-- municipio --}}
                                    <li class="!border-gray-200">
                                        <div class="flex items-start !px-4 !py-1">
                                            <div class="flex-shrink-0 mt-1 mr-3 text-primary">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 48 48">
                                                    <g fill="currentColor">
                                                        <path d="M22 13h-2v2h2zm2 0h2v2h-2zm6 0h-2v2h2zm-10 4h2v2h-2zm6 0h-2v2h2zm2 0h2v2h-2zm-6 4h-2v2h2zm2 0h2v2h-2zm6 0h-2v2h2zm-10 4h2v2h-2zm6 0h-2v2h2zm-6 4h2v2h-2zm6 0h-2v2h2zm-6 4h2v2h-2zm6 0h-2v2h2zm-6 4h2v2h-2zm6 0h-2v2h2zm5-7h3v-2h-3zm3 4h-3v-2h3zm-3 4h3v-2h-3z" />
                                                        <path fill-rule="evenodd" d="m17 4l16 6v14h4a1 1 0 0 1 1 1v17h1a1 1 0 1 1 0 2H9a1 1 0 1 1 0-2h1V21a1 1 0 0 1 1-1h2v-7h2v7h2zm2 2.886l12 4.5V24h-3a1 1 0 0 0-1 1v17h-8zM12 22v20h5V22zm24 20h-2v-2h-3v2h-2V26h7z" clip-rule="evenodd" />
                                                    </g>
                                                </svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Municipio</p>
                                                <p class="text-gray-800 font-medium">
                                                    {{ json_decode($user->foreing_aditional_info?->municipality)->name ?? '-' }}
                                                </p>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <div class="container-v">
                                <ul class="list-vertical wrap-primary">
                                    {{-- departamento --}}
                                    <li class="!border-gray-200">
                                        <div class="flex items-start !px-4 !py-1">
                                            <div class="flex-shrink-0 mt-1 mr-3 text-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 48 48">
                                                    <g fill="none">
                                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M4 42h40" />
                                                        <rect width="8" height="16" x="8" y="26" stroke="currentColor" stroke-linejoin="round" stroke-width="4" rx="2" />
                                                        <path stroke="currentColor" stroke-linecap="square" stroke-linejoin="round" stroke-width="4" d="M12 34h1" />
                                                        <rect width="24" height="38" x="16" y="4" stroke="currentColor" stroke-linejoin="round" stroke-width="4" rx="2" />
                                                        <path fill="currentColor" d="M22 10h4v4h-4zm8 0h4v4h-4zm-8 7h4v4h-4zm8 0h4v4h-4zm0 7h4v4h-4zm0 7h4v4h-4z" />
                                                    </g>
                                                </svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Departamento</p>
                                                <p class="text-gray-800 font-medium">
                                                    {{ json_decode($user->foreing_aditional_info?->department)->name ?? '-'}}
                                                </p>
                                            </div>
                                        </div>
                                    </li>
                                    {{-- comuna --}}
                                    <li class="!border-gray-200">
                                        <div class="flex items-start !px-4 !py-1">
                                            <div class="flex-shrink-0 mt-1 mr-3 text-primary">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m5.253 4.196l-1.227.712c-.989.573-1.483.86-1.754 1.337C2 6.722 2 7.302 2 8.464v8.164c0 1.526 0 2.29.342 2.714c.228.282.547.472.9.535c.53.095 1.18-.282 2.478-1.035c.882-.511 1.73-1.043 2.785-.898c.48.065.937.293 1.853.748l3.813 1.896c.825.41.833.412 1.75.412H18c1.886 0 2.828 0 3.414-.599c.586-.598.586-1.562.586-3.49v-6.74c0-1.927 0-2.89-.586-3.49c-.586-.598-1.528-.598-3.414-.598h-2.079c-.917 0-.925-.002-1.75-.412L10.84 4.015C9.449 3.323 8.753 2.977 8.012 3S6.6 3.415 5.253 4.196M8 3v14.5m7-11v14" />
                                                </svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Comuna</p>
                                                <p class="text-gray-800 font-medium">{{ $user->foreing_aditional_info?->district_commune ?? '-'}}</p>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <ul class="list-vertical wrap-primary">
                            <li class="!border-gray-200">
                                <div class="flex items-start !px-4 !py-1">
                                    <div class="flex-shrink-0 mt-1 mr-3 text-primary">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 512 512">
                                            <path fill="currentColor" d="M414.39 97.74A224 224 0 1 0 97.61 414.52A224 224 0 1 0 414.39 97.74M64 256.13a191.6 191.6 0 0 1 6.7-50.31c7.34 15.8 18 29.45 25.25 45.66c9.37 20.84 34.53 15.06 45.64 33.32c9.86 16.21-.67 36.71 6.71 53.67c5.36 12.31 18 15 26.72 24c8.91 9.08 8.72 21.52 10.08 33.36a305 305 0 0 0 7.45 41.27c0 .1 0 .21.08.31C117.8 411.13 64 339.8 64 256.13m192 192a193 193 0 0 1-32-2.68c.11-2.71.16-5.24.43-7c2.43-15.9 10.39-31.45 21.13-43.35c10.61-11.74 25.15-19.68 34.11-33c8.78-13 11.41-30.5 7.79-45.69c-5.33-22.44-35.82-29.93-52.26-42.1c-9.45-7-17.86-17.82-30.27-18.7c-5.72-.4-10.51.83-16.18-.63c-5.2-1.35-9.28-4.15-14.82-3.42c-10.35 1.36-16.88 12.42-28 10.92c-10.55-1.41-21.42-13.76-23.82-23.81c-3.08-12.92 7.14-17.11 18.09-18.26c4.57-.48 9.7-1 14.09.68c5.78 2.14 8.51 7.8 13.7 10.66c9.73 5.34 11.7-3.19 10.21-11.83c-2.23-12.94-4.83-18.21 6.71-27.12c8-6.14 14.84-10.58 13.56-21.61c-.76-6.48-4.31-9.41-1-15.86c2.51-4.91 9.4-9.34 13.89-12.27c11.59-7.56 49.65-7 34.1-28.16c-4.57-6.21-13-17.31-21-18.83c-10-1.89-14.44 9.27-21.41 14.19c-7.2 5.09-21.22 10.87-28.43 3c-9.7-10.59 6.43-14.06 10-21.46c1.65-3.45 0-8.24-2.78-12.75q5.41-2.28 11-4.23a15.6 15.6 0 0 0 8 3c6.69.44 13-3.18 18.84 1.38c6.48 5 11.15 11.32 19.75 12.88c8.32 1.51 17.13-3.34 19.19-11.86c1.25-5.18 0-10.65-1.2-16a190.83 190.83 0 0 1 105 32.21c-2-.76-4.39-.67-7.34.7c-6.07 2.82-14.67 10-15.38 17.12c-.81 8.08 11.11 9.22 16.77 9.22c8.5 0 17.11-3.8 14.37-13.62c-1.19-4.26-2.81-8.69-5.42-11.37a193 193 0 0 1 18 14.14c-.09.09-.18.17-.27.27c-5.76 6-12.45 10.75-16.39 18.05c-2.78 5.14-5.91 7.58-11.54 8.91c-3.1.73-6.64 1-9.24 3.08c-7.24 5.7-3.12 19.4 3.74 23.51c8.67 5.19 21.53 2.75 28.07-4.66c5.11-5.8 8.12-15.87 17.31-15.86a15.4 15.4 0 0 1 10.82 4.41c3.8 3.94 3.05 7.62 3.86 12.54c1.43 8.74 9.14 4 13.83-.41a192 192 0 0 1 9.24 18.77c-5.16 7.43-9.26 15.53-21.67 6.87c-7.43-5.19-12-12.72-21.33-15.06c-8.15-2-16.5.08-24.55 1.47c-9.15 1.59-20 2.29-26.94 9.22c-6.71 6.68-10.26 15.62-17.4 22.33c-13.81 13-19.64 27.19-10.7 45.57c8.6 17.67 26.59 27.26 46 26c19.07-1.27 38.88-12.33 38.33 15.38c-.2 9.81 1.85 16.6 4.86 25.71c2.79 8.4 2.6 16.54 3.24 25.21a158 158 0 0 0 4.74 30.07A191.75 191.75 0 0 1 256 448.13" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Barrio/Vereda</p>
                                        <p class="text-gray-800 font-medium">{{ $user->foreing_aditional_info?->neighborhood_village_name ?? '-' }}</p>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <hr>
                    <div class="flex flex-col gap-3 w-full md:flex-row md:justify-between md:items-center">
                        <span> Información Actualizada el: {{ $user->foreing_aditional_info?->updated_at->format('d/m/Y H:i') ?? $user->updated_at->format('d/m/Y H:i')}}</span>
                        <button type="button">
                            Editar
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
