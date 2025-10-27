<div class="flex flex-col gap-2 w-full">
    <h1 class='text-center'>Smart<span class='text-primary'>E</span>lect</h1>
    @if ($page == 1)
        <p class='regular text-center'>Mensaje de Bienvenida: Bienvenido y de que trata la Plataforma.</p>
    @endif
    {{-- page 1 --}}
    @if ($page == 1)
        <div class='login-login'>
            <div class='form-login space-y-3'>
                <h3>Registro</h3>
                <div class='steps'>
                    <div class='number'>
                        <h4>1</h4>
                    </div>
                    <p class='base-bold text-grey-400'>Validar Documento de Identidad</p>
                </div>
                <hr/>
                <form wire:submit='nextStep' method="POST" class="space-y-3">
                    <div class='group-form-v'>
                        <label for="type_doc">Tipo de Documento</label>
                        <select id="type_doc" wire:model='doc_type' required wire:loading.attr="disabled">
                            <option value="">Seleccione</option>
                            @foreach ($documents_type as $document_type)
                                <option value="{{ $document_type->id }}"> {{ $document_type->code }} - {{ $document_type->name }} </option>
                            @endforeach
                        </select>
                    </div>
                    @error('doc_type')
                        <x-toast.error-toast :message="$message" />
                    @enderror
                    <input type="text" placeholder="Digite el Número de documento" id="document-number" wire:model='doc_number' required wire:loading.attr="disabled">
                    @error('doc_number')
                        <x-toast.error-toast :message="$message"/>
                    @enderror
                    <div class="flex justify-end">
                        <button type="submit" class='btn-primary'>
                            Iniciar
                            <span wire:loading.remove>
                                <x-icons.right-fill/>
                            </span>
                            <span wire:loading>
                                <x-icons.loading    wire:loading/>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
    {{-- page 2 --}}
    @if ($page == 2)
        <div class="login-register">
            <div class='form-login space-y-3'>
                <h3>Registro</h3>
                <div class='steps'>
                    <div class='number'>
                        <h4>2</h4>
                    </div>
                    <p class='base-bold text-grey-400'>Información Personal</p>
                </div>
                <hr/>
                <div class="grop-columns-2">
                    <div>
                        <span class="text-[14px]">Tipo de documento:</span>
                        @if($this->selectedDoc())
                            <span class="base-semibold"> {{ $this->selectedDoc()->code }} - {{ $this->selectedDoc()->name }} </span>
                        @endif
                    </div>
                    <div>
                        <span class="text-[14px]">Número de documento:</span>
                        <span class="base-semibold"> {{ $doc_number }} </span>
                    </div>
                </div>
                <hr/>
                <form wire:submit='nextStep' method="POST" class="space-y-3">
                    <div class="grop-columns-2">
                        <div class="container-v">
                            <div class="group-form-v">
                                <label for="first_name">Primer Nombre <span class="text-red-500">*</span></label>
                                <input type="text" placeholder="Digite su Primer Nombre" id="first_name" wire:model='first_name' required wire:loading.attr="disabled">
                            </div>
                            <div>
                                @error('first_name')
                                    <x-toast.error-toast :message="$message"/>
                                @enderror
                            </div>
                        </div>
                        <div class="container-v">
                            <div class="group-form-v">
                                <label for="middle_name">Segundo Nombre</label>
                                <input type="text" placeholder="Digite su Segundo Nombre" id="middle_name" wire:model='middle_name' wire:loading.attr="disabled">
                            </div>
                            <div>
                                @error('middle_name')
                                    <x-toast.error-toast :message="$message"/>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="grop-columns-2">
                        <div class="container-v">
                            <div class="group-form-v">
                                <label for="paternal_surname">Primer Apellido <span class="text-red-500">*</span></label>
                                <input type="text" placeholder="Digite su Primer Apellido" id="paternal_surname" wire:model='paternal_surname' required wire:loading.attr="disabled">
                            </div>
                            <div>
                                @error('paternal_surname')
                                    <x-toast.error-toast :message="$message"/>
                                @enderror
                            </div>
                        </div>
                        <div class="container-v">
                            <div class="group-form-v">
                                <label for="maternal_surname">Segundo Apellido</label>
                                <input type="text" placeholder="Digite su Segundo Apellido" id="maternal_surname" wire:model='maternal_surname' wire:loading.attr="disabled">
                            </div>
                            <div>
                                @error('maternal_surname')
                                   <x-toast.error-toast :message="$message"/>
                               @enderror
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class='btn-primary'>
                            Continuar
                            <span wire:loading.remove>
                                <x-icons.right-fill/>
                            </span>
                            <span wire:loading>
                                <x-icons.loading    wire:loading/>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
    {{-- page 3 --}}
    @if ($page == 3)
        <div class="login-register">
            <div class='form-login space-y-3'>
                <h3>Registro</h3>
                <div class='steps'>
                    <div class='number'>
                        <h4>3</h4>
                    </div>
                    <p class='base-bold text-grey-400'>Información de Contacto</p>
                </div>
                <hr/>
                <form wire:submit="register" class="space-y-3" method="POST">
                    <div class="grop-columns-2">
                        <div class="container-v">
                            <div class="group-form-v">
                                <label for="celphone">Número de celular <span class="text-red-500">*</span></label>
                                <input type="text" placeholder="Digite su Número de celular" id="celphone" wire:model='celphone' required wire:loading.attr="disabled">
                            </div>
                            <div>
                                @error('celphone')
                                    <x-toast.error-toast :message="$message"/>
                                @enderror
                            </div>
                        </div>
                        <div class="container-v">
                            <div class="group-form-v">
                                <label for="email">Correo electronico (Este será su Usuario)<span class="text-red-500">*</span></label>
                                <input type="email" placeholder="Digite su Correo electronico" id="email" wire:model='email' required wire:loading.attr="disabled">
                            </div>
                            <div>
                                @error('email')
                                    <x-toast.error-toast :message="$message"/>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class='steps mt-7'>
                        <div class='number'>
                            <h4>4</h4>
                        </div>
                        <p class='base-bold text-grey-400'>Configuración de acceso</p>
                    </div>
                    <hr/>
                    <div class="grop-columns-3">
                        <div class="group-form-v">
                            <label for="username">Usuario <span class="text-red-500">*</span></label>
                            <input type="email" placeholder="Su Correo electronico" id="username" wire:model='email' disabled>
                        </div>
                        <div class="container-v">
                            <div class="group-form-v">
                                <label for="pass">Contraseña <span class="text-red-500">*</span></label>
                                <x-password-input id="pass" placeholder="Digite su Contraseña" wire:model="password" autocomplete="new-password" required/>
                            </div>
                            <div>
                                @error('password')
                                    <x-toast.error-toast :message="$message"/>
                                @enderror
                            </div>
                        </div>
                        <div class="group-form-v">
                            <label for="con-pass">Confirmar Contraseña <span class="text-red-500">*</span></label>
                            <x-password-input id="con-pass" placeholder="Repita la Contraseña" wire:model="password_confirmation" autocomplete="new-password" required/>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class='btn-primary'>
                            Registrarme
                            <span wire:loading.remove>
                                <x-icons.right-fill/>
                            </span>
                            <span wire:loading>
                                <x-icons.loading    wire:loading/>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
