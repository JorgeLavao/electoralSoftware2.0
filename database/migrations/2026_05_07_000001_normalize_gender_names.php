<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $femaleId = $this->canonicalGenderId('F', 'Femenino');
        $maleId = $this->canonicalGenderId('M', 'Masculino');

        $this->mergeGenderAliases($femaleId, ['mujer', 'femenino', 'female', 'f']);
        $this->mergeGenderAliases($maleId, ['hombre', 'masculino', 'male', 'm']);

        DB::table('genders')->where('id', $femaleId)->update([
            'code' => 'F',
            'name' => 'Femenino',
            'status' => true,
            'updated_at' => now(),
        ]);

        DB::table('genders')->where('id', $maleId)->update([
            'code' => 'M',
            'name' => 'Masculino',
            'status' => true,
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        //
    }

    private function canonicalGenderId(string $code, string $name): int
    {
        $gender = DB::table('genders')
            ->where('code', $code)
            ->orWhereRaw('LOWER(name) = ?', [strtolower($name)])
            ->first();

        if ($gender) {
            return (int) $gender->id;
        }

        return (int) DB::table('genders')->insertGetId([
            'code' => $code,
            'name' => $name,
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function mergeGenderAliases(int $canonicalId, array $aliases): void
    {
        $aliasIds = DB::table('genders')
            ->whereIn(DB::raw('LOWER(name)'), $aliases)
            ->where('id', '!=', $canonicalId)
            ->pluck('id');

        if ($aliasIds->isEmpty()) {
            return;
        }

        DB::table('user_profiles')
            ->whereIn('gender_id', $aliasIds)
            ->update(['gender_id' => $canonicalId]);

        DB::table('genders')
            ->whereIn('id', $aliasIds)
            ->delete();
    }
};
