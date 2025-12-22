<div class="flex flex-col gap-2 w-full">
    <h1 class='text-center'>Smart<span class='text-primary'>E</span>lect</h1>
    <div class="login-login">
        <form method="POST" wire:submit="sendPasswordResetLink" class="form-login space-y-2">
            <h3>Restablecer Contraseña</h3>
            <span class="!text-md">Escriba su correo a continuación. Le enviaremos un enlace seguro para que recupere el acceso a su cuenta.</span>
            <div class="group-form mt-4">
                <label for="email">Correo: </label>
                <input type="email" wire:model='email' id="email" required autofocus placeholder="email@example.com">
            </div>
            @error('email')
                <div>
                    <x-toast.error-toast :message="$message"/>
                </div>
            @enderror
            @if (session('status'))
                <div class="mt-2">
                    <x-toast.success-toast :message="session('status')"/>
                </div>
            @endif
            <div class="mt-4">
                <button type="submit" class="btn-primary all-w">Enviar enlace de recuperación</button>
            </div>
            <div class="mt-2 flex justify-center">
                <a href="{{ route('login') }}" class="underline"> Iniciar Sesión </a>
            </div>
        </form>
    </div>
</div>
