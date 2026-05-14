<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OcupationSeeders extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $occupations = [
            // Sector Primario (Agricultura, ganadería, pesca)
            ['name' => 'Agricultor', 'status' => 1],
            ['name' => 'Ganadero', 'status' => 1],
            ['name' => 'Pescador', 'status' => 1],
            ['name' => 'Trabajador agrícola', 'status' => 1],
            ['name' => 'Ingeniero agrónomo', 'status' => 1],

            // Sector Secundario (Industria, manufactura, construcción)
            ['name' => 'Obrero de construcción', 'status' => 1],
            ['name' => 'Operario de fábrica', 'status' => 1],
            ['name' => 'Soldador', 'status' => 1],
            ['name' => 'Carpintero', 'status' => 1],
            ['name' => 'Ingeniero civil', 'status' => 1],
            ['name' => 'Ingeniero industrial', 'status' => 1],
            ['name' => 'Supervisor de producción', 'status' => 1],
            ['name' => 'Técnico en mantenimiento', 'status' => 1],

            // Sector Terciario (Servicios)
            ['name' => 'Comerciante', 'status' => 1],
            ['name' => 'Vendedor', 'status' => 1],
            ['name' => 'Cajero', 'status' => 1],
            ['name' => 'Dependiente de almacén', 'status' => 1],
            ['name' => 'Asesor comercial', 'status' => 1],

            // Transporte y logística
            ['name' => 'Conductor', 'status' => 1],
            ['name' => 'Taxista', 'status' => 1],
            ['name' => 'Repartidor', 'status' => 1],
            ['name' => 'Operador logístico', 'status' => 1],
            ['name' => 'Mensajero', 'status' => 1],

            // Salud
            ['name' => 'Médico', 'status' => 1],
            ['name' => 'Enfermero', 'status' => 1],
            ['name' => 'Auxiliar de enfermería', 'status' => 1],
            ['name' => 'Odontólogo', 'status' => 1],
            ['name' => 'Farmacéutico', 'status' => 1],
            ['name' => 'Técnico en salud', 'status' => 1],

            // Educación
            ['name' => 'Profesor', 'status' => 1],
            ['name' => 'Maestro', 'status' => 1],
            ['name' => 'Rector', 'status' => 1],
            ['name' => 'Coordinador académico', 'status' => 1],
            ['name' => 'Investigador', 'status' => 1],

            // Tecnología e Informática
            ['name' => 'Desarrollador de software', 'status' => 1],
            ['name' => 'Ingeniero de sistemas', 'status' => 1],
            ['name' => 'Analista de datos', 'status' => 1],
            ['name' => 'Técnico en informática', 'status' => 1],
            ['name' => 'Diseñador web', 'status' => 1],
            ['name' => 'Especialista en ciberseguridad', 'status' => 1],

            // Administración y finanzas
            ['name' => 'Contador', 'status' => 1],
            ['name' => 'Administrador', 'status' => 1],
            ['name' => 'Gerente', 'status' => 1],
            ['name' => 'Asistente administrativo', 'status' => 1],
            ['name' => 'Auditor', 'status' => 1],
            ['name' => 'Analista financiero', 'status' => 1],

            // Turismo y hotelería
            ['name' => 'Hotelero', 'status' => 1],
            ['name' => 'Guía turístico', 'status' => 1],
            ['name' => 'Recepcionista', 'status' => 1],
            ['name' => 'Camarero/Mesero', 'status' => 1],
            ['name' => 'Chef', 'status' => 1],
            ['name' => 'Cocinero', 'status' => 1],

            // Servicios personales y domésticos
            ['name' => 'Peluquero', 'status' => 1],
            ['name' => 'Barbero', 'status' => 1],
            ['name' => 'Esteticista', 'status' => 1],
            ['name' => 'Trabajador doméstico', 'status' => 1],
            ['name' => 'Niñera', 'status' => 1],

            // Seguridad y defensa
            ['name' => 'Policía', 'status' => 1],
            ['name' => 'Militar', 'status' => 1],
            ['name' => 'Guardia de seguridad', 'status' => 1],
            ['name' => 'Vigilante', 'status' => 1],

            // Artes y entretenimiento
            ['name' => 'Artista', 'status' => 1],
            ['name' => 'Músico', 'status' => 1],
            ['name' => 'Actor', 'status' => 1],
            ['name' => 'Diseñador gráfico', 'status' => 1],
            ['name' => 'Fotógrafo', 'status' => 1],

            // Otras profesiones importantes
            ['name' => 'Abogado', 'status' => 1],
            ['name' => 'Arquitecto', 'status' => 1],
            ['name' => 'Periodista', 'status' => 1],
            ['name' => 'Psicólogo', 'status' => 1],
            ['name' => 'Ingeniero ambiental', 'status' => 1],
            ['name' => 'Bombero', 'status' => 1],
            ['name' => 'Paramédico', 'status' => 1],
        ];

        $now = Carbon::now();

        // Insertar las ocupaciones en la base de datos
        foreach ($occupations as $occupation) {
            DB::table('occupations')->insert([
                'name' => $occupation['name'],
                'status' => $occupation['status'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
