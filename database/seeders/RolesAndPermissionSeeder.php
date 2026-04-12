<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'web';

        $globalPermissions = [
            'system.admin',
            'campaigns.create',
            'campaigns.view',
            'users.manage',
            'roles.manage',
        ];

        $campaignPermissions = [
            'campaign.view',
            'campaign.update',
            'campaign.members.view',
            'campaign.members.manage',
            'voters.view',
            'voters.create',
            'voters.update',
            'reports.view',
            'lists.view',
            'lists.manage',
            'news.view',
            'news.create',
            'news.update',
        ];

        foreach (array_merge($globalPermissions, $campaignPermissions) as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => $guard,
            ]);
        }

        $this->createGlobalRoles($guard, $globalPermissions);
        $this->createCampaignRoles($guard, $campaignPermissions);
        $this->createSuperAdmin($guard);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function createGlobalRoles(string $guard, array $globalPermissions): void
    {
        $superAdminRole = Role::firstOrCreate([
            'name' => 'super-admin',
            'guard_name' => $guard,
            'campaign_id' => null,
        ]);

        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => $guard,
            'campaign_id' => null,
        ]);

        $superAdminRole->syncPermissions(Permission::whereIn('name', array_merge(
            $globalPermissions,
            [
                'campaign.view',
                'campaign.update',
                'campaign.members.view',
                'campaign.members.manage',
                'voters.view',
                'voters.create',
                'voters.update',
                'reports.view',
                'lists.view',
                'lists.manage',
                'news.view',
                'news.create',
                'news.update',
            ]
        ))->get());

        $adminRole->syncPermissions(Permission::whereIn('name', [
            'campaigns.create',
            'campaigns.view',
            'users.manage',
            'campaign.view',
            'campaign.members.view',
            'reports.view',
        ])->get());
    }

    private function createCampaignRoles(string $guard, array $campaignPermissions): void
    {
        $directorRole = Role::firstOrCreate([
            'name' => 'director-campania',
            'guard_name' => $guard,
            'campaign_id' => 1,
        ]);

        $coordinadorRole = Role::firstOrCreate([
            'name' => 'coordinador',
            'guard_name' => $guard,
            'campaign_id' => 1,
        ]);

        $digitadorRole = Role::firstOrCreate([
            'name' => 'digitador',
            'guard_name' => $guard,
            'campaign_id' => 1,
        ]);

        $consultaRole = Role::firstOrCreate([
            'name' => 'consulta',
            'guard_name' => $guard,
            'campaign_id' => 1,
        ]);

        $directorRole->syncPermissions(Permission::whereIn('name', $campaignPermissions)->get());

        $coordinadorRole->syncPermissions(Permission::whereIn('name', [
            'campaign.view',
            'campaign.members.view',
            'campaign.members.manage',
            'voters.view',
            'voters.create',
            'voters.update',
            'reports.view',
            'lists.view',
            'news.view',
        ])->get());

        $digitadorRole->syncPermissions(Permission::whereIn('name', [
            'campaign.view',
            'voters.view',
            'voters.create',
            'voters.update',
            'lists.view',
            'news.view',
        ])->get());

        $consultaRole->syncPermissions(Permission::whereIn('name', [
            'campaign.view',
            'campaign.members.view',
            'voters.view',
            'reports.view',
            'lists.view',
            'news.view',
        ])->get());
    }

    private function createSuperAdmin(string $guard): void
    {
        $documentType = DocumentType::query()->first();

        if (! $documentType) {
            throw new \RuntimeException('Debes tener al menos un document type creado antes de correr este seeder.');
        }

        setPermissionsTeamId(null);

        $user = User::firstOrCreate(
            ['email' => 'admin@nvst.com'],
            [
                'document_type_id' => $documentType->id,
                'document_number' => '1000000000',
                'first_name' => 'Super',
                'middle_name' => null,
                'paternal_surname' => 'Admin',
                'maternal_surname' => null,
                'celphone' => '3000000000',
                'current_campaign' => null,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $user->assignRole('super-admin');
    }
}
