<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DemoCampaignRolesSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $rolePermissions = $this->rolePermissions();
        $campaigns = Campaign::query()->orderBy('id')->get();

        foreach ($campaigns as $campaign) {
            foreach ($rolePermissions as $roleName => $permissions) {
                $role = Role::query()->updateOrCreate(
                    [
                        'name' => $roleName,
                        'guard_name' => 'web',
                        'campaign_id' => $campaign->id,
                    ],
                    [
                        'updated_at' => now(),
                    ]
                );

                $role->syncPermissions($permissions);
            }
        }

        $this->resetCampaignDemoData($campaigns);
        $this->createAndAssignUsers($campaigns);

        User::query()
            ->where('is_super_admin', false)
            ->get()
            ->each(function (User $user) {
                $firstName = Str::lower(Str::ascii(trim(explode(' ', $user->first_name)[0] ?: 'usuario')));
                $user->forceFill([
                    'password' => Hash::make($firstName.'1234'),
                    'email_verified_at' => $user->email_verified_at ?: now(),
                ])->save();
            });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function rolePermissions(): array
    {
        $all = Permission::query()
            ->where('guard_name', 'web')
            ->where('name', 'like', 'campaign.%')
            ->pluck('name')
            ->all();

        return [
            'Coordinador Campaña' => $all,
            'Líder' => [
                'campaign.supporters.view',
                'campaign.supporters.refer',
                'campaign.lists.view',
                'campaign.lists.create',
                'campaign.votation-point.view',
            ],
            'Simpatizante' => [
                'campaign.supporters.view',
                'campaign.votation-point.view',
            ],
            'Soporte Técnico' => $all,
            'Call Center' => User::CALL_CENTER_CAMPAIGN_PERMISSIONS,
        ];
    }

    protected function resetCampaignDemoData($campaigns): void
    {
        $campaignIds = $campaigns->pluck('id')->all();

        DB::table(config('permission.table_names.model_has_roles', 'model_has_roles'))
            ->whereIn('campaign_id', $campaignIds)
            ->delete();

        DB::table('campaign_staff')
            ->whereIn('campaign_id', $campaignIds)
            ->delete();

        DB::table('campaign_user')
            ->whereIn('campaign_id', $campaignIds)
            ->delete();
    }

    protected function createAndAssignUsers($campaigns): void
    {
        $peopleByCampaign = [
            '01' => [
                ['Angela', 'Ramirez', 'angela@gmail.com', '1077225128', 'Coordinador Campaña'],
                ['Carlos', 'Lopez', 'carlos@gmail.com', '1059332211', 'Líder'],
                ['Luisa', 'Martinez', 'luisa@gmail.com', '1098765432', 'Líder'],
                ['Alejandro', 'Perez', 'alejandro@gmail.com', '1077224495', 'Soporte Técnico'],
                ['Camilo', 'Rojas', 'andres@gmail.com', '1077224436', 'Call Center'],
                ['Yolanda', 'Vargas', 'correo@gmail.com', '1234434', 'Simpatizante'],
                ['Julian', 'Torres', 'julian@gmail.com', '1087654634', 'Simpatizante'],
                ['Santiago', 'Diaz', 'correo54@gmail.com', '343494340', 'Simpatizante'],
                ['Valentina', 'Gomez', 'valentina.demo01@gmail.com', '20010001', 'Simpatizante'],
                ['Mateo', 'Castro', 'mateo.demo01@gmail.com', '20010002', 'Simpatizante'],
            ],
            '02' => [
                ['Natalia', 'Ortiz', 'natalia.demo02@gmail.com', '20020001', 'Coordinador Campaña'],
                ['Felipe', 'Moreno', 'felipe.demo02@gmail.com', '20020002', 'Líder'],
                ['Daniela', 'Reyes', 'daniela.demo02@gmail.com', '20020003', 'Líder'],
                ['Andres', 'Mejia', 'andres.demo02@gmail.com', '20020004', 'Soporte Técnico'],
                ['Paula', 'Herrera', 'paula.demo02@gmail.com', '20020005', 'Call Center'],
                ['Miguel', 'Santos', 'miguel.demo02@gmail.com', '20020006', 'Simpatizante'],
                ['Camila', 'Vega', 'camila.demo02@gmail.com', '20020007', 'Simpatizante'],
                ['Sebastian', 'Nunez', 'sebastian.demo02@gmail.com', '20020008', 'Simpatizante'],
                ['Laura', 'Molina', 'laura.demo02@gmail.com', '20020009', 'Simpatizante'],
                ['Diego', 'Cortes', 'diego.demo02@gmail.com', '20020010', 'Simpatizante'],
            ],
            '03' => [
                ['Mariana', 'Salazar', 'mariana.demo03@gmail.com', '20030001', 'Coordinador Campaña'],
                ['Esteban', 'Pardo', 'esteban.demo03@gmail.com', '20030002', 'Líder'],
                ['Sofia', 'Acosta', 'sofia.demo03@gmail.com', '20030003', 'Líder'],
                ['Javier', 'Rincon', 'javier.demo03@gmail.com', '20030004', 'Soporte Técnico'],
                ['Carolina', 'Fuentes', 'carolina.demo03@gmail.com', '20030005', 'Call Center'],
                ['Ricardo', 'Leon', 'ricardo.demo03@gmail.com', '20030006', 'Simpatizante'],
                ['Patricia', 'Arias', 'patricia.demo03@gmail.com', '20030007', 'Simpatizante'],
                ['Oscar', 'Suarez', 'oscar.demo03@gmail.com', '20030008', 'Simpatizante'],
                ['Monica', 'Peña', 'monica.demo03@gmail.com', '20030009', 'Simpatizante'],
                ['Kevin', 'Ibarra', 'kevin.demo03@gmail.com', '20030010', 'Simpatizante'],
            ],
        ];

        foreach ($campaigns as $campaign) {
            foreach ($peopleByCampaign[$campaign->code] ?? [] as $person) {
                [$firstName, $surname, $email, $document, $roleName] = $person;

                $user = User::query()->updateOrCreate(
                    ['email' => $email],
                    [
                        'document_type_id' => 6,
                        'document_number' => $document,
                        'first_name' => $firstName,
                        'paternal_surname' => $surname,
                        'celphone' => '300'.str_pad((string) random_int(1000000, 9999999), 7, '0'),
                        'current_campaign' => $campaign->code,
                        'is_super_admin' => false,
                        'platform_role' => $this->platformRoleFor($roleName),
                        'email_verified_at' => now(),
                    ]
                );

                $campaign->foreign_users()->syncWithoutDetaching([
                    $user->id => [
                        'reffer_by' => null,
                        'approach' => 4,
                        'validate' => 1,
                    ],
                ]);

                if (in_array($roleName, ['Coordinador Campaña', 'Soporte Técnico', 'Call Center'], true)) {
                    $campaign->staff_users()->syncWithoutDetaching([
                        $user->id => [
                            'role' => $this->staffRoleFor($roleName),
                            'status' => true,
                        ],
                    ]);
                }

                $role = Role::query()
                    ->where('campaign_id', $campaign->id)
                    ->where('guard_name', 'web')
                    ->where('name', $roleName)
                    ->first();

                if ($role) {
                    DB::table(config('permission.table_names.model_has_roles', 'model_has_roles'))->updateOrInsert([
                        'role_id' => $role->id,
                        'model_type' => User::class,
                        'model_id' => $user->id,
                        'campaign_id' => $campaign->id,
                    ], []);
                }

                $this->ensureProfile($user);
            }
        }
    }

    protected function ensureProfile(User $user): void
    {
        DB::table('user_profiles')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'gender_id' => 5,
                'occupation_id' => 13,
                'vehicle' => 0,
                'age_range_id' => 5,
                'zone' => 'urbana',
                'department' => json_encode(['id' => 18, 'name' => 'Huila']),
                'municipality' => json_encode(['id' => 657, 'name' => 'Neiva']),
                'neighborhood_village_name' => 'Centro',
                'latitude' => 2.92730000,
                'longitude' => -75.28190000,
                'current_location' => 'Neiva, Huila, Colombia',
                'birth_date' => '1990-01-01',
                'birth_day' => 1,
                'birth_month' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    protected function platformRoleFor(string $roleName): string
    {
        return match ($roleName) {
            'Coordinador Campaña' => User::ROLE_CAMPAIGN_MANAGER,
            'Soporte Técnico' => User::ROLE_TECH_SUPPORT,
            'Call Center' => User::ROLE_CALL_CENTER,
            default => User::ROLE_SUPPORTER,
        };
    }

    protected function staffRoleFor(string $roleName): string
    {
        return match ($roleName) {
            'Soporte Técnico' => 'support',
            'Call Center' => 'call_center',
            default => 'coordinator',
        };
    }
}
