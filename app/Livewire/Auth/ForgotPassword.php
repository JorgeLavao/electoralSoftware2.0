<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class ForgotPassword extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $email = mb_strtolower(trim($this->email));
        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if (! $user) {
            $this->addError('email', 'El correo electrónico no hace parte de nuestra base de información.');
            return;
        }

        Password::sendResetLink(['email' => $user->email]);

        session()->flash('status', 'Ya se envió el correo de recuperación. Revisa tu bandeja de entrada o spam.');
    }
}
