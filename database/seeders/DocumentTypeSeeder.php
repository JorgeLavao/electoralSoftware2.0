<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $documents = [
            ['code' => 'CC',  'name' => 'Cédula de Ciudadanía'],
            ['code' => 'TI',  'name' => 'Tarjeta de Identidad'],
            ['code' => 'CE',  'name' => 'Cédula de Extranjería'],
            ['code' => 'PA',  'name' => 'Pasaporte'],
            ['code' => 'RC',  'name' => 'Registro Civil'],
            ['code' => 'NIT', 'name' => 'Número de Identificación Tributaria'],
            ['code' => 'PEP', 'name' => 'Permiso Especial de Permanencia'],
            ['code' => 'PPT', 'name' => 'Permiso por Protección Temporal'],
        ];
        foreach ($documents as $doc) {
            DB::table('document_types')->updateOrInsert(
                ['code' => $doc['code']],
                ['name' => $doc['name']]
            );
        }
    }
}
