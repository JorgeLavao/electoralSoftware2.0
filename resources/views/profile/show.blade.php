<x-layouts.app :title="__('Mi Perfil')">
    <div x-data="{ active: 1 }" class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-6">

        <div class="breadcrumbs text-gray-600">
            Mi Perfil
        </div>

        {{-- 🔹 ACORDEÓN DATOS BÁSICOS --}}
        <div class="rounded-xl bg-white shadow-sm">
            <button @click="active = active === 1 ? null : 1"
                class="w-full flex justify-between items-center p-6 text-left">

                <div>
                    <h3 class="text-xl font-bold text-slate-900">Datos Básicos</h3>
                    <p class="text-sm text-gray-500">Información personal principal del usuario.</p>
                </div>

                <span :class="{'rotate-180': active === 1}" class="transition-transform">
                    ▼
                </span>
            </button>

            <div x-show="active === 1" x-transition class="px-6 pb-6">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border p-4">
                        <p class="text-xs text-gray-400">Nombre Completo</p>
                        <p class="mt-2 font-semibold">{{ $user->full_name ?: '-' }}</p>
                    </div>

                    <div class="rounded-2xl border p-4">
                        <p class="text-xs text-gray-400">Tipo de documento</p>
                        <p class="mt-2 font-semibold">{{ $user->foreign_document_type?->name ?? '-' }}</p>
                    </div>

                    <div class="rounded-2xl border p-4">
                        <p class="text-xs text-gray-400">Número de documento</p>
                        <p class="mt-2 font-semibold">{{ $user->document_number ?: '-' }}</p>
                    </div>

                    <div class="rounded-2xl border p-4">
                        <p class="text-xs text-gray-400">Teléfono</p>
                        <p class="mt-2 font-semibold">{{ $user->celphone ?: '-' }}</p>
                    </div>

                    <div class="rounded-2xl border p-4">
                        <p class="text-xs text-gray-400">Correo</p>
                        <p class="mt-2 font-semibold break-all">{{ $user->email ?: '-' }}</p>
                    </div>

                    <div class="rounded-2xl border p-4">
                        <p class="text-xs text-gray-400">Estado del correo</p>
                        <p class="mt-2 font-semibold">
                            {{ $user->email_verified_at ? 'Verificado' : 'Pendiente' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- 🔹 ACORDEÓN INFORMACIÓN COMPLEMENTARIA --}}
        <div class="rounded-xl bg-white shadow-sm">
            <button @click="active = active === 2 ? null : 2"
                class="w-full flex justify-between items-center p-6 text-left">

                <div>
                    <h3 class="text-xl font-bold text-slate-900">Información Complementaria</h3>
                    <p class="text-sm text-gray-500">Datos demográficos y de residencia.</p>
                </div>

                <span :class="{'rotate-180': active === 2}" class="transition-transform">
                    ▼
                </span>
            </button>

            <div x-show="active === 2" x-transition class="px-6 pb-6">
                @if ($profile)
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border p-4">
                        <p class="text-xs text-gray-400">Género</p>
                        <p class="mt-2 font-semibold">{{ $profile->foreign_gender?->name ?? '-' }}</p>
                    </div>

                    <div class="rounded-2xl border p-4">
                        <p class="text-xs text-gray-400">Rango de edad</p>
                        <p class="mt-2 font-semibold">{{ $profile->foreign_range_age?->range ?? '-' }}</p>
                    </div>

                    <div class="rounded-2xl border p-4">
                        <p class="text-xs text-gray-400">Ocupación</p>
                        <p class="mt-2 font-semibold">{{ $profile->foreign_occupations?->name ?? '-' }}</p>
                    </div>

                    <div class="rounded-2xl border p-4">
                        <p class="text-xs text-gray-400">Vehículo</p>
                        <p class="mt-2 font-semibold">{{ $profile->vehicle ? 'Sí' : 'No' }}</p>
                    </div>

                    <div class="rounded-2xl border p-4">
                        <p class="text-xs text-gray-400">Zona</p>
                        <p class="mt-2 font-semibold">{{ ucfirst($profile->zone ?? '-') }}</p>
                    </div>

                    <div class="rounded-2xl border p-4">
                        <p class="text-xs text-gray-400">Ubicación actual</p>
                        <p class="mt-2 font-semibold">{{ $profile->current_location ?: '-' }}</p>
                    </div>
                </div>
                @else
                <div class="p-4 border border-amber-300 bg-amber-50 rounded-xl">
                    Aún no has completado tu información.
                    <div class="mt-3">
                        <a href="{{ route('profile.complete-register') }}" class="button btn-primary">
                            Completar información
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- 🔹 ACORDEÓN UBICACIÓN --}}
        <div class="rounded-xl bg-white shadow-sm">
            <button @click="active = active === 3 ? null : 3"
                class="w-full flex justify-between items-center p-6 text-left">

                <div>
                    <h3 class="text-xl font-bold text-slate-900">Ubicación</h3>
                    <p class="text-sm text-gray-500">Detalle de residencia.</p>
                </div>

                <span :class="{'rotate-180': active === 3}" class="transition-transform">
                    ▼
                </span>
            </button>

            <div x-show="active === 3" x-transition class="px-6 pb-6">
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-2xl border p-4">
                        <p class="text-xs text-gray-400">Departamento</p>
                        <p class="mt-2 font-semibold">{{ $department['name'] ?? '-' }}</p>
                    </div>

                    <div class="rounded-2xl border p-4">
                        <p class="text-xs text-gray-400">Municipio</p>
                        <p class="mt-2 font-semibold">{{ $municipality['name'] ?? '-' }}</p>
                    </div>

                    <div class="rounded-2xl border p-4">
                        <p class="text-xs text-gray-400">Comuna</p>
                        <p class="mt-2 font-semibold">{{ $profile?->district_commune ?: '-' }}</p>
                    </div>

                    <div class="rounded-2xl border p-4">
                        <p class="text-xs text-gray-400">Barrio</p>
                        <p class="mt-2 font-semibold">{{ $profile?->neighborhood_village_name ?: '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-layouts.app>