<?php

use App\Http\Controllers\Exports\DownloadExportController;
use App\Http\Controllers\Exports\ListExportController;
use App\Http\Controllers\Users\SearchCampaignUsersController;
use App\Http\Controllers\Users\SearchUserController;
use App\Livewire\Admin\UserRoles;
use App\Livewire\Campaign\AcceptCampaign;
use App\Livewire\Campaign\AddSupporter;
use App\Livewire\Campaign\CreateGroup;
use App\Livewire\Campaign\EditGroup;
use App\Livewire\Campaign\IndexCampaign;
use App\Livewire\Campaign\ManageGroups;
use App\Livewire\Committee\IndexCommittee;
use App\Livewire\List\CreateList;
use App\Livewire\List\EditList;
use App\Livewire\Point\IndexPoint;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\CompleteInfo;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use App\Livewire\Supporters\ImportSupporter;
use App\Livewire\Supporters\IndexSupporters;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::get('api/buscar-usuarios', SearchUserController::class)->middleware('axios', 'auth', 'throttle:30,1');
Route::get('api/campanias/{campaign:code}/buscar-usuarios', SearchCampaignUsersController::class)
    ->middleware('axios', 'auth', 'throttle:30,1')
    ->name('campaign.users.search');

Route::get('exports/{exportBatch}/download', DownloadExportController::class)
    ->middleware('auth')
    ->name('exports.download');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        $currentCampaign = session('current_campaign');
        $user = auth()->user();

        $news = News::query()
            ->with('user')
            ->visibleForUserInCampaign($user, $currentCampaign)
            ->orderByDesc('published_at')
            ->latest()
            ->paginate(3);

        return view('dashboard', compact('news'));
    })->middleware('verified')->name('dashboard');

    Route::get('/news-manager', function () {
        Gate::authorize('create', News::class);

        return view('news-manager');
    })->middleware('verified')->name('news.manager');

    Route::get('/news-manager/{news}', function (News $news) {
        Gate::authorize('update', $news);

        return view('news-manager', compact('news'));
    })->middleware('verified')->name('news.edit');

    Route::get('/mi-perfil', function () {
        $user = auth()->user()->loadMissing([
            'foreign_document_type',
            'foreing_aditional_info.foreign_gender',
            'foreing_aditional_info.foreign_occupations',
            'foreing_aditional_info.foreign_range_age',
        ]);

        $profile = $user->foreing_aditional_info;
        $department = $profile?->department ? json_decode($profile->department, true) : null;
        $municipality = $profile?->municipality ? json_decode($profile->municipality, true) : null;

        return view('profile.show', compact('user', 'profile', 'department', 'municipality'));
    })->middleware('verified')->name('profile.show');

    Route::patch('/mi-perfil/foto', function (Request $request) {
        $validated = $request->validate([
            'profile_photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = $request->user();

        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $user->profile_photo_path = $validated['profile_photo']->store('profile-photos', 'public');
        $user->save();

        return back()->with('profile_photo_status', 'Foto de perfil actualizada correctamente.');
    })->middleware('verified')->name('profile.photo.update');

    Route::get('campanias', IndexCampaign::class)->name('campaign.index');
    Route::get('administracion/usuarios', UserRoles::class)->middleware('verified')->name('admin.users.roles');
});

Route::middleware(['auth', 'complete-info'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

    Route::get('campanias/{campaign:code}/referir-simpatizante', AddSupporter::class)->name('campaign.add-supporter');
    Route::get('campanias/{campaign:code}/comites', IndexCommittee::class)->name('campaign.committees');
    Route::get('campanias/{campaign:code}/grupos', ManageGroups::class)->name('campaign.groups');
    Route::get('campanias/{campaign:code}/grupos/crear', CreateGroup::class)->name('campaign.groups.create');
    Route::get('campanias/{campaign:code}/grupos/{group}/editar', EditGroup::class)->name('campaign.groups.edit');

    Route::get('campanias/{campaign:code}/simpatizantes', IndexSupporters::class)->name('supporter.index');
    Route::get('campanias/{campaign:code}/importar-simpatizantes', ImportSupporter::class)->name('supporter.import');
    Route::get('/download/plantilla-simpatizantes', function () {
        return response()->download(public_path('templates/Plantilla_Simpatizantes.xlsx'));
    })->name('download.template.supporter');
    
    Route::get('campanias/{campaign:code}/punto-votacion/', IndexPoint::class)->name('point.index');

    Route::get('campanias/{campaign:code}/listados/', CreateList::class)->name('list.index');
    Route::get('campanias/{campaign:code}/listados/crear', CreateList::class)->name('list.create');
    Route::get('campanias/{campaign:code}/listados/{list}/editar', EditList::class)->name('list.edit');
    Route::get('campanias/{campaign:code}/listados/{list}/exportar', ListExportController::class)->name('list.export');

    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )->name('two-factor.show');
});

Route::get('/completar-registro', CompleteInfo::class)->name('profile.complete-register')->middleware(['auth']);
Route::get('/invitaciones/aceptar/{invitation:token}', AcceptCampaign::class)->name('campaign.accept-invitation');

require __DIR__ . '/auth.php';
