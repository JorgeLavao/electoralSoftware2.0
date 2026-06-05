<?php

namespace App\Exports;

use App\Models\Campaign;
use App\Models\ExportBatch;
use App\Services\SupporterListQueryService;
use App\Services\SupporterRowMapper;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class QueuedFilteredUsersExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected ExportBatch $batch;
    protected Campaign $campaign;
    protected SupporterRowMapper $rowMapper;

    public function __construct(protected int $batchId)
    {
        $this->batch = ExportBatch::query()->findOrFail($this->batchId);
        $this->campaign = Campaign::query()->findOrFail($this->batch->campaign_id);
        $this->rowMapper = app(SupporterRowMapper::class);
    }

    public function query()
    {
        $query = app(SupporterListQueryService::class)->build(
            $this->campaign,
            $this->campaign,
            $this->batch->filters ?? []
        );

        if ($this->batch->scope === ExportBatch::SCOPE_CURRENT_PAGE) {
            $query->forPage(max(1, (int) $this->batch->page), max(1, (int) $this->batch->per_page));
        }

        return $query;
    }

    public function map($user): array
    {
        $row = $this->rowMapper->map($user);

        return array_values($this->rowMapper->onlyColumns($row, $this->batch->columns ?? []));
    }

    public function headings(): array
    {
        $labels = $this->columnLabels();

        return collect($this->batch->columns ?? [])
            ->map(fn ($column) => $labels[$column] ?? $column)
            ->all();
    }

    protected function columnLabels(): array
    {
        return [
            'document_number' => 'Cédula',
            'profile_photo' => 'Foto de perfil',
            'first_name' => 'Primer Nombre',
            'middle_name' => 'Segundo Nombre',
            'paternal_surname' => 'Primer Apellido',
            'maternal_surname' => 'Segundo Apellido',
            'full_name' => 'Nombre Completo',
            'celphone' => 'Celular',
            'email' => 'Correo',
            'validate' => 'Validado',
            'approach' => 'Acercamiento',
            'vehicle' => 'Vehiculo',
            'gender' => 'Genero',
            'birth_month' => 'Mes de nacimiento',
            'birth_day' => 'Dia de nacimiento',
            'age_range' => 'Rango de edad',
            'occupation' => 'Profesion',
            'zone' => 'Zona',
            'department' => 'Departamento',
            'municipality' => 'Municipio',
            'district_commune' => 'Comuna',
            'neighborhood_village_name' => 'Barrio',
            'committees' => 'Comites',
            'roles' => 'Roles',
            'referred_by' => 'Quien lo refirio',
            'referrals_count' => 'Cantidad referidos',
            'joined_at' => 'Fecha de ingreso',
            'validated_at' => 'Fecha de validación',
        ];
    }
}
