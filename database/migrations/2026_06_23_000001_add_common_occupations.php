<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        foreach ($this->occupations() as $name) {
            DB::table('occupations')->updateOrInsert(
                ['name' => $name],
                [
                    'status' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('occupations')
            ->whereIn('name', $this->occupations())
            ->delete();
    }

    private function occupations(): array
    {
        return [
            'Administrador de empresas',
            'Administrador de sistemas',
            'Agente de ventas',
            'Ama de casa',
            'Analista contable',
            'Analista de mercadeo',
            'Asesor de call center',
            'Auxiliar administrativo',
            'Auxiliar contable',
            'Auxiliar de bodega',
            'Auxiliar de cocina',
            'Auxiliar logistico',
            'Call Center',
            'Camionero',
            'Community manager',
            'Cuidador',
            'Desempleado',
            'Diseñador UX/UI',
            'Domiciliario',
            'Electricista',
            'Empleada domestica',
            'Emprendedor',
            'Estudiante',
            'Guarda de seguridad',
            'Independiente',
            'Ingeniero de software',
            'Mecanico',
            'Mercaderista',
            'Mototaxista',
            'Ninguna',
            'Otra',
            'Panadero',
            'Pensionado',
            'Programador',
            'Recursos humanos',
            'Servicio al cliente',
            'Soporte tecnico',
            'Tecnico de redes',
            'Transportador',
        ];
    }
};
