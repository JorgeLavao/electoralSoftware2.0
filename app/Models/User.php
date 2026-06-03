<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Notifications\CustomResetPassword;
use App\Models\Campaign;
use App\Services\ClientesMas\ClientesMasMailer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
        'google_id',
        'google_avatar',
        'profile_photo_path',
        'document_type_id',
        'document_number',
        'first_name',
        'middle_name',
        'paternal_surname',
        'maternal_surname',
        'current_campaign',
        'is_super_admin',
        'platform_role',
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

    public const ROLE_ADMIN = 'admin';
    public const ROLE_CAMPAIGN_MANAGER = 'campaign_manager';
    public const ROLE_CALL_CENTER = 'call_center';
    public const ROLE_TECH_SUPPORT = 'technical_support';
    public const ROLE_SUPPORTER = 'supporter';

    public const ROLE_LABELS = [
        self::ROLE_ADMIN => 'Administrador',
        self::ROLE_CAMPAIGN_MANAGER => 'Coordinador de Campaña',
        self::ROLE_CALL_CENTER => 'Call Center',
        self::ROLE_TECH_SUPPORT => 'Soporte tecnico',
        self::ROLE_SUPPORTER => 'Simpatizante',
    ];

    public const CALL_CENTER_CAMPAIGN_PERMISSIONS = [
        'campaign.supporters.view',
        'campaign.supporters.refer',
        'campaign.supporters.validate',
        'campaign.lists.view',
        'campaign.votation-point.view',
        'campaign.votation-point.manage',
    ];

    public function effectiveRole(): string
    {
        if ($this->is_super_admin) {
            return self::ROLE_ADMIN;
        }

        return $this->platform_role ?: self::ROLE_SUPPORTER;
    }

    public function roleLabel(): string
    {
        return self::ROLE_LABELS[$this->effectiveRole()] ?? self::ROLE_LABELS[self::ROLE_SUPPORTER];
    }

    public function campaignRoleLabel(?Campaign $campaign = null): string
    {
        if ($this->is_super_admin) {
            return self::ROLE_LABELS[self::ROLE_ADMIN];
        }

        if (! $campaign) {
            return $this->roleLabel();
        }

        $roleNames = DB::table(config('permission.table_names.model_has_roles', 'model_has_roles'))
            ->join(config('permission.table_names.roles', 'roles'), 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', self::class)
            ->where('model_has_roles.model_id', $this->id)
            ->where('model_has_roles.campaign_id', $campaign->id)
            ->where('roles.campaign_id', $campaign->id)
            ->orderBy('roles.name')
            ->pluck('roles.name')
            ->filter()
            ->unique()
            ->values();

        if ($roleNames->isNotEmpty()) {
            return $roleNames->implode(', ');
        }

        $staffRole = $this->foreign_campaings()
            ->where('campaigns.id', $campaign->id)
            ->wherePivot('status', true)
            ->first()?->pivot?->role;

        if ($staffRole) {
            return match ($staffRole) {
                'coordinator' => 'Coordinador',
                'support' => self::ROLE_LABELS[self::ROLE_TECH_SUPPORT],
                'call_center' => self::ROLE_LABELS[self::ROLE_CALL_CENTER],
                default => str($staffRole)->replace(['_', '-'], ' ')->headline()->toString(),
            };
        }

        if ($this->supporter_campaigns()
            ->where('campaigns.id', $campaign->id)
            ->wherePivot('validate', '!=', 2)
            ->exists()) {
            return self::ROLE_LABELS[self::ROLE_SUPPORTER];
        }

        return $this->roleLabel();
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

    public function initials(): string
    {
        $parts = collect([
            $this->first_name,
            $this->paternal_surname,
        ])->filter();

        if ($parts->isEmpty()) {
            $parts = collect(explode(' ', trim((string) $this->fullName)))->filter();
        }

        return $parts
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('') ?: 'US';
    }

    public function hasProfilePhoto(): bool
    {
        return filled($this->profile_photo_path) || filled($this->google_avatar);
    }

    public function profilePhotoUrl(): ?string
    {
        if ($this->profile_photo_path) {
            return Storage::disk('public')->url($this->profile_photo_path);
        }

        return $this->google_avatar ?: null;
    }

    public function sendPasswordResetNotification($token){
        $mailer = app(ClientesMasMailer::class);

        if ($mailer->enabled()) {
            $mailer->sendPasswordReset($this, $token);
            return;
        }

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

    public function foreign_groups()
    {
        return $this->belongsToMany(Group::class, 'group_user', 'user_id', 'group_id')
            ->withPivot('role', 'notes')
            ->withTimestamps();
    }

    public function committees()
    {
        return $this->belongsToMany(Committee::class, 'committee_user', 'user_id', 'committee_id')
            ->withPivot('role')
            ->withTimestamps();
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
            ->exists()
            || $this->supporter_campaigns()
                ->where('campaigns.id', $campaignId)
                ->wherePivot('validate', '!=', 2)
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

        if ($this->effectiveRole() === self::ROLE_CALL_CENTER
            && in_array($permission, self::CALL_CENTER_CAMPAIGN_PERMISSIONS, true)) {
            return true;
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
