<div class="flex flex-col gap-2 w-full">
    <h1 class='text-center'>Smart<span class='text-primary'>E</span>lect</h1>
    <p class='regular text-center'>Hola {{ Auth::user()->first_name }} gracias por Registrarse, para poder seguir interactuando por favor complete la siguiente información.</p>
     <div class="login-register">
        <div class='form-login space-y-3'>
                <div class="flex w-full md:justify-between flex-col-reverse md:flex-row">
                <h3>Registro</h3>
                    <button type="submit" class="btn-secundary" form="logout-form">
                        <x-icons.log-out/>
                        Cerrar Sesión
                    </button>
            </div>
            <div class='steps'>
                <div class='number'>
                    <h4>5</h4>
                </div>
                <p class='base-bold text-grey-400'>Datos Generales</p>
            </div>
            <hr/>
            <form class="space-y-3" method="POST" wire:submit="sendForm">
                <div class="grop-columns-3">
                    <div class="container-v">
                        <div class="group-form-v">
                            <label for="gender">Género<span class="text-red-500">*</span></label>
                            <select id="gender" required wire:model='gender'>
                                <option value="" hidden>Seleccione</option>
                                @foreach ($genders as $gender)
                                    <option value="{{ $gender->id }}">{{ $gender->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            @error('gender')
                                <x-toast.error-toast :message="$message"/>
                            @enderror
                        </div>
                    </div>
                    <div class="container-v">
                        <div class="group-form-v">
                            <label for="occupation">Ocupación<span class="text-red-500">*</span></label>
                            <select id="occupation" required wire:model='occupation'>
                                <option value="" hidden>Seleccione</option>
                                @foreach ($occupations as $occupation)
                                    <option value="{{ $occupation->id }}">{{ $occupation->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            @error('occupation')
                                <x-toast.error-toast :message="$message"/>
                            @enderror
                        </div>
                    </div>
                        <div class="container-v">
                        <div class="group-form-v">
                            <label for="vehicle">Posee vehículo<span class="text-red-500">*</span></label>
                            <select id="vehicle" required wire:model='vehicle'>
                                <option value="">Seleccione</option>
                                <option value="1">Si</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                        <div>
                            @error('vehicle')
                                <x-toast.error-toast :message="$message"/>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class='steps mt-7'>
                    <div class='number'>
                        <h4>6</h4>
                    </div>
                    <p class='base-bold text-grey-400'>Ubicación</p>
                </div>
                <hr/>
                <div class="space-x-4 space-y-2">
                    <label class="cursor-pointer">
                        <input type="radio" id="zone" value="rural" wire:model='zone'><span class="pl-1">Rural</span>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" id="zone" value="urbana" wire:model='zone'><span class="pl-1">Urbana</span>
                    </label>
                    @error('zone')
                    <div class="mt-2">
                        <x-toast.error-toast :message="$message"/>
                    </div>
                    @enderror
                </div>
                <livewire:components.location-selector />
                <div class="grop-columns-2">
                    <div>
                        @error('department')
                            <x-toast.error-toast :message="$message"/>
                        @enderror
                    </div>
                    <div>
                        @error('municipality')
                            <x-toast.error-toast :message="$message"/>
                        @enderror
                    </div>
                </div>
                <div class="grop-columns-2">
                    <div class="container-v">
                        <div class="group-form-v">
                            <label for="district">Corregimiento o Comuna<span class="text-red-500">*</span></label>
                            <input type="text" placeholder="Digite el nombre del Corregimiento o Comuna" id="district" wire:model='district' required>
                        </div>
                        <div>
                            @error('district')
                                <x-toast.error-toast :message="$message"/>
                            @enderror
                        </div>
                    </div>
                    <div class="container-v">
                        <div class="group-form-v">
                            <label for="neighborhood">Nombre Barrio o Vereda<span class="text-red-500">*</span></label>
                            <input type="text" placeholder="Digite el Nombre del Barrio o de la Vereda" id="neighborhood" wire:model='neighborhood' required>
                        </div>
                        <div>
                            @error('neighborhood')
                                <x-toast.error-toast :message="$message"/>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="flex flex-col md:flex-row gap-2">
                    <button type="button" class="btn-extra-primary" wire:click="$dispatch('abrirUbicacion')">
                        <x-icons.geo/>
                        Geo ubicación
                    </button>
                    <input type="text" class="w-full md:!w-auto md:grow" placeholder="Dirección seleccionada con geoubicación" disabled wire:model='address'>
                </div>
                @error('address')
                    <x-toast.error-toast :message="$message"/>
                @enderror
                <div class="flex w-full justify-end">
                    <button type="submit" class='btn-primary'>
                        <span wire:loading.remove>
                            <x-icons.save/>
                        </span>
                        <span wire:loading>
                            <x-icons.loading    wire:loading/>
                        </span>
                        Guardar
                    </button>
                </div>
            </form>
            <form method="POST" action="{{ route('logout') }}" class="w-full" id="logout-form">
                @csrf
            </form>
        </div>
    </div>
    <livewire:components.location-modal />
</div>
