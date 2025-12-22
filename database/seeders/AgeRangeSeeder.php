<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AgeRangeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('age_ranges')->insert([
            ['id' => 1, 'range' => '18 - 24'],
            ['id' => 2, 'range' => '25 - 34'],
            ['id' => 3, 'range' => '35 - 44'],
            ['id' => 4, 'range' => '45 - 54'],
            ['id' => 5, 'range' => '55 - 64'],
            ['id' => 6, 'range' => '65+'],
        ]);
    }
}
