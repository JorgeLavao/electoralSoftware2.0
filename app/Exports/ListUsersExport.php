<?php

namespace App\Exports;

use App\Models\CampaignList;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ListUsersExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(
        protected CampaignList $list
    ) {}

    public function collection()
    {
        return $this->list->foreign_users()
            ->select('users.document_number', 'users.first_name', 'users.middle_name', 'users.paternal_surname', 'users.maternal_surname', 'users.celphone')
            ->get()
            ->map(function ($user) {
                return [
                    'documento' => $user->document_number,
                    'nombre_completo' => trim(implode(' ', array_filter([
                        $user->first_name,
                        $user->middle_name,
                        $user->paternal_surname,
                        $user->maternal_surname,
                    ]))),
                    'celular' => $user->celphone,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Documento',
            'Nombre completo',
            'Celular',
        ];
    }
}
