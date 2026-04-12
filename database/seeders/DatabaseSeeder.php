<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([DocumentTypeSeeder::class]);
        $this->call([GeederSeeder::class]);
        $this->call(AgeRangeSeeder::class);
        $this->call(OcupationSeeders::class);
        $this->call(RolesAndPermissionSeeder::class);
    }
}
