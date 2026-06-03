<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SupporterRowMapper
{
    public function roleNamesByUser(Campaign $campaign, Collection $users): array
    {
        $userIds = $users->pluck('id')->filter()->unique()->values();

        if ($userIds->isEmpty()) {
            return [];
        }

        return DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', User::class)
            ->where('model_has_roles.campaign_id', $campaign->id)
            ->where('roles.campaign_id', $campaign->id)
            ->whereIn('model_has_roles.model_id', $userIds)
            ->orderBy('roles.name')
            ->get(['model_has_roles.model_id', 'roles.name'])
            ->groupBy('model_id')
            ->map(fn($rows) => $rows->pluck('name')->filter()->unique()->implode(', '))
            ->all();
    }

    public function referrerNamesByUser(Campaign $campaign, Collection $users): array
    {
        $userIds = $users->pluck('id')->filter()->unique()->values();

        if ($userIds->isEmpty()) {
            return [];
        }

        return DB::table('campaign_user')
            ->join('users as referrers', 'referrers.id', '=', 'campaign_user.reffer_by')
            ->where('campaign_user.campaign_id', $campaign->id)
            ->whereIn('campaign_user.user_id', $userIds)
            ->get([
                'campaign_user.user_id',
                'referrers.first_name',
                'referrers.middle_name',
                'referrers.paternal_surname',
                'referrers.maternal_surname',
            ])
            ->mapWithKeys(fn ($row) => [
                $row->user_id => collect([
                    $row->first_name,
                    $row->middle_name,
                    $row->paternal_surname,
                    $row->maternal_surname,
                ])->filter()->implode(' '),
            ])
            ->all();
    }

    public function referrerIdsByUser(Campaign $campaign, Collection $users): array
    {
        $userIds = $users->pluck('id')->filter()->unique()->values();

        if ($userIds->isEmpty()) {
            return [];
        }

        return DB::table('campaign_user')
            ->where('campaign_id', $campaign->id)
            ->whereIn('user_id', $userIds)
            ->whereNotNull('reffer_by')
            ->pluck('reffer_by', 'user_id')
            ->all();
    }

    public function referralCountsByUser(Campaign $campaign, Collection $users): array
    {
        $userIds = $users->pluck('id')->filter()->unique()->values();

        if ($userIds->isEmpty()) {
            return [];
        }

        return DB::table('campaign_user')
            ->where('campaign_id', $campaign->id)
            ->whereIn('reffer_by', $userIds)
            ->select('reffer_by', DB::raw('count(*) as total'))
            ->groupBy('reffer_by')
            ->pluck('total', 'reffer_by')
            ->all();
    }

    public function map(
        User $user,
        array $roleNamesByUser = [],
        array $referrerNamesByUser = [],
        array $referralCountsByUser = [],
        array $referrerIdsByUser = []
    ): array
    {
        $profile = $user->foreing_aditional_info;
        $department = $profile?->department ? json_decode($profile->department, true) : null;
        $municipality = $profile?->municipality ? json_decode($profile->municipality, true) : null;
        $committeeNames = $user->committees
            ->pluck('name')
            ->filter()
            ->implode(', ');
        $roleNames = $roleNamesByUser[$user->id] ?? $user->roles->pluck('name')->filter()->unique()->implode(', ');
        $joinedAt = $user->pivot?->created_at
            ? Carbon::parse($user->pivot->created_at)->format('Y-m-d H:i')
            : '-';
        $birthMonth = $profile?->birth_month;
        $birthDay = $profile?->birth_day;

        return [
            'id' => $user->id,
            'profile_photo' => $user->hasProfilePhoto() ? '' : '',
            'profile_photo_url' => $user->profilePhotoUrl(),
            'profile_initials' => $user->initials(),
            'document_number' => $user->document_number ?: '-',
            'first_name' => $user->first_name ?: '-',
            'middle_name' => $user->middle_name ?: '-',
            'paternal_surname' => $user->paternal_surname ?: '-',
            'maternal_surname' => $user->maternal_surname ?: '-',
            'full_name' => $user->fullName ?: '-',
            'celphone' => $user->celphone ?: '-',
            'email' => $user->email ?: '-',
            'validate' => (string) $user->pivot->validate === '1' ? 'Si' : 'No',
            'approach' => $user->pivot->approach ?: '-',
            'vehicle' => $profile ? ($profile->vehicle ? 'Si' : 'No') : '-',
            'gender' => $profile?->foreign_gender?->name ?: '-',
            'birth_month' => $birthMonth ? str_pad((string) $birthMonth, 2, '0', STR_PAD_LEFT) : '-',
            'birth_day' => $birthDay ? str_pad((string) $birthDay, 2, '0', STR_PAD_LEFT) : '-',
            'age_range' => $profile?->foreign_range_age?->range ?: '-',
            'occupation' => $profile?->foreign_occupations?->name ?: '-',
            'zone' => $profile?->zone ? ucfirst($profile->zone) : '-',
            'department' => data_get($department, 'name', '-'),
            'municipality' => data_get($municipality, 'name', '-'),
            'district_commune' => $profile?->district_commune ?: '-',
            'neighborhood_village_name' => $profile?->neighborhood_village_name ?: '-',
            'committees' => $committeeNames !== '' ? $committeeNames : '-',
            'roles' => $roleNames !== '' ? $roleNames : '-',
            'referred_by' => $referrerNamesByUser[$user->id] ?? '-',
            'referred_by_id' => isset($referrerIdsByUser[$user->id]) ? (int) $referrerIdsByUser[$user->id] : null,
            'referrals_count' => (int) ($referralCountsByUser[$user->id] ?? 0),
            'joined_at' => $joinedAt,
            'validated_at' => (string) $user->pivot->validate === '1' && $user->pivot?->updated_at
                ? Carbon::parse($user->pivot->updated_at)->format('Y-m-d H:i')
                : '-',
        ];
    }

    public function onlyColumns(array $row, array $columns): array
    {
        return collect($columns)
            ->mapWithKeys(fn($column) => [$column => $row[$column] ?? '-'])
            ->all();
    }
}
