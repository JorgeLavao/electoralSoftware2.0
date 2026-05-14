<div class="flex flex-col gap-2 w-full">
    <h1 class='text-center'>Smart<span class='text-primary'>E</span>lect</h1>
    <p class='regular text-center'>Mensaje de Bienvenida: Bienvenido y de que trata la Plataforma.</p>

    <div class="login-register">
        <div class="bg-white rounded-xl shadow-lg border border-grey-200 overflow-hidden">
            
            {{-- BLOQUE 1: Manejo de Errores mediante Switch --}}
            @if ($error_type)
                @switch($error_type)
                    
                    {{-- Caso: El usuario ya está logueado pero con un correo diferente al de la invitación --}}
                    @case('user_log')
                        <div class="bg-grey-50 px-6 py-4 border-b border-grey-200">
                            <div class="flex items-center space-x-3">
                                <div>
                                    <h4 class="font-bold text-error">Cuenta Incorrecta</h4>
                                    <p class="text-grey-400 !text-sm">El usuario está logueado con una cuenta distinta</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="bg-grey-50 border-l-4 border-primary rounded-lg p-4 mb-5">
                                <span class="font-medium text-black"><strong>Invitación para:</strong> {{ $user->email }} </span> <br>
                                <span class="font-medium text-black"><strong>Cuenta actual:</strong> {{ auth()->user()->email }}</span>
                            </div>
                            <div class="space-y-3 mb-6">
                                <div class="flex items-center space-x-3">
                                    <div class="w-6 h-6 bg-primary rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0">1</div>
                                    <p class="text-black !text-sm">Cerrar sesión y aceptar como invitado</p>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <div class="w-6 h-6 bg-primary rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0">2</div>
                                    <p class="text-black !text-sm">Iniciar sesión con la cuenta correcta</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                {{-- Este botón dispara el formulario de logout de abajo --}}
                                <button type="submit" class="btn-primary !rounded-lg" form="logout-form">Cerrar Sesión</button>
                                <a href="{{ route('dashboard') }}" class="px-5 py-2.5 bg-grey-200 text-black font-medium rounded-lg">Volver</a>
                            </div>
                        </div>
                        {{-- Formulario oculto para cerrar sesión --}}
                        <form method="POST" action="{{ route('logout') }}" class="w-full" id="logout-form">
                            @csrf
                        </form>
                    @break

                    {{-- Caso: La invitación ya fue procesada anteriormente --}}
                    @case('used')
                        <div class="bg-grey-50 px-6 py-4 border-b border-grey-200">
                            <h4 class="font-bold text-amber-500">Invitación Ya Utilizada</h4>
                        </div>
                        <div class="p-6">
                            <div class="bg-grey-50 border-l-4 border-primary rounded-lg p-4 mb-5">
                                <span class="font-medium text-black"><strong>Aceptada el:</strong>
                                    {{ \Carbon\Carbon::parse($invitation->accepted_at)->translatedFormat('j \d\e F \d\e Y') }}
                                </span>
                            </div>
                            <a href="{{ route('dashboard') }}" class="px-5 py-2.5 bg-grey-200 text-black font-medium rounded-lg">Ir al Panel</a>
                        </div>
                    @break

                    {{-- Caso: Se superó la fecha límite de la invitación --}}
                    @case('expired')
                        <div class="bg-grey-50 px-6 py-4 border-b border-grey-200">
                            <h4 class="font-bold text-amber-500">Invitación Expirada</h4>
                        </div>
                        <div class="p-6">
                            <p class="mb-4">Esta invitación ha expirado el {{ \Carbon\Carbon::parse($invitation->expires_at)->format('d/m/Y') }}.</p>
                            <a href="{{ route('dashboard') }}" class="btn-primary">Volver al Inicio</a>
                        </div>
                    @break
                @endswitch

            {{-- BLOQUE 2: Invitación Aceptada con Éxito --}}
            @elseif ($acepted)
                <div class="bg-grey-50 px-6 py-4 border-b border-grey-200 rounded-t-lg">
                    <h3 class="font-bold text-center">¡Invitación Aceptada!</h3>
                </div>
                <div class="p-6 text-center">
                    <p class="mb-5">Has sido incorporado exitosamente al equipo de campaña.</p>
                    <div class="bg-grey-50 border-l-4 border-primary rounded-lg p-4 mb-5 text-left">
                        <strong>Campaña:</strong> {{ $campaign->name }}<br>
                        <strong>Candidato:</strong> {{ $campaign->candidate_name }}
                    </div>
                    
                    {{-- Si el usuario es nuevo y no tiene contraseña, se le invita a crear una --}}
                    @if (!$user->password)
                        <button type="button" class="btn-secundary w-full" wire:click='resetPassword' wire:loading.attr="disabled">
                            Establecer mi Contraseña
                        </button>
                    @endif
                </div>

            {{-- BLOQUE 3: Estado Inicial - Confirmación de Invitación --}}
            @else
                <div class="bg-grey-50 px-6 py-4 border-b border-grey-200 rounded-t-lg">
                    <h3 class="font-bold text-center">Confirmar invitación</h3>
                </div>
                <div class="p-6 space-y-4">
                    <p class="text-center">Has sido invitado a:</p>
                    <div class="container-v bg-grey-100 p-4 rounded-lg text-sm">
                        <div><strong>Campaña:</strong> {{$campaign->name}} </div>
                        <div><strong>Candidato:</strong> {{$campaign->candidate_name}} </div>
                        <div><strong>Cargo:</strong> {{$campaign->position}} </div>
                    </div>
                    {{-- Acción principal para aceptar --}}
                    <button type="button" class="btn-primary all-w" 
                        wire:click='acceptInvitation' 
                        wire:loading.attr="disabled">
                        ACEPTAR INVITACIÓN
                    </button>
                </div>
            @endif

            <p class="small text-center mb-4">
                © {{ date('Y') }} SmartElect — Transformando campañas políticas.
            </p>
        </div>
    </div>
</div>