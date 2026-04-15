<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Notifications\CustomResetPassword;
use App\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Casts\Attribute;

use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'document_type_id',
        'document_number',
        'first_name',
        'middle_name',
        'paternal_surname',
        'maternal_surname',
        'current_campaign',
        'is_super_admin',
        'celphone',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_super_admin'    => 'boolean',
            'password'          => 'hashed',
        ];
    }

    public function scopeSearch($query, $search){
        if(empty($search)){
            return $query;
        }
        return $query->where(function ($q) use ($search) {
            $q->whereRaw(
                "TRIM(CONCAT_WS(' ', first_name, middle_name, paternal_surname, maternal_surname)) LIKE ?",
                ["%{$search}%"]
            )->orWhere('document_number', 'like', "%{$search}%");
        });
    }

    protected function fullName(): Attribute {
        return Attribute::get(fn () => trim(
            implode(' ', array_filter([
                $this->first_name,
                $this->middle_name,
                $this->paternal_surname,
                $this->maternal_surname,
            ]))
        ));
    }
    public function sendPasswordResetNotification($token){
        $this->notify(new CustomResetPassword($token));
    }

    public function foreign_document_type(){
       return $this->belongsTo(DocumentType::class, 'document_type_id', 'id');
    }

    public function foreing_aditional_info(){
        return $this->hasOne(UserProfile::class);
    }

    public function foreign_campaings(){
        return $this->belongsToMany(Campaign::class, 'campaign_staff', 'user_id', 'campaign_id')
            ->withPivot('role', 'status')
            ->withTimestamps();
    }

    public function supporter_campaigns(){
        return $this->belongsToMany(Campaign::class)->withPivot('reffer_by', 'approach', 'validate');
    }

    public function foreign_lists(){
        return $this->belongsToMany(CampaignList::class, 'list_user', 'user_id', 'list_id');
    }

    public function platform_permissions(): BelongsToMany
    {
        return $this->belongsToMany(PlatformPermission::class, 'platform_permission_user')->withTimestamps();
    }

    public function hasPlatformPermission(string $permission): bool
    {
        if ($this->is_super_admin) {
            return true;
        }
        return $this->platform_permissions()->where('name', $permission)->exists();
    }

    public function belongsToCampaign(Campaign|int $campaign): bool
    {
        $campaignId = $campaign instanceof Campaign ? $campaign->id : $campaign;

        return $this->foreign_campaings()
            ->where('campaigns.id', $campaignId)
            ->wherePivot('status', true)
            ->exists();
    }

    public function hasCampaignPermission(string $permission, Campaign|int $campaign): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        $campaignId = $campaign instanceof Campaign ? $campaign->id : $campaign;

        if (! $this->belongsToCampaign($campaignId)) {
            return false;
        }

        $previousTeamId = getPermissionsTeamId();

        app(PermissionRegistrar::class)->setPermissionsTeamId($campaignId);
        $this->unsetRelation('roles')->unsetRelation('permissions');

        try {
            return $this->can($permission);
        } finally {
            app(PermissionRegistrar::class)->setPermissionsTeamId($previousTeamId);
            $this->unsetRelation('roles')->unsetRelation('permissions');
        }
    }

    public function assignCampaignRole(string $role, Campaign|int $campaign): void
    {
        $campaignId = $campaign instanceof Campaign ? $campaign->id : $campaign;
        $previousTeamId = getPermissionsTeamId();

        app(PermissionRegistrar::class)->setPermissionsTeamId($campaignId);
        $this->unsetRelation('roles')->unsetRelation('permissions');

        try {
            $roleModel = Role::query()
                ->where('name', $role)
                ->where('guard_name', 'web')
                ->where(function ($query) use ($campaignId) {
                    $query->whereNull('campaign_id')
                        ->orWhere('campaign_id', $campaignId);
                })
                ->first();

            if ($roleModel) {
                $this->assignRole($roleModel);
            }
        } finally {
            app(PermissionRegistrar::class)->setPermissionsTeamId($previousTeamId);
            $this->unsetRelation('roles')->unsetRelation('permissions');
        }
    }

    public function removeCampaignRole(string $role, Campaign|int $campaign): void
    {
        $campaignId = $campaign instanceof Campaign ? $campaign->id : $campaign;
        $previousTeamId = getPermissionsTeamId();

        app(PermissionRegistrar::class)->setPermissionsTeamId($campaignId);
        $this->unsetRelation('roles')->unsetRelation('permissions');

        try {
            $roleModel = Role::query()
                ->where('name', $role)
                ->where('guard_name', 'web')
                ->where(function ($query) use ($campaignId) {
                    $query->whereNull('campaign_id')
                        ->orWhere('campaign_id', $campaignId);
                })
                ->first();

            if ($roleModel) {
                $this->removeRole($roleModel);
            }
        } finally {
            app(PermissionRegistrar::class)->setPermissionsTeamId($previousTeamId);
            $this->unsetRelation('roles')->unsetRelation('permissions');
        }
    }
}
