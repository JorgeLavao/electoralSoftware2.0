<?php

use App\Exports\ListUsersExport;
use App\Http\Controllers\Users\SearchUserController;
use App\Livewire\Campaign\AcceptCampaign;
use App\Livewire\Campaign\AddSupporter;
use App\Livewire\Campaign\CreateGroup;
use App\Livewire\Campaign\EditGroup;
use App\Livewire\Campaign\IndexCampaign;
use App\Livewire\Campaign\ManageGroups;
use App\Livewire\List\CreateList;
use App\Livewire\List\EditList;
use App\Livewire\List\IndexList;
use App\Livewire\Point\IndexPoint;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\CompleteInfo;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use App\Livewire\Supporters\ImportSupporter;
use App\Livewire\Supporters\IndexSupporters;
use App\Models\Campaign;
use App\Models\CampaignList;
use App\Models\News;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Maatwebsite\Excel\Facades\Excel;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::get('api/buscar-usuarios', SearchUserController::class)->middleware('axios', 'auth');

Route::middleware(['auth'])->group(function () {
    // noticias
    Route::get('/dashboard', function () {
        $news = News::query()
            ->with('user')
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

    // campanas
    Route::get('campanias', IndexCampaign::class)->name('campaign.index');
});

Route::middleware(['auth', 'complete-info'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

    // campanas
    Route::get('campanias/{campaign:code}/referir-simpatizante', AddSupporter::class)->name('campaign.add-supporter');
    Route::get('campanias/{campaign:code}/grupos', ManageGroups::class)->name('campaign.groups');
    Route::get('campanias/{campaign:code}/grupos/crear', CreateGroup::class)->name('campaign.groups.create');
    Route::get('campanias/{campaign:code}/grupos/{group}/editar', EditGroup::class)->name('campaign.groups.edit');

    // simpatizantes
    Route::get('campanias/{campaign:code}/simpatizantes', IndexSupporters::class)->name('supporter.index');
    Route::get('campanias/{campaign:code}/importar-simpatizantes', ImportSupporter::class)->name('supporter.import');
    Route::get('/download/plantilla-simpatizantes', function () {
        return response()->download(
            public_path('templates/Plantilla_Simpatizantes.xlsx')
        );
    })->name('download.template.supporter');

    function get_size_in_mb($size)
    {
        $unit = strtoupper(substr($size, -1));
        $value = (float) $size;

        switch ($unit) {
            case 'G':
                return $value * 1024;
            case 'M':
                return $value;
            case 'K':
                return $value / 1024;
            default:
                return $value / (1024 * 1024);
        }
    }

    // Punto de Votacion
    Route::get('campanias/{campaign:code}/punto-votacion/', IndexPoint::class)->name('point.index');

    // listados
    Route::get('campanias/{campaign:code}/listados/', IndexList::class)->name('list.index');
    Route::get('campanias/{campaign:code}/listados/crear', CreateList::class)->name('list.create');
    Route::get('campanias/{campaign:code}/listados/{list}/editar', EditList::class)->name('list.edit');
    Route::get('campanias/{campaign:code}/listados/{list}/exportar', function (Campaign $campaign, CampaignList $list) {
        abort_unless($list->campaign_id === $campaign->id, 404);
        Gate::authorize('exportLists', $campaign);

        $fileName = 'listado-' . $list->id . '-' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new ListUsersExport($list), $fileName);
    })->name('list.export');

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
