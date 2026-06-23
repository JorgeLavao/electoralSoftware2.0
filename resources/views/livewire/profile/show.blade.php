<section class="dashboard__main__section">
    <div class="breadcrumbs">
        <a href="">Mi Perfil</a>
    </div>

    <div class="container-v mb-4">
        @if (session('profile_photo_status'))
            <x-toast.success-toast :message="session('profile_photo_status')" />
        @endif

        @if (session('basic_status'))
            <x-toast.success-toast :message="session('basic_status')" />
        @endif

        @if (session('complementary_status'))
            <x-toast.success-toast :message="session('complementary_status')" />
        @endif

        @if (session('location_status'))
            <x-toast.success-toast :message="session('location_status')" />
        @endif
    </div>

    <div class="grop-columns-2 grop-columns-lg">
        <div class="container-v">
            <div class="flex items-center gap-4 rounded-lg border border-gray-300 bg-gray-50 p-4">
                <img
                    class="h-20 w-20 rounded-full border border-gray-200 object-cover"
                    src="{{ $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : ($user->google_avatar ?: 'https://ui-avatars.com/api/?background=C4C4C4&name=' . urlencode($user->full_name ?: $user->first_name) . '&bold=true') }}"
                    alt="Foto de perfil de {{ $user->full_name ?: $user->first_name }}">

                <div class="min-w-0">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Usuario</p>
                    <p class="truncate text-lg font-semibold text-gray-800">{{ $user->full_name ?: 'Usuario' }}</p>
                    <p class="break-all text-sm text-gray-600">{{ $user->email ?: '-' }}</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.photo.update') }}" enctype="multipart/form-data" class="container-v">
            @csrf
            @method('PATCH')

            <div class="group-form-v">
                <label for="profile_photo">Foto de perfil</label>
                <input
                    id="profile_photo"
                    name="profile_photo"
                    type="file"
                    accept="image/png,image/jpeg,image/webp"
                    required
                    class="!py-3">
            </div>

            @error('profile_photo')
                <x-toast.error-toast :message="$message" />
            @enderror

            <div class="mt-3 flex justify-end">
                <button type="submit" class="btn-primary w-full !inline-flex !items-center !justify-center !gap-2 !px-5 !py-3 text-center sm:w-auto">
                    <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center">
                        <x-icons.upload-line />
                    </span>
                    <span>Guardar foto</span>
                </button>
            </div>
        </form>
    </div>

    <hr class="my-4">

    <form wire:submit="updateBasicInformation" class="space-y-5">
        <h3 class="text-lg font-semibold">Datos basicos</h3>

        <div class="grop-columns-2">
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
        </div>

        <div class="grop-columns-2">
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
        </div>

        <div class="grop-columns-2">
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
        </div>

        <div class="grop-columns-2">
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

        <div class="flex justify-end">
            <button type="submit" class="btn-primary w-full !inline-flex !items-center !justify-center !gap-2 !px-5 !py-3 text-center md:w-auto">
                <span wire:loading.remove wire:target="updateBasicInformation" class="inline-flex h-5 w-5 shrink-0 items-center justify-center"><x-icons.save /></span>
                <span wire:loading wire:target="updateBasicInformation" class="inline-flex h-5 w-5 shrink-0 items-center justify-center"><x-icons.loading /></span>
                <span>Guardar datos basicos</span>
            </button>
        </div>
    </form>

    <hr class="my-4">

    @if ($profile)
        <form wire:submit="updateComplementaryInformation" class="space-y-5">
            <h3 class="text-lg font-semibold">Informacion complementaria</h3>

            <div class="grop-columns-2">
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
            </div>

            <div class="grop-columns-2">
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
            </div>

            <div class="grop-columns-2">
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

            @if (session('complementary_error'))
                <x-toast.error-toast :message="session('complementary_error')" />
            @endif

            <div class="flex justify-end">
                <button type="submit" class="btn-primary w-full !inline-flex !items-center !justify-center !gap-2 !px-5 !py-3 text-center md:w-auto">
                    <span wire:loading.remove wire:target="updateComplementaryInformation" class="inline-flex h-5 w-5 shrink-0 items-center justify-center"><x-icons.save /></span>
                    <span wire:loading wire:target="updateComplementaryInformation" class="inline-flex h-5 w-5 shrink-0 items-center justify-center"><x-icons.loading /></span>
                    <span>Guardar informacion complementaria</span>
                </button>
            </div>
        </form>

        <hr class="my-4">

        <form wire:submit="updateLocationInformation" class="space-y-5">
            <h3 class="text-lg font-semibold">Ubicacion</h3>

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

            <div class="grop-columns-2">
                <div>
                    @error('department') <x-toast.error-toast :message="$message" /> @enderror
                </div>
                <div>
                    @error('municipality') <x-toast.error-toast :message="$message" /> @enderror
                </div>
            </div>

            <div class="grop-columns-2">
                <div class="container-v">
                    <div class="group-form-v">
                        <label for="neighborhood">Nombre barrio o vereda<span class="text-red-500">*</span></label>
                        <input id="neighborhood" type="text" wire:model="neighborhood" required>
                    </div>
                    @error('neighborhood') <x-toast.error-toast :message="$message" /> @enderror
                </div>

                <div class="container-v">
                    <div class="group-form-v">
                        <label for="district">Corregimiento o comuna</label>
                        <input id="district" type="text" wire:model="district">
                    </div>
                    @error('district') <x-toast.error-toast :message="$message" /> @enderror
                </div>
            </div>

            <div class="container-v">
                <div class="group-form-v">
                    <label for="profile_address">Direccion seleccionada</label>
                    <div class="group-form-h gap-y-4">
                        <input id="profile_address" type="text" class="!py-3" placeholder="Direccion seleccionada con geoubicacion" disabled wire:model="address">

                        <button type="button" class="btn-extra-primary w-full !inline-flex !items-center !justify-center !gap-2 !px-5 !py-3 text-center sm:w-auto" wire:click="$dispatch('abrirUbicacion')">
                            <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center">
                                <x-icons.geo />
                            </span>
                            <span>Geo ubicacion</span>
                        </button>
                    </div>
                </div>
                @error('address') <x-toast.error-toast :message="$message" /> @enderror
                @error('lat') <x-toast.error-toast :message="$message" /> @enderror
                @error('lng') <x-toast.error-toast :message="$message" /> @enderror
            </div>

            @if (session('location_error'))
                <x-toast.error-toast :message="session('location_error')" />
            @endif

            <div class="flex justify-end">
                <button type="submit" class="btn-primary w-full !inline-flex !items-center !justify-center !gap-2 !px-5 !py-3 text-center md:w-auto">
                    <span wire:loading.remove wire:target="updateLocationInformation" class="inline-flex h-5 w-5 shrink-0 items-center justify-center"><x-icons.save /></span>
                    <span wire:loading wire:target="updateLocationInformation" class="inline-flex h-5 w-5 shrink-0 items-center justify-center"><x-icons.loading /></span>
                    <span>Guardar ubicacion</span>
                </button>
            </div>
        </form>
    @else
        <div class="mt-4 rounded-lg border border-amber-300 bg-amber-50 p-4">
            <p class="font-medium text-amber-900">Aun no has completado tu informacion adicional.</p>
            <p class="mt-1 text-sm text-amber-800">Completa el registro para activar los campos de informacion complementaria y ubicacion.</p>

            <div class="mt-3">
                <a href="{{ route('profile.complete-register') }}" class="button btn-primary w-full !inline-flex !items-center !justify-center !gap-2 !px-5 !py-3 text-center sm:w-auto">
                    <span>Completar informacion</span>
                </a>
            </div>
        </div>
    @endif

    <livewire:components.location-modal />
</section>
