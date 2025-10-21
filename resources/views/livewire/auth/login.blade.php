<div class="flex flex-col gap-6">
    <h1 class='text-center'>Smart<span class='text-primary'>E</span>lect</h1>
    <div class='login-login'>
        <div class='form-login'>
            <h3>Acceso</h3>
            <form method="POST" wire:submit="login" class="space-y-2">
                <div class='group-form'>
                    <label for="username">Usuario</label>
                    <input type="email" id='username' wire:model='email'
                        required autoFocus autoComplete='email' placeholder='Digite su Usuario' />
                </div>
                <div class='group-form'>
                    <label for="pass">Clave</label>
                    <x-password-input id="pass" placeholder="Digite su Clave" wire:model.defer="password" autoComplete="current-password" required/>
                </div>
                {{-- btn --}}
                <div class='mt-4'>
                    <button type="submit" class='all-w btn-primary' data-test="login-button">
                        Iniciar Sesión
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                            <g fill="none" fill-rule="evenodd">
                                <path d="M24 0v24H0V0zM12.593 23.258l-.011.002l-.071.035l-.02.004l-.014-.004l-.071-.035q-.016-.005-.024.005l-.004.01l-.017.428l.005.02l.01.013l.104.074l.015.004l.012-.004l.104-.074l.012-.016l.004-.017l-.017-.427q-.004-.016-.017-.018m.265-.113l-.013.002l-.185.093l-.01.01l-.003.011l.018.43l.005.012l.008.007l.201.093q.019.005.029-.008l.004-.014l-.034-.614q-.005-.019-.02-.022m-.715.002a.02.02 0 0 0-.027.006l-.006.014l-.034.614q.001.018.017.024l.015-.002l.201-.093l.01-.008l.004-.011l.017-.43l-.003-.012l-.01-.01z" />
                                <path fill="currentColor" d="M16.06 10.94a1.5 1.5 0 0 1 0 2.12l-5.656 5.658a1.5 1.5 0 1 1-2.121-2.122L12.879 12L8.283 7.404a1.5 1.5 0 0 1 2.12-2.122l5.658 5.657Z" />
                            </g>
                        </svg>
                    </button>
                </div>
                @error('email')
                    <x-toast.error-toast :message="$message"/>
                @enderror
            </form>
            @if (Route::has('password.request'))
                <div class='flex w-full justify-center mt-2'>
                    <a href="{{route('password.request')}}" wire:navigate class='darkened base-bold text-grey-400'>Recuperar Contraseña</a>
                </div>
            @endif
            <div class="mt-2">
                @if (session('status'))
                    <x-toast.success-toast :message="session('status')"/>
                @endif
            </div>
        </div>
        <div class='register-area'>
            <a href="" class='button btn-tertiary'>
                Registrarse con
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 16 16">
                    <g fill="none" fill-rule="evenodd" clip-rule="evenodd">
                        <path fill="#f44336" d="M7.209 1.061c.725-.081 1.154-.081 1.933 0a6.57 6.57 0 0 1 3.65 1.82a100 100 0 0 0-1.986 1.93q-1.876-1.59-4.188-.734q-1.696.78-2.362 2.528a78 78 0 0 1-2.148-1.658a.26.26 0 0 0-.16-.027q1.683-3.245 5.26-3.86" opacity="0.987"/>
                        <path fill="#ffc107" d="M1.946 4.92q.085-.013.161.027a78 78 0 0 0 2.148 1.658A7.6 7.6 0 0 0 4.04 7.99q.037.678.215 1.331L2 11.116Q.527 8.038 1.946 4.92" opacity="0.997"/>
                        <path fill="#448aff" d="M12.685 13.29a26 26 0 0 0-2.202-1.74q1.15-.812 1.396-2.228H8.122V6.713q3.25-.027 6.497.055q.616 3.345-1.423 6.032a7 7 0 0 1-.51.49" opacity="0.999"/>
                        <path fill="#43a047" d="M4.255 9.322q1.23 3.057 4.51 2.854a3.94 3.94 0 0 0 1.718-.626q1.148.812 2.202 1.74a6.62 6.62 0 0 1-4.027 1.684a6.4 6.4 0 0 1-1.02 0Q3.82 14.524 2 11.116z" opacity="0.993"/>
                    </g>
                </svg>
            </a>
            @if (Route::has('register'))
                <a href="{{route('register')}}" wire:navigate class="button btn-tertiary">
                     Registrarme
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                        <g fill="none" fill-rule="evenodd">
                            <path d="M24 0v24H0V0zM12.593 23.258l-.011.002l-.071.035l-.02.004l-.014-.004l-.071-.035q-.016-.005-.024.005l-.004.01l-.017.428l.005.02l.01.013l.104.074l.015.004l.012-.004l.104-.074l.012-.016l.004-.017l-.017-.427q-.004-.016-.017-.018m.265-.113l-.013.002l-.185.093l-.01.01l-.003.011l.018.43l.005.012l.008.007l.201.093q.019.005.029-.008l.004-.014l-.034-.614q-.005-.019-.02-.022m-.715.002a.02.02 0 0 0-.027.006l-.006.014l-.034.614q.001.018.017.024l.015-.002l.201-.093l.01-.008l.004-.011l.017-.43l-.003-.012l-.01-.01z" />
                            <path fill="currentColor" d="M16.06 10.94a1.5 1.5 0 0 1 0 2.12l-5.656 5.658a1.5 1.5 0 1 1-2.121-2.122L12.879 12L8.283 7.404a1.5 1.5 0 0 1 2.12-2.122l5.658 5.657Z" />
                        </g>
                    </svg>
                </a>
            @endif
        </div>
    </div>
</div>
