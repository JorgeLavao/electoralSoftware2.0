<div x-data="{ active: 2 }" class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-6">
    <div class="breadcrumbs text-gray-600">
        Mi Perfil
    </div>

    <div class="rounded-xl bg-white shadow-sm">
        <button type="button" @click="active = active === 1 ? null : 1"
            class="w-full flex justify-between items-center p-6 text-left">
            <div>
                <h3 class="text-xl font-bold text-slate-900">Foto de Perfil</h3>
                <p class="text-sm text-gray-500">Imagen que se muestra en tu cuenta y en el menu principal.</p>
            </div>
            <span :class="{'rotate-180': active === 1}" class="transition-transform">▼</span>
        </button>

        <div x-show="active === 1" x-transition class="px-6 pb-6">
            <div class="grid gap-4 md:grid-cols-[auto,1fr] md:items-center">
                <div class="rounded-2xl border p-4">
                    <div class="flex items-center gap-4">
                        <img
                            class="h-20 w-20 rounded-full border border-gray-200 object-cover"
                            src="{{ $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : ($user->google_avatar ?: 'https://ui-avatars.com/api/?background=C4C4C4&name=' . urlencode($user->full_name ?: $user->first_name) . '&bold=true') }}"
                            alt="Foto de perfil de {{ $user->full_name ?: $user->first_name }}">
                        <div>
                            <p class="text-xs text-gray-400">Usuario</p>
                            <p class="mt-2 font-semibold">{{ $user->full_name ?: 'Usuario' }}</p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('profile.photo.update') }}" enctype="multipart/form-data" class="rounded-2xl border p-4">
                    @csrf
                    @method('PATCH')

                    <label for="profile_photo" class="text-xs text-gray-400">Seleccionar foto</label>
                    <div class="mt-2 flex flex-col gap-3 lg:flex-row lg:items-center">
                        <input
                            id="profile_photo"
                            name="profile_photo"
                            type="file"
                            accept="image/png,image/jpeg,image/webp"
                            required
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 file:mr-4 file:rounded-md file:border-0 file:bg-primary file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white">

                        <button type="submit" class="button btn-primary shrink-0">Guardar foto</button>
                    </div>

                    @error('profile_photo')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @if (session('profile_photo_status'))
                    <p class="mt-2 text-sm font-medium text-green-600">{{ session('profile_photo_status') }}</p>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <div class="rounded-xl bg-white shadow-sm">
        <button type="button" @click="active = active === 2 ? null : 2"
            class="w-full flex justify-between items-center p-6 text-left">
            <div>
                <h3 class="text-xl font-bold text-slate-900">Datos Basicos</h3>
                <p class="text-sm text-gray-500">Informacion personal principal del usuario.</p>
            </div>
            <span :class="{'rotate-180': active === 2}" class="transition-transform">▼</span>
        </button>

        <div x-show="active === 2" x-transition class="px-6 pb-6">
            <form wire:submit="updateBasicInformation" class="space-y-4">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="container-v">
                        <div class="group-form-v">
                            <label for="doc_type">Tipo de documento<span class="text-red-500">*</span></label>
                            <select id="doc_type" wire:model="doc_type" required>
                                <option value="">Seleccione</option>
                                @foreach ($documents_type as $document)
                                    <option value="{{ $document->id }}">{{ $document->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('doc_type') <x-toast.error-toast :message="$message" /> @enderror
                    </div>

                    <div class="container-v">
                        <div class="group-form-v">
                            <label for="doc_number">Numero de documento<span class="text-red-500">*</span></label>
                            <input id="doc_number" type="text" wire:model="doc_number" required>
                        </div>
                        @error('doc_number') <x-toast.error-toast :message="$message" /> @enderror
                    </div>

                    <div class="container-v">
                        <div class="group-form-v">
                            <label for="first_name">Primer nombre<span class="text-red-500">*</span></label>
                            <input id="first_name" type="text" wire:model="first_name" required>
                        </div>
                        @error('first_name') <x-toast.error-toast :message="$message" /> @enderror
                    </div>

                    <div class="container-v">
                        <div class="group-form-v">
                            <label for="middle_name">Segundo nombre</label>
                            <input id="middle_name" type="text" wire:model="middle_name">
                        </div>
                        @error('middle_name') <x-toast.error-toast :message="$message" /> @enderror
                    </div>

                    <div class="container-v">
                        <div class="group-form-v">
                            <label for="paternal_surname">Primer apellido<span class="text-red-500">*</span></label>
                            <input id="paternal_surname" type="text" wire:model="paternal_surname" required>
                        </div>
                        @error('paternal_surname') <x-toast.error-toast :message="$message" /> @enderror
                    </div>

                    <div class="container-v">
                        <div class="group-form-v">
                            <label for="maternal_surname">Segundo apellido</label>
                            <input id="maternal_surname" type="text" wire:model="maternal_surname">
                        </div>
                        @error('maternal_surname') <x-toast.error-toast :message="$message" /> @enderror
                    </div>

                    <div class="container-v">
                        <div class="group-form-v">
                            <label for="celphone">Telefono<span class="text-red-500">*</span></label>
                            <input id="celphone" type="text" wire:model="celphone" required>
                        </div>
                        @error('celphone') <x-toast.error-toast :message="$message" /> @enderror
                    </div>

                    <div class="container-v">
                        <div class="group-form-v">
                            <label for="email">Correo<span class="text-red-500">*</span></label>
                            <input id="email" type="email" wire:model="email" required>
                        </div>
                        @error('email') <x-toast.error-toast :message="$message" /> @enderror
                    </div>
                </div>

                @if (session('basic_status'))
                    <p class="text-sm font-medium text-green-600">{{ session('basic_status') }}</p>
                @endif

                <div class="flex justify-end">
                    <button type="submit" class="btn-primary">
                        <span wire:loading.remove wire:target="updateBasicInformation"><x-icons.save /></span>
                        <span wire:loading wire:target="updateBasicInformation"><x-icons.loading /></span>
                        Guardar datos basicos
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="rounded-xl bg-white shadow-sm">
        <button type="button" @click="active = active === 3 ? null : 3"
            class="w-full flex justify-between items-center p-6 text-left">
            <div>
                <h3 class="text-xl font-bold text-slate-900">Informacion Complementaria</h3>
                <p class="text-sm text-gray-500">Datos demograficos y condiciones generales.</p>
            </div>
            <span :class="{'rotate-180': active === 3}" class="transition-transform">▼</span>
        </button>

        <div x-show="active === 3" x-transition class="px-6 pb-6">
            @if ($profile)
                <form wire:submit="updateComplementaryInformation" class="space-y-4">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="container-v">
                            <div class="group-form-v">
                                <label for="gender">Genero<span class="text-red-500">*</span></label>
                                <select id="gender" wire:model="gender" required>
                                    <option value="">Seleccione</option>
                                    @foreach ($genders as $genderOption)
                                        <option value="{{ $genderOption->id }}">{{ $genderOption->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('gender') <x-toast.error-toast :message="$message" /> @enderror
                        </div>

                        <div class="container-v">
                            <div class="group-form-v">
                                <label for="age_id">Rango de edad<span class="text-red-500">*</span></label>
                                <select id="age_id" wire:model="age_id" required>
                                    <option value="">Seleccione</option>
                                    @foreach ($age_ranges as $age)
                                        <option value="{{ $age->id }}">{{ $age->range }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('age_id') <x-toast.error-toast :message="$message" /> @enderror
                        </div>

                        <div class="container-v">
                            <div class="group-form-v">
                                <label for="birth_day">Dia de nacimiento<span class="text-red-500">*</span></label>
                                <select id="birth_day" wire:model="birth_day" required>
                                    <option value="">Seleccione</option>
                                    @for ($day = 1; $day <= 31; $day++)
                                        <option value="{{ $day }}">{{ str_pad($day, 2, '0', STR_PAD_LEFT) }}</option>
                                    @endfor
                                </select>
                            </div>
                            @error('birth_day') <x-toast.error-toast :message="$message" /> @enderror
                        </div>

                        <div class="container-v">
                            <div class="group-form-v">
                                <label for="birth_month">Mes de nacimiento<span class="text-red-500">*</span></label>
                                <select id="birth_month" wire:model="birth_month" required>
                                    <option value="">Seleccione</option>
                                    <option value="1">Enero</option>
                                    <option value="2">Febrero</option>
                                    <option value="3">Marzo</option>
                                    <option value="4">Abril</option>
                                    <option value="5">Mayo</option>
                                    <option value="6">Junio</option>
                                    <option value="7">Julio</option>
                                    <option value="8">Agosto</option>
                                    <option value="9">Septiembre</option>
                                    <option value="10">Octubre</option>
                                    <option value="11">Noviembre</option>
                                    <option value="12">Diciembre</option>
                                </select>
                            </div>
                            @error('birth_month') <x-toast.error-toast :message="$message" /> @enderror
                        </div>

                        <div class="container-v">
                            <div class="group-form-v">
                                <label for="occupation">Ocupacion<span class="text-red-500">*</span></label>
                                <select id="occupation" wire:model="occupation" required>
                                    <option value="">Seleccione</option>
                                    @foreach ($occupations as $occupationOption)
                                        <option value="{{ $occupationOption->id }}">{{ $occupationOption->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('occupation') <x-toast.error-toast :message="$message" /> @enderror
                        </div>

                        <div class="container-v">
                            <div class="group-form-v">
                                <label for="vehicle">Posee vehiculo<span class="text-red-500">*</span></label>
                                <select id="vehicle" wire:model="vehicle" required>
                                    <option value="">Seleccione</option>
                                    <option value="1">Si</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                            @error('vehicle') <x-toast.error-toast :message="$message" /> @enderror
                        </div>
                    </div>

                    @if (session('complementary_status'))
                        <p class="text-sm font-medium text-green-600">{{ session('complementary_status') }}</p>
                    @endif
                    @if (session('complementary_error'))
                        <p class="text-sm font-medium text-red-600">{{ session('complementary_error') }}</p>
                    @endif

                    <div class="flex justify-end">
                        <button type="submit" class="btn-primary">
                            <span wire:loading.remove wire:target="updateComplementaryInformation"><x-icons.save /></span>
                            <span wire:loading wire:target="updateComplementaryInformation"><x-icons.loading /></span>
                            Guardar informacion complementaria
                        </button>
                    </div>
                </form>
            @else
                <div class="p-4 border border-amber-300 bg-amber-50 rounded-xl">
                    Aun no has completado tu informacion.
                    <div class="mt-3">
                        <a href="{{ route('profile.complete-register') }}" class="button btn-primary">
                            Completar informacion
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="rounded-xl bg-white shadow-sm">
        <button type="button" @click="active = active === 4 ? null : 4"
            class="w-full flex justify-between items-center p-6 text-left">
            <div>
                <h3 class="text-xl font-bold text-slate-900">Ubicacion</h3>
                <p class="text-sm text-gray-500">Detalle de residencia y geoubicacion.</p>
            </div>
            <span :class="{'rotate-180': active === 4}" class="transition-transform">▼</span>
        </button>

        <div x-show="active === 4" x-transition class="px-6 pb-6">
            @if ($profile)
                <form wire:submit="updateLocationInformation" class="space-y-4">
                    <div class="space-x-4 space-y-2">
                        <label class="cursor-pointer">
                            <input type="radio" value="rural" wire:model="zone"><span class="pl-1">Rural</span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" value="urbana" wire:model="zone"><span class="pl-1">Urbana</span>
                        </label>
                        @error('zone')
                            <div class="mt-2"><x-toast.error-toast :message="$message" /></div>
                        @enderror
                    </div>

                    <livewire:components.location-selector
                        :initial-department-id="$department['id'] ?? null"
                        :initial-municipality-id="$municipality['id'] ?? null"
                        :initial-department-name="$department['name'] ?? null"
                        :initial-municipality-name="$municipality['name'] ?? null"
                        :key="'profile-location-' . ($department['id'] ?? 'none') . '-' . ($municipality['id'] ?? 'none')" />

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            @error('department') <x-toast.error-toast :message="$message" /> @enderror
                        </div>
                        <div>
                            @error('municipality') <x-toast.error-toast :message="$message" /> @enderror
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="container-v">
                            <div class="group-form-v">
                                <label for="neighborhood">Nombre Barrio o Vereda<span class="text-red-500">*</span></label>
                                <input id="neighborhood" type="text" wire:model="neighborhood" required>
                            </div>
                            @error('neighborhood') <x-toast.error-toast :message="$message" /> @enderror
                        </div>
                        <div class="container-v">
                            <div class="group-form-v">
                                <label for="district">Corregimiento o Comuna</label>
                                <input id="district" type="text" wire:model="district">
                            </div>
                            @error('district') <x-toast.error-toast :message="$message" /> @enderror
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row gap-2">
                        <button type="button" class="btn-extra-primary" wire:click="$dispatch('abrirUbicacion')">
                            <x-icons.geo />
                            Geo ubicacion
                        </button>
                        <input type="text" class="w-full md:!w-auto md:grow" placeholder="Direccion seleccionada con geoubicacion" disabled wire:model="address">
                    </div>
                    @error('address') <x-toast.error-toast :message="$message" /> @enderror
                    @error('lat') <x-toast.error-toast :message="$message" /> @enderror
                    @error('lng') <x-toast.error-toast :message="$message" /> @enderror

                    @if (session('location_status'))
                        <p class="text-sm font-medium text-green-600">{{ session('location_status') }}</p>
                    @endif
                    @if (session('location_error'))
                        <p class="text-sm font-medium text-red-600">{{ session('location_error') }}</p>
                    @endif

                    <div class="flex justify-end">
                        <button type="submit" class="btn-primary">
                            <span wire:loading.remove wire:target="updateLocationInformation"><x-icons.save /></span>
                            <span wire:loading wire:target="updateLocationInformation"><x-icons.loading /></span>
                            Guardar ubicacion
                        </button>
                    </div>
                </form>
            @else
                <div class="p-4 border border-amber-300 bg-amber-50 rounded-xl">
                    Aun no has completado tu informacion.
                    <div class="mt-3">
                        <a href="{{ route('profile.complete-register') }}" class="button btn-primary">
                            Completar informacion
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <livewire:components.location-modal />
</div>
