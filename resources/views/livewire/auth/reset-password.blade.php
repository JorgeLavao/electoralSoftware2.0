<div class="flex flex-col gap-2 w-full">
    <h1 class='text-center'>Smart<span class='text-primary'>E</span>lect</h1>
    <div class="login-login">
        <form method="POST" wire:submit="resetPassword" class="form-login space-y-2">
            <h3>Cambiar Contraseña</h3>
            <div class="group-form">
                <label for="email">Correo: </label>
                <input type="text" wire:model='email' id="email" disabled required>
            </div>
            <div class="group-form">
                <label for="password">Nueva Contraseña</label>
                <x-password-input id="password" placeholder="Digite la nueva Contraseña" wire:model.defer="password" autoComplete="new-password" required/>
            </div>
            <div class="group-form">
                <label for="con-pass">Confirme Contraseña</label>
                <x-password-input id="con-pass" placeholder="Repita la Contraseña" wire:model="password_confirmation" autocomplete="new-password" required/>
            </div>
            @error('password')
                <div>
                    <x-toast.error-toast :message="$message"/>
                </div>
            @enderror
            @error('email')
                <div>
                    <x-toast.error-toast :message="$message"/>
                </div>
            @enderror
            
            <div class="mt-4">
                <button type="submit" class="btn-primary all-w">Establecer Contraseña</button>
            </div>
        </form>
    </div>
</div>
