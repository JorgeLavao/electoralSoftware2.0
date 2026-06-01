<?php

namespace App\Services;

use App\Models\Campaign;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CampaignLocationOptions
{
    public function departments(Campaign $campaign): array
    {
        return $this->profileRows($campaign, ['user_profiles.department'])
            ->pluck('department')
            ->map(fn ($value) => $this->decodeLocation($value))
            ->filter(fn ($value) => is_array($value) && filled($value['id'] ?? null))
            ->unique('id')
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    public function municipalities(Campaign $campaign, $departmentId): array
    {
        if (! filled($departmentId)) {
            return [];
        }

        return $this->profileRows($campaign, ['user_profiles.department', 'user_profiles.municipality'])
            ->filter(fn ($row) => data_get($this->decodeLocation($row->department), 'id') == $departmentId)
            ->pluck('municipality')
            ->map(fn ($value) => $this->decodeLocation($value))
            ->filter(fn ($value) => is_array($value) && filled($value['id'] ?? null))
            ->unique('id')
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    public function districts(Campaign $campaign, $departmentId = null, $municipalityId = null): array
    {
        return $this->profileRows($campaign, [
            'user_profiles.department',
            'user_profiles.municipality',
            'user_profiles.district_commune',
        ])
            ->filter(fn ($row) => ! filled($departmentId) || data_get($this->decodeLocation($row->department), 'id') == $departmentId)
            ->filter(fn ($row) => ! filled($municipalityId) || data_get($this->decodeLocation($row->municipality), 'id') == $municipalityId)
            ->pluck('district_commune')
            ->filter()
            ->unique()
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    public function neighborhoods(Campaign $campaign, $departmentId = null, $municipalityId = null, $districtCommune = null): array
    {
        return $this->profileRows($campaign, [
            'user_profiles.department',
            'user_profiles.municipality',
            'user_profiles.district_commune',
            'user_profiles.neighborhood_village_name',
        ])
            ->filter(fn ($row) => ! filled($departmentId) || data_get($this->decodeLocation($row->department), 'id') == $departmentId)
            ->filter(fn ($row) => ! filled($municipalityId) || data_get($this->decodeLocation($row->municipality), 'id') == $municipalityId)
            ->filter(fn ($row) => ! filled($districtCommune) || $row->district_commune === $districtCommune)
            ->pluck('neighborhood_village_name')
            ->filter()
            ->unique()
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    private function profileRows(Campaign $campaign, array $columns): Collection
    {
        return DB::table('campaign_user')
            ->join('user_profiles', 'user_profiles.user_id', '=', 'campaign_user.user_id')
            ->where('campaign_user.campaign_id', $campaign->id)
            ->where('campaign_user.validate', '!=', 2)
            ->select($columns)
            ->get();
    }

    private function decodeLocation(?string $value): ?array
    {
        if (! $value) {
            return null;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : null;
    }
}
