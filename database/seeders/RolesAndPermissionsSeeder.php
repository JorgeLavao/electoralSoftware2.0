<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $platformPermissions = [
            [
                'name'          => 'platform.news.view',
                'group_key'     => 'platform.news',
                'group_label'   => 'Publicaciones de la plataforma',
                'description'   => 'Ver publicaciones',
            ],
            [
                'name'          => 'platform.news.create',
                'group_key'     => 'platform.news',
                'group_label'   => 'Publicaciones de la plataforma',
                'description'   => 'Crear publicaciones',
            ],
            [
                'name'          => 'platform.news.update',
                'group_key'     => 'platform.news',
                'group_label'   => 'Publicaciones de la plataforma',
                'description'   => 'Editar publicaciones',
            ],
            [
                'name'          => 'platform.news.delete',
                'group_key'     => 'platform.news',
                'group_label'   => 'Publicaciones de la plataforma',
                'description'   => 'Eliminar publicaciones',
            ],
        ];

        foreach ($platformPermissions as $permission) {
            DB::table('platform_permissions')->updateOrInsert(
                ['name' => $permission['name']],
                [
                    'group_key'     => $permission['group_key'],
                    'group_label'   => $permission['group_label'],
                    'description'   => $permission['description'],
                    'updated_at'    => now(),
                    'created_at'    => now(),
                ]
            );
        }

        $superAdmin = User::query()->updateOrCreate(
            ['email' => 'softwarenuevastic@gmail.com'],
            [
                'document_type_id'  => 6,
                'document_number'   => '9000000-1',
                'first_name'        => 'Administrador',
                'paternal_surname'  => 'NuevasTic',
                'celphone'          => 3153907092,
                'email_verified_at' => now(),
                'password' => Hash::make('Admin12345*'),
            ]
        );

        $superAdmin->forceFill([
            'is_super_admin' => true,
        ])->save();

        $superAdmin->foreing_aditional_info()->updateOrCreate(['user_id' => $superAdmin->id],
        [
            'gender_id'                 => 5,
            'occupation_id'             => 13,
            'vehicle'                   => 0,
            'age_range_id'              => 5,
            'zone'                      => 'urbana',
            'department'                => json_encode(['id' => 18,     "name" => "Huila"]),
            'municipality'              => json_encode(['id' => 657,    "name" => "Neiva"]),
            'neighborhood_village_name' => 'Candido',
            'latitude'                  => 2.94741900,
            'longitude'                 => -75.29745370,
            'current_location'          => 'Cl. 34 #1 A- 14, Neiva, Huila, Colombia'
        ]);

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
    }
}
