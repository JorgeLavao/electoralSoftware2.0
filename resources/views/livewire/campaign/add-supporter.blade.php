<section class="dashboard__main__section">
    <div class="breadcrumbs">
        <a href="">{{ $campaign->name }}</a> / Referir simpatizante
    </div>

    <div class="container-v mb-4">
        @if (session()->has('success'))
            <x-toast.success-toast :message="session('success')"/>
        @endif
    </div>

    <form wire:submit='searchUser' method="POST">
        <div class="grop-columns-2">
            <div class="container-v">
                <div class="group-form-v">
                    <label for="type_doc">Tipo de Documento<span class="text-red-500">*</span></label>
                    {{-- wire:loading.attr="disabled" bloquea el select mientras se procesa la búsqueda --}}
                    <select id="type_doc" wire:model='doc_type' required wire:loading.attr="disabled">
                        <option value="" hidden>Seleccione</option>
                        @foreach ($documents_type as $document_type)
                            <option value="{{ $document_type->id }}"> {{ $document_type->code }} - {{ $document_type->name }} </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    @error('doc_type')
                        <x-toast.error-toast :message="$message"/>
                    @enderror
                </div>
            </div>

            <div class="container-v">
                <div class="group-form-v">
                    <label for="document_number">Documento<span class="text-red-500">*</span></label>
                    <div class="group-form-h gap-y-4">
                        <input type="text" id="document_number" class="!py-3" wire:loading.attr="disabled"
                            wire:model="document_number" placeholder="Digite el documento" required="">
                        <div class="items-end">
                            <button type="submit" class="btn-primary !flex-nowrap">Buscar <x-icons.search/> </button>
                        </div>
                    </div>
                </div>
                <div>
                    @error('document_number')
                        <x-toast.error-toast :message="$message"/>
                    @enderror
                </div>
            </div>
        </div>
    </form>

    {{-- Se muestra solo si $showForm es true (después de ejecutar searchUser) --}}
    @if ($showForm)
        <div class="mt-4 p-4 rounded-lg border border-gray-300 bg-gray-50 shadow-sm" wire:loading.remove wire:target="searchUser">
            <div class="flex items-start gap-3">
                <div class="text-gray-600">
                    <x-icons.info />
                </div>
                <div class="text-gray-800">
                    <p class="font-semibold text-lg">
                        {{ $user ? 'Información del usuario' : 'Usuario no registrado' }}
                    </p>

                    <p class="mt-1">
                        {{ $user
                            ? 'Hemos encontrado un usuario con este documento. Puedes editar sus datos o enviar la invitación.'
                            : 'No existe un usuario con este documento. Puedes registrarlo e invitarlo a la campaña.'
                        }}
                    </p>

                    {{-- Muestra el nombre si el objeto $user existe --}}
                    @if($user)
                        <span class="text-sm text-gray-600 mt-1">
                            Nombre: <strong>{{ $user->first_name }} {{ $user->paternal_surname }}</strong>
                        </span>
                    @endif
                </div>
            </div>
        </div>
        
        <hr>

        {{-- Formulario de Datos del Usuario: se llena automáticamente si el usuario existe o se vacía si es nuevo --}}
        <form class="space-y-5 mt-3" wire:loading.remove wire:target="searchUser" wire:submit='sendInvitation' novalidate>
            <div class="grop-columns-2">
                <div class="container-v">
                    <div class="group-form-v">
                        <label for="first_name">Primer Nombre<span class="text-red-500">*</span></label>
                        <input type="text" id="first_name" wire:model="first_name"
                            wire:loading.attr="disabled" placeholder="Digite el primer nombre" required>
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
                        <input type="text" id="middle_name" wire:model="middle_name"
                            wire:loading.attr="disabled" placeholder="Digite el segundo nombre (opcional)">
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
                        <label for="paternal_surname">Primer Apellido<span class="text-red-500">*</span></label>
                        <input type="text" id="paternal_surname" wire:model="paternal_surname"
                            placeholder="Digite el primer apellido" required>
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
                        <input type="text" id="maternal_surname" wire:model="maternal_surname"
                            wire:loading.attr="disabled" placeholder="Digite el segundo apellido (opcional)">
                    </div>
                    <div>
                        @error('maternal_surname')
                            <x-toast.error-toast :message="$message"/>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="grop-columns-2">
                <div class="container-v">
                    <div class="group-form-v">
                        <label for="celphone">Numero de Celular<span class="text-red-500">*</span></label>
                        <input type="text" id="celphone" wire:model="celphone" placeholder="Digite el número de celular"
                            wire:loading.attr="disabled" required>
                    </div>
                    <div>
                        @error('celphone')
                            <x-toast.error-toast :message="$message"/>
                        @enderror
                    </div>
                </div>
                <div class="container-v">
                    <div class="group-form-v">
                        <label for="email">Correo Electronico<span class="text-red-500">*</span></label>
                        <input type="email" id="email" wire:model="email" placeholder="Digite el correo electrónico"
                            wire:loading.attr="disabled" required>
                    </div>
                    <div>
                        @error('email')
                            <x-toast.error-toast :message="$message"/>
                        @enderror
                    </div>
                </div>
            </div>

            <hr/>

            <div>
                @if (session()->has('error'))
                    <x-toast.error-toast :message="session('error')"/>
                    <hr/>
                @endif
            </div>

            <div class="justify-between flex w-full">
                <a href="{{ url()->previous() }}" class="button btn-secondary">
                    Cancelar
                </a>
                <button type="submit" class="btn-primary">
                    @if ($user)
                        Actualizar datos e Invitar <x-icons.send-fill/>
                    @else
                        Crear usuario e Invitar <x-icons.send-fill/>
                    @endif
                </button>
            </div>
        </form>
    @endif
</section>
