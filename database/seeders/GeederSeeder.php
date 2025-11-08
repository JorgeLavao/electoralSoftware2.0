<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GeederSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $genders = [
            ['code' => 'M',   'name' => 'Masculino'],
            ['code' => 'F',   'name' => 'Femenino'],
            ['code' => 'NB',  'name' => 'No binario'],
            ['code' => 'OT',  'name' => 'Otro'],
            ['code' => 'PND', 'name' => 'Prefiero no decirlo'],
        ];

        DB::table('genders')->upsert(
            $genders,
            ['code'],
            ['name']
        );
    }
}
