<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Laravel\Fortify\Features;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        if (blank(config('services.google.client_id')) || blank(config('services.google.client_secret'))) {
            return redirect()
                ->route('login')
                ->with('status', 'Faltan las credenciales de Google. Configura GOOGLE_CLIENT_ID y GOOGLE_CLIENT_SECRET en el archivo .env.');
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable) {
            return redirect()
                ->route('login')
                ->with('status', 'No fue posible conectar con Google. Intenta nuevamente.');
        }

        $email = strtolower($googleUser->getEmail());
        $nameParts = $this->splitName($googleUser->getName() ?: $googleUser->getNickname() ?: $email);

        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $email)
            ->first();

        if ($user) {
            $user->forceFill([
                'google_id' => $googleUser->getId(),
                'google_avatar' => $googleUser->getAvatar(),
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();
        } else {
            $user = User::create([
                'google_id' => $googleUser->getId(),
                'google_avatar' => $googleUser->getAvatar(),
                'first_name' => $nameParts['first_name'],
                'middle_name' => $nameParts['middle_name'],
                'paternal_surname' => $nameParts['paternal_surname'],
                'maternal_surname' => $nameParts['maternal_surname'],
                'email' => $email,
                'email_verified_at' => now(),
            ]);
        }

        if (Features::canManageTwoFactorAuthentication() && $user->hasEnabledTwoFactorAuthentication()) {
            Session::put([
                'login.id' => $user->getKey(),
                'login.remember' => true,
            ]);

            return redirect()->route('two-factor.login');
        }

        Auth::login($user, remember: true);
        Session::regenerate();

        $campaign = $this->resolveCurrentCampaign($user);

        if ($campaign) {
            $user->forceFill(['current_campaign' => $campaign->code])->save();
            session(['current_campaign' => $campaign]);
        } else {
            $user->forceFill(['current_campaign' => null])->save();
            session()->forget('current_campaign');
        }

        return redirect()->intended(route('campaign.index', absolute: false));
    }

    /**
     * @return array{first_name: string, middle_name: ?string, paternal_surname: ?string, maternal_surname: ?string}
     */
    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];

        return [
            'first_name' => $parts[0] ?? 'Usuario',
            'middle_name' => count($parts) > 3 ? $parts[1] : null,
            'paternal_surname' => $parts[count($parts) - 1] ?? null,
            'maternal_surname' => count($parts) > 3 ? $parts[count($parts) - 2] : null,
        ];
    }

    private function resolveCurrentCampaign(User $user): ?Campaign
    {
        $currentCampaign = $user->current_campaign
            ? Campaign::firstWhere('code', $user->current_campaign)
            : null;

        if ($currentCampaign && ($user->is_super_admin || $user->belongsToCampaign($currentCampaign))) {
            return $currentCampaign;
        }

        if ($user->is_super_admin) {
            return Campaign::query()
                ->where('status', '1')
                ->orderBy('name')
                ->first();
        }

        return Campaign::query()
            ->where('status', '1')
            ->where(function ($query) use ($user) {
                $query->whereHas('staff_users', function ($staffQuery) use ($user) {
                    $staffQuery->where('users.id', $user->id)
                        ->where('campaign_staff.status', true);
                })->orWhereHas('foreign_users', function ($supporterQuery) use ($user) {
                    $supporterQuery->where('users.id', $user->id)
                        ->where('campaign_user.validate', '!=', 2);
                });
            })
            ->orderBy('name')
            ->first();
    }
}
