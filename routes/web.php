<?php

use App\Exports\PersonasExport;
use App\Http\Controllers\Users\SearchUserController;
use App\Livewire\Campaign\AcceptCampaign;
use App\Livewire\Campaign\AddSupporter;
use App\Livewire\Campaign\IndexCampaign;
use App\Livewire\List\CreateList;
use App\Livewire\List\EditList;
use App\Livewire\List\IndexList;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\CompleteInfo;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use App\Livewire\Supporters\ImportSupporter;
use App\Livewire\Supporters\IndexSupporters;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Maatwebsite\Excel\Facades\Excel;


Route::get('/', function () {return  redirect()->route('dashboard');})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified', 'complete-info'])
    ->name('dashboard');

Route::get('api/buscar-usuarios', SearchUserController::class)->middleware('axios', 'auth');

Route::middleware(['auth', 'complete-info'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

    // campañas
    Route::get('campanias', IndexCampaign::class)->name('campaign.index');
    Route::get('campanias/{campaign:code}/referir-simpatizante', AddSupporter::class)->name('campaign.add-supporter');

    //simpatizantes
    Route::get('campanias/{campaign:code}/simpatizantes',               IndexSupporters::class)->name('supporter.index');
    Route::get('campanias/{campaign:code}/importar-simpatizantes',      ImportSupporter::class)->name('supporter.import');
    Route::get('/download/plantilla-simpatizantes', function () {
        return response()->download(
            public_path('templates/Plantilla_Simpatizantes.xlsx')
        );})->name('download.template.supporter');
        
function get_size_in_mb($size) {
    $unit = strtoupper(substr($size, -1));
    $value = (float) $size;

    switch ($unit) {
        case 'G': return $value * 1024;
        case 'M': return $value;
        case 'K': return $value / 1024;
        default: return $value / (1024 * 1024);
    }
}




//     Route::get('/php-info', function() {
//     phpinfo();
// });

    // listados
    Route::get('campanias/{campaign:code}/listados/',               IndexList::class)->name('list.index');
    Route::get('campanias/{campaign:code}/listados/crear',          CreateList::class)->name('list.create');
    Route::get('campanias/{campaign:code}/listados/{list}/editar',  EditList::class)->name('list.edit');

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

Route::get('/completar-registro',           CompleteInfo::class)->name('profile.complete-register')->middleware(['auth']);
Route::get('/invitaciones/aceptar/{invitation:token}', AcceptCampaign::class)->name('campaign.accept-invitation');

require __DIR__.'/auth.php';
