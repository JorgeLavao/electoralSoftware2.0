<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SupporterListQueryService
{
    public function build(Campaign $campaign, Campaign $rolesCampaign, array $filters)
    {
        $query = $campaign->foreign_users()
            ->with([
                'foreing_aditional_info.foreign_gender',
                'foreing_aditional_info.foreign_range_age',
                'foreing_aditional_info.foreign_occupations',
                'committees' => fn ($committeeQuery) => $committeeQuery
                    ->where('committees.campaign_id', $campaign->id)
                    ->orderBy('name'),
            ])
            ->select('users.*');

        $this->applySearchFilter($query, $filters);
        $this->applyProfilePhotoFilter($query, $filters);
        $this->applyProfileFilters($query, $filters);
        $this->applyCampaignPivotFilters($query, $filters);
        $this->applyRelationshipFilters($query, $campaign, $rolesCampaign, $filters);

        return $query->orderBy('first_name')->orderBy('paternal_surname');
    }

    private function applySearchFilter($query, array $filters): void
    {
        $term = trim((string) ($filters['searchTerm'] ?? ''));

        if ($term === '') {
            return;
        }

        ($filters['sw_search'] ?? false)
            ? $query->whereNot(fn ($searchQuery) => $searchQuery->search($term))
            : $query->search($term);
    }

    private function applyProfilePhotoFilter($query, array $filters): void
    {
        $value = $filters['profile_photo_filter'] ?? null;

        if (! in_array($value, ['with', 'without'], true)) {
            return;
        }

        $query->where(function ($photoQuery) use ($value) {
            if ($value === 'with') {
                $photoQuery
                    ->where(function ($localQuery) {
                        $localQuery
                            ->whereNotNull('users.profile_photo_path')
                            ->where('users.profile_photo_path', '!=', '');
                    })
                    ->orWhere(function ($googleQuery) {
                        $googleQuery
                            ->whereNotNull('users.google_avatar')
                            ->where('users.google_avatar', '!=', '');
                    });

                return;
            }

            $photoQuery
                ->where(function ($localQuery) {
                    $localQuery
                        ->whereNull('users.profile_photo_path')
                        ->orWhere('users.profile_photo_path', '');
                })
                ->where(function ($googleQuery) {
                    $googleQuery
                        ->whereNull('users.google_avatar')
                        ->orWhere('users.google_avatar', '');
                });
        });
    }

    private function applyProfileFilters($query, array $filters): void
    {
        $this->applyWhereHasFilter($query, 'foreing_aditional_info', 'gender_id', $filters['gender_id'] ?? null, $filters['sw_gender'] ?? false, true);
        $this->applyWhereHasFilter($query, 'foreing_aditional_info', 'age_range_id', $filters['age_range'] ?? null, $filters['sw_age'] ?? false);
        $this->applyWhereHasFilter($query, 'foreing_aditional_info', 'occupation_id', $filters['occupation_id'] ?? null, $filters['sw_occupation'] ?? false);
        $this->applyWhereHasFilter($query, 'foreing_aditional_info', 'zone', $filters['zone'] ?? null, $filters['sw_zone'] ?? false);
        $this->applyWhereHasFilter($query, 'foreing_aditional_info', 'department->id', $filters['department'] ?? null, $filters['sw_department'] ?? false);
        $this->applyWhereHasFilter($query, 'foreing_aditional_info', 'municipality->id', $filters['municipality'] ?? null, $filters['sw_municipality'] ?? false);
        $this->applyWhereHasFilter($query, 'foreing_aditional_info', 'district_commune', $filters['district_commune'] ?? null, $filters['sw_district'] ?? false);
        $this->applyWhereHasFilter($query, 'foreing_aditional_info', 'neighborhood_village_name', $filters['neighborhood'] ?? null, $filters['sw_nghd'] ?? false);
        $this->applyWhereHasFilter($query, 'foreing_aditional_info', 'vehicle', $filters['vehicle'] ?? null, $filters['sw_vehicle'] ?? false);

        if (($filters['birth_month'] ?? null) || ($filters['birth_day'] ?? null)) {
            $callback = function ($q) use ($filters) {
                if ($filters['birth_month'] ?? null) {
                    $q->where('birth_month', (int) $filters['birth_month']);
                }

                if ($filters['birth_day'] ?? null) {
                    $q->where('birth_day', (int) $filters['birth_day']);
                }
            };

            ($filters['sw_birth'] ?? false)
                ? $query->whereDoesntHave('foreing_aditional_info', $callback)
                : $query->whereHas('foreing_aditional_info', $callback);
        }
    }

    private function applyWhereHasFilter($query, string $relation, string $column, $value, bool $exclude = false, bool $castInt = false): void
    {
        if (is_null($value) || $value === '') {
            return;
        }

        $filterValue = $castInt ? (int) $value : $value;
        $callback = fn ($q) => $q->where($column, $filterValue);

        $exclude
            ? $query->whereDoesntHave($relation, $callback)
            : $query->whereHas($relation, $callback);
    }

    private function applyCampaignPivotFilters($query, array $filters): void
    {
        if (! is_null($filters['approach'] ?? null) && $filters['approach'] !== '') {
            ($filters['sw_approach'] ?? false)
                ? $query->wherePivot('approach', '!=', $filters['approach'])
                : $query->wherePivot('approach', $filters['approach']);
        }

        if (! is_null($filters['verify'] ?? null) && $filters['verify'] !== '') {
            ($filters['sw_verify'] ?? false)
                ? $query->wherePivot('validate', '!=', $filters['verify'])
                : $query->wherePivot('validate', $filters['verify']);
        }

        if (! empty($filters['refer_ids'] ?? [])) {
            $referIds = collect($filters['refer_ids'])
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values()
                ->all();

            if ($referIds !== []) {
                ($filters['sw_refers'] ?? false)
                    ? $query->wherePivotNotIn('reffer_by', $referIds)
                    : $query->wherePivotIn('reffer_by', $referIds);
            }
        }

        $this->applyJoinedDateFilters($query, $filters);
        $this->applyValidationDateFilters($query, $filters);
    }

    private function applyJoinedDateFilters($query, array $filters): void
    {
        $joinedFrom = $filters['joined_from'] ?? null;
        $joinedTo = $filters['joined_to'] ?? null;
        $exclude = $filters['sw_joined'] ?? false;

        if ($joinedFrom && $joinedTo) {
            $dates = [
                Carbon::parse($joinedFrom)->startOfDay(),
                Carbon::parse($joinedTo)->endOfDay(),
            ];

            $exclude
                ? $query->whereNotBetween('campaign_user.created_at', $dates)
                : $query->whereBetween('campaign_user.created_at', $dates);
        } elseif ($joinedFrom) {
            $query->where('campaign_user.created_at', $exclude ? '<' : '>=', Carbon::parse($joinedFrom)->startOfDay());
        } elseif ($joinedTo) {
            $query->where('campaign_user.created_at', $exclude ? '>' : '<=', Carbon::parse($joinedTo)->endOfDay());
        }
    }

    private function applyValidationDateFilters($query, array $filters): void
    {
        $validationFrom = $filters['validation_from'] ?? null;
        $validationTo = $filters['validation_to'] ?? null;
        $exclude = $filters['sw_validation'] ?? false;

        if (! $validationFrom && ! $validationTo) {
            return;
        }

        if ($validationFrom && $validationTo) {
            $dates = [
                Carbon::parse($validationFrom)->startOfDay(),
                Carbon::parse($validationTo)->endOfDay(),
            ];

            $exclude
                ? $query->where(fn ($validationQuery) => $validationQuery
                    ->where('campaign_user.validate', '!=', 1)
                    ->orWhereNotBetween('campaign_user.updated_at', $dates))
                : $query->wherePivot('validate', 1)->whereBetween('campaign_user.updated_at', $dates);

            return;
        }

        if ($validationFrom) {
            $date = Carbon::parse($validationFrom)->startOfDay();

            $exclude
                ? $query->where(fn ($validationQuery) => $validationQuery
                    ->where('campaign_user.validate', '!=', 1)
                    ->orWhere('campaign_user.updated_at', '<', $date))
                : $query->wherePivot('validate', 1)->where('campaign_user.updated_at', '>=', $date);

            return;
        }

        $date = Carbon::parse($validationTo)->endOfDay();

        $exclude
            ? $query->where(fn ($validationQuery) => $validationQuery
                ->where('campaign_user.validate', '!=', 1)
                ->orWhere('campaign_user.updated_at', '>', $date))
            : $query->wherePivot('validate', 1)->where('campaign_user.updated_at', '<=', $date);
    }

    private function applyRelationshipFilters($query, Campaign $campaign, Campaign $rolesCampaign, array $filters): void
    {
        if (! empty($filters['committee_ids'] ?? [])) {
            ($filters['sw_committees'] ?? false)
                ? $query->whereDoesntHave('committees', function ($committeeQuery) use ($campaign, $filters) {
                    $committeeQuery
                        ->where('committees.campaign_id', $campaign->id)
                        ->whereIn('committees.id', $filters['committee_ids']);
                })
                : $query->whereHas('committees', function ($committeeQuery) use ($campaign, $filters) {
                    $committeeQuery
                        ->where('committees.campaign_id', $campaign->id)
                        ->whereIn('committees.id', $filters['committee_ids']);
                });
        }

        if (! empty($filters['role_ids'] ?? [])) {
            $roleUserIds = $this->roleUserIdsSubquery($rolesCampaign, $filters['role_ids']);

            ($filters['sw_roles'] ?? false)
                ? $query->whereNotIn('users.id', $roleUserIds)
                : $query->whereIn('users.id', $roleUserIds);
        }
    }

    private function roleUserIdsSubquery(Campaign $campaign, array $roleIds)
    {
        return DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', User::class)
            ->where('model_has_roles.campaign_id', $campaign->id)
            ->where('roles.campaign_id', $campaign->id)
            ->whereIn('roles.id', $roleIds)
            ->select('model_has_roles.model_id');
    }
}
