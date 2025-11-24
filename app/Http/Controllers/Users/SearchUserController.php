<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchUserController extends Controller
{
    public function __invoke(Request $request)
    {
        $search = trim($request->input('q', ''));

        // Evitar consultas costosas cuando la búsqueda está vacía
        if (strlen($search) < 2) {
            return [];
        }

        return User::query()
            ->where(function ($q) use ($search) {
                $normalized = preg_replace('/\s+/', ' ', $search);

                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('paternal_surname', 'like', "%{$search}%")
                  ->orWhere('maternal_surname', 'like', "%{$search}%");
                // Buscar por nombre completo concatenado
                $q->orWhereRaw(
                    "CONCAT_WS(' ', first_name, middle_name, paternal_surname, maternal_surname) LIKE ?",
                    ["%{$normalized}%"]
                );
            })
            ->limit(20)
            ->get([
                'id',
                // Texto de salida compatible con TomSelect u otros selectores
                DB::raw("CONCAT_WS(' ', first_name, middle_name, paternal_surname, maternal_surname) AS text")
            ]);
    }
}
