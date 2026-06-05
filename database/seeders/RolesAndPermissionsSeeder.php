<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $platformPermissions = [
            [
                'name' => 'platform.news.view',
                'group_key' => 'platform.news',
                'group_label' => 'Publicaciones de la plataforma',
                'description' => 'Ver publicaciones',
            ],
            [
                'name' => 'platform.news.create',
                'group_key' => 'platform.news',
                'group_label' => 'Publicaciones de la plataforma',
                'description' => 'Crear publicaciones',
            ],
            [
                'name' => 'platform.news.update',
                'group_key' => 'platform.news',
                'group_label' => 'Publicaciones de la plataforma',
                'description' => 'Editar publicaciones',
            ],
            [
                'name' => 'platform.news.delete',
                'group_key' => 'platform.news',
                'group_label' => 'Publicaciones de la plataforma',
                'description' => 'Eliminar publicaciones',
            ],
            [
                'name' => 'platform.campaign.view-all',
                'group_key' => 'platform.campaign',
                'group_label' => 'Campañas de la plataforma',
                'description' => 'Ver todas las campañas',
            ],
            [
                'name' => 'platform.campaign.create',
                'group_key' => 'platform.campaign',
                'group_label' => 'Campañas de la plataforma',
                'description' => 'Crear campañas',
            ],
            [
                'name' => 'platform.campaign.update',
                'group_key' => 'platform.campaign',
                'group_label' => 'Campañas de la plataforma',
                'description' => 'Editar campañas',
            ],
        ];

        foreach ($platformPermissions as $permission) {
            DB::table('platform_permissions')->updateOrInsert(
                ['name' => $permission['name']],
                [
                    'group_key' => $permission['group_key'],
                    'group_label' => $permission['group_label'],
                    'description' => $permission['description'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $campaignPermissions = [
            [
                'name' => 'campaign.news.view',
                'group_key' => 'campaign.news',
                'group_label' => 'Noticias de campana',
                'description' => 'Ver noticias',
            ],
            [
                'name' => 'campaign.news.create',
                'group_key' => 'campaign.news',
                'group_label' => 'Noticias de campana',
                'description' => 'Crear noticias',
            ],
            [
                'name' => 'campaign.news.update',
                'group_key' => 'campaign.news',
                'group_label' => 'Noticias de campana',
                'description' => 'Editar noticias',
            ],
            [
                'name' => 'campaign.news.delete',
                'group_key' => 'campaign.news',
                'group_label' => 'Noticias de campana',
                'description' => 'Eliminar noticias',
            ],
            [
                'name' => 'campaign.supporters.view',
                'group_key' => 'campaign.supporters',
                'group_label' => 'Simpatizantes de campaña',
                'description' => 'Ver simpatizantes',
            ],
            [
                'name' => 'campaign.supporters.refer',
                'group_key' => 'campaign.supporters',
                'group_label' => 'Simpatizantes de campaña',
                'description' => 'Referir simpatizantes',
            ],
            [
                'name' => 'campaign.supporters.import',
                'group_key' => 'campaign.supporters',
                'group_label' => 'Simpatizantes de campaña',
                'description' => 'Importar simpatizantes',
            ],
            [
                'name' => 'campaign.supporters.validate',
                'group_key' => 'campaign.supporters',
                'group_label' => 'Simpatizantes de campaña',
                'description' => 'Validar simpatizantes',
            ],
            [
                'name' => 'campaign.supporters.remove',
                'group_key' => 'campaign.supporters',
                'group_label' => 'Simpatizantes de campaña',
                'description' => 'Eliminar simpatizantes de la campaña',
            ],
            [
                'name' => 'campaign.lists.view',
                'group_key' => 'campaign.lists',
                'group_label' => 'Listados de campaña',
                'description' => 'Ver listados',
            ],
            [
                'name' => 'campaign.lists.create',
                'group_key' => 'campaign.lists',
                'group_label' => 'Listados de campaña',
                'description' => 'Crear listados',
            ],
            [
                'name' => 'campaign.lists.update',
                'group_key' => 'campaign.lists',
                'group_label' => 'Listados de campaña',
                'description' => 'Editar listados',
            ],
            [
                'name' => 'campaign.lists.delete',
                'group_key' => 'campaign.lists',
                'group_label' => 'Listados de campaña',
                'description' => 'Eliminar listados',
            ],
            [
                'name' => 'campaign.lists.export',
                'group_key' => 'campaign.lists',
                'group_label' => 'Listados de campaña',
                'description' => 'Exportar listados',
            ],
            [
                'name' => 'campaign.groups.view',
                'group_key' => 'campaign.groups',
                'group_label' => 'Grupos de campana',
                'description' => 'Ver grupos',
            ],
            [
                'name' => 'campaign.groups.manage',
                'group_key' => 'campaign.groups',
                'group_label' => 'Grupos de campana',
                'description' => 'Gestionar grupos',
            ],
            [
                'name' => 'campaign.committees.view',
                'group_key' => 'campaign.committees',
                'group_label' => 'Comites de campana',
                'description' => 'Ver comites',
            ],
            [
                'name' => 'campaign.committees.manage',
                'group_key' => 'campaign.committees',
                'group_label' => 'Comites de campana',
                'description' => 'Gestionar comites',
            ],
            [
                'name' => 'campaign.members.remove',
                'group_key' => 'campaign.members',
                'group_label' => 'Miembros de campaña',
                'description' => 'Sacar miembros de la campaña',
            ],
            [
                'name' => 'campaign.votation-point.view',
                'group_key' => 'campaign.votation-point',
                'group_label' => 'Punto de votación',
                'description' => 'Ver punto de votación',
            ],
            [
                'name' => 'campaign.votation-point.manage',
                'group_key' => 'campaign.votation-point',
                'group_label' => 'Punto de votación',
                'description' => 'Gestionar punto de votación',
            ],
        ];

        foreach ($campaignPermissions as $campaignPermission) {
            DB::table(config('permission.table_names.permissions', 'permissions'))->updateOrInsert(
                [
                    'name' => $campaignPermission['name'],
                    'guard_name' => 'web',
                ],
                [
                    'group_key' => $campaignPermission['group_key'],
                    'group_label' => $campaignPermission['group_label'],
                    'description' => $campaignPermission['description'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $campaignCoordinatorRole = Role::query()->firstOrCreate(
            [
                'name' => 'Coordinador de Campaña',
                'guard_name' => 'web',
                'campaign_id' => null,
            ]
        );

        $campaignCoordinatorRole->syncPermissions(
            array_column($campaignPermissions, 'name')
        );

        $callCenterRole = Role::query()->firstOrCreate(
            [
                'name' => 'Call Center',
                'guard_name' => 'web',
                'campaign_id' => null,
            ]
        );

        $callCenterRole->syncPermissions(User::CALL_CENTER_CAMPAIGN_PERMISSIONS);

        $superAdmin = User::query()->updateOrCreate(
            ['email' => 'softwarenuevastic@gmail.com'],
            [
                'document_type_id' => 6,
                'document_number' => '9000000-1',
                'first_name' => 'Administrador',
                'paternal_surname' => 'NuevasTic',
                'celphone' => 3153907092,
                'email_verified_at' => now(),
                'password' => Hash::make('Admin12345*'),
            ]
        );

        $superAdmin->forceFill([
            'is_super_admin' => true,
            'platform_role' => User::ROLE_ADMIN,
        ])->save();

        $superAdmin->foreing_aditional_info()->updateOrCreate(
            ['user_id' => $superAdmin->id],
            [
                'gender_id' => 5,
                'occupation_id' => 13,
                'vehicle' => 0,
                'age_range_id' => 5,
                'zone' => 'urbana',
                'department' => json_encode(['id' => 18, 'name' => 'Huila']),
                'municipality' => json_encode(['id' => 657, 'name' => 'Neiva']),
                'neighborhood_village_name' => 'Candido',
                'latitude' => 2.94741900,
                'longitude' => -75.29745370,
                'current_location' => 'Cl. 34 #1 A- 14, Neiva, Huila, Colombia',
            ]
        );

        $permissionIds = DB::table('platform_permissions')
            ->whereIn('name', array_column($platformPermissions, 'name'))
            ->pluck('id');

        foreach ($permissionIds as $permissionId) {
            DB::table('platform_permission_user')->updateOrInsert(
                [
                    'platform_permission_id' => $permissionId,
                    'user_id' => $superAdmin->id,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
