<div class="flex flex-col gap-2 w-full">
    <h1 class='text-center'>Smart<span class='text-primary'>E</span>lect</h1>
    <p class='regular text-center'>Mensaje de Bienvenida: Bienvenido y de que trata la Plataforma.</p>
    <div class="login-register">
        @if ($error_type)
            @switch($error_type)
                {{-- usuario logueado --}}
                @case('user_log')
                    <div class="bg-white rounded-xl shadow-lg border border-grey-200 overflow-hidden">
                        <div class="bg-grey-50 px-6 py-4 border-b border-grey-200">
                            <div class="flex items-center space-x-3">
                                <div>
                                    <h4 class="font-bold text-error">Cuenta Incorrecta</h4>
                                    <p class="text-grey-400 !text-sm">El usuario está logueado con una cuenta distinta a la de la invitación</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="flex items-start space-x-4 mb-5">
                                <p class="text-black !leading-relaxed !text-base"> Estás intentando aceptar una invitación con una cuenta diferente a la que recibió la invitación.
                                </p>
                            </div>
                            <div class="bg-grey-50 border-l-4 border-primary rounded-lg p-4 mb-5">
                                <span class="font-medium text-black"><strong>Invitación para:</strong> {{ $user->email }} </span> <br>
                                <span class="font-medium text-black"><strong>Cuenta actual:</strong> {{ auth()->user()->email }}</span>
                            </div>
                            <p class="text-black !leading-relaxed mb-4 !text-base">
                                No es obligatorio estar logueado para aceptar la invitación. Puedes proceder de dos maneras:
                            </p>
                            <div class="space-y-3 mb-6">
                                <div class="flex items-center space-x-3">
                                    <div class="w-6 h-6 bg-primary rounded-full flex items-center justify-center text-white text-xs font-bold mt-1 flex-shrink-0">1</div>
                                    <p class="text-black !text-sm">Cerrar sesión y aceptar la invitación sin iniciar sesión</p>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <div class="w-6 h-6 bg-primary rounded-full flex items-center justify-center text-white text-xs font-bold mt-1 flex-shrink-0">2</div>
                                    <p class="text-black !text-sm !flex">Iniciar sesión con la cuenta correcta (<span class="font-medium">juan.perez@email.com</span>) para aceptar la invitación</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <button type="submit" class="btn-primary !rounded-lg" form="logout-form">Cerrar Sesión</button>
                                <a href="{{ route('dashboard') }}" class="px-5 py-2.5 bg-grey-200 text-black font-medium rounded-lg hover:bg-grey-300 transition-colors duration-200">
                                    Volver al Inicio
                                </a>
                            </div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="w-full" id="logout-form">
                        @csrf
                    </form>
                    @break
                @case('used')
                    <div class="bg-white rounded-xl shadow-lg border border-grey-200 overflow-hidden">
                        <div class="bg-grey-50 px-6 py-4 border-b border-grey-200">
                            <div class="flex items-center space-x-3">
                                <div>
                                    <h4 class="font-bold text-amber-500">Invitación Ya Utilizada</h4>
                                    <p class="text-grey-400 !text-sm">La invitación ya fue usada</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="flex items-start space-x-4 mb-5">
                                <p class="text-black !leading-relaxed !text-base"> Esta invitación ya ha sido aceptada previamente. </p>
                            </div>
                            <div class="bg-grey-50 border-l-4 border-primary rounded-lg p-4 mb-5">
                                <span class="font-medium text-black"><strong>Invitación aceptada el:</strong>
                                    {{ \Carbon\Carbon::parse($invitation->accepted_at)->translatedFormat('j \d\e F \d\e Y') }}
                                </span><br>
                            </div>
                            <p class="text-black !leading-relaxed mb-4 !text-base">
                                Ya formas parte de esta campaña. Puedes acceder a tu panel de voluntario para ver tus tareas y actividades pendientes.
                            </p>
                            <div class="flex flex-wrap gap-3">
                                <a href="{{ route('dashboard') }}" class="px-5 py-2.5 bg-grey-200 text-black font-medium rounded-lg hover:bg-grey-300 transition-colors duration-200">
                                    Ver Campaña
                                </a>
                            </div>
                        </div>
                    </div>
                    @break
                @case('expired')
                    <div class="bg-white rounded-xl shadow-lg border border-grey-200 overflow-hidden">
                        <div class="bg-grey-50 px-6 py-4 border-b border-grey-200">
                            <div class="flex items-center space-x-3">
                                <div>
                                    <h4 class="font-bold text-amber-500">Invitación Expirada</h4>
                                    <p class="text-grey-400 !text-sm">La invitación ya expiró</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="flex items-start space-x-4 mb-5">
                                <p class="text-black !leading-relaxed !text-base"> Lo sentimos, esta invitación ha expirado y ya no está disponible.</p>
                            </div>
                            <div class="bg-grey-50 border-l-4 border-primary rounded-lg p-4 mb-5">
                                <span class="font-medium text-black"><strong>Fecha de expiración:</strong>  {{ \Carbon\Carbon::parse($invitation->expires_at)->translatedFormat('j \d\e F \d\e Y') }} </span> <br>
                                <span class="font-medium text-black"><strong>Campaña:</strong> {{ $campaign->name }}</span> <br>
                                <span class="font-medium text-black"><strong>Candidato:</strong> {{ $campaign->candidate_name }}</span>
                            </div>
                            <p class="text-black !leading-relaxed mb-4 !text-base">
                                Si deseas unirte a esta campaña, contacta directamente al candidato para solicitar una nueva invitación.
                            </p>
                            <div class="flex flex-wrap gap-3">
                                <a href="{{ route('dashboard') }}" class="px-5 py-2.5 bg-grey-200 text-black font-medium rounded-lg hover:bg-grey-300 transition-colors duration-200">
                                    Volver al Inicio
                                </a>
                            </div>
                        </div>
                    </div>
                    @break
            @endswitch
        @elseif ($acepted)
            <div class="bg-grey-50 px-6 py-4 border-b border-grey-200 rounded-t-lg">
                <div class="flex items-center space-x-3 justify-center">
                    <h3 class="font-bold">¡Invitación Aceptada!</h3>
                </div>
            </div>
            <div class="p-6">
                <div class="flex items-start space-x-4 mb-5">
                    <p class="text-black !leading-relaxed !text-base">Has sido incorporado exitosamente al equipo de campaña.</p>
                </div>
                <div class="bg-grey-50 border-l-4 border-primary rounded-lg p-4 mb-5">
                    <span class="font-medium text-black"><strong>Campaña:</strong>  {{ $campaign->name }} </span> <br>
                    <span class="font-medium text-black"><strong>Candidato:</strong> {{ $campaign->candidate_name }}</span><br>
                    <span class="font-medium text-black"><strong>Fecha de incorporación:</strong> {{ now() }}</span> <br>
                </div>
                <p class="text-black !leading-relaxed mb-4 !text-base">
                    Como nuevo miembro del equipo, puedes:
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-left">
                    <div class="flex items-start space-x-2">
                        <div class="w-5 h-5 bg-valid rounded-full flex items-center justify-center mt-0.5 flex-shrink-0">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <p class="text-black !text-base">Acceder a materiales de campaña exclusivos</p>
                    </div>
                    <div class="flex items-start space-x-2">
                        <div class="w-5 h-5 bg-valid rounded-full flex items-center justify-center mt-0.5 flex-shrink-0">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <p class="text-black !text-base">Coordinar con otros voluntarios</p>
                    </div>
                    <div class="flex items-start space-x-2">
                        <div class="w-5 h-5 bg-valid rounded-full flex items-center justify-center mt-0.5 flex-shrink-0">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <p class="text-black !text-base">Participar en eventos y actividades</p>
                    </div>
                    <div class="flex items-start space-x-2">
                        <div class="w-5 h-5 bg-valid rounded-full flex items-center justify-center mt-0.5 flex-shrink-0">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <p class="text-black !text-base">Seguir el progreso de la campaña</p>
                    </div>
                </div>
                <div class="mt-8">
                    @if (session()->has('status'))
                        <x-toast.success-toast :message="session('status')"/>
                    @endif
                </div>
                <div class="flex flex-wrap gap-3 mt-2">
                    @if (!$user->password)
                        <button type="button" class="btn-secundary !rounded-lg" wire:click='resetPassword' wire:loading.attr="disabled">
                            Crear Contraseña
                        </button>
                    @endif
                </div>
            </div>
        @else
            <div class="form-login space-y-3">
                <div class="container-v">
                    <h3 class="text-center"> Confirmar invitación </h3>
                    <span class="text-center">Has sido invitado a unirte a la campaña:</span>
                    <div class="container-v bg-grey-100 p-4 rounded-lg">
                        <div><strong>Campaña:</strong>  {{$campaign->name}} </div>
                        <div><strong>Candidato:</strong>  {{$campaign->candidate_name}} </div>
                        <div><strong>Cargo:</strong>  {{$campaign->position}} </div>
                        <div><strong>Distrito / Región:</strong>  $campaign->district </div>
                    </div>
                </div>
                <div>
                    <span class="text-justify">¿Deseas unirte oficialmente al equipo de campaña?</span>
                </div>
                <button type="button" class="btn-primary all-w" wire:click='acceptInvitation' wire:loading.attr="disabled">ACEPTAR INVITACIÓN</button>
                <hr>
            </div>
        @endif
        <p class="small text-center mb-4">
            © {{ date('Y') }} SmartElect — Transformando campañas políticas.
        </p>
    </div>
</div>
