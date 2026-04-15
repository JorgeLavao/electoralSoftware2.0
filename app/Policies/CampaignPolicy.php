<?php

namespace App\Policies;

use App\Models\Campaign;
use App\Models\User;

class CampaignPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Campaign $campaign): bool
    {
        return $user->hasPlatformPermission('platform.campaign.view-all')
            || $user->belongsToCampaign($campaign);
    }

    public function create(User $user): bool
    {
        return $user->hasPlatformPermission('platform.campaign.create');
    }

    public function update(User $user, Campaign $campaign): bool
    {
        return $user->hasPlatformPermission('platform.campaign.update');
    }

    public function referSupporters(User $user, Campaign $campaign): bool
    {
        return $this->view($user, $campaign)
            && $user->hasCampaignPermission('campaign.supporters.refer', $campaign);
    }

    public function viewSupporters(User $user, Campaign $campaign): bool
    {
        return $this->view($user, $campaign)
            && $user->hasCampaignPermission('campaign.supporters.view', $campaign);
    }

    public function importSupporters(User $user, Campaign $campaign): bool
    {
        return $this->view($user, $campaign)
            && $user->hasCampaignPermission('campaign.supporters.import', $campaign);
    }

    public function validateSupporters(User $user, Campaign $campaign): bool
    {
        return $this->view($user, $campaign)
            && $user->hasCampaignPermission('campaign.supporters.validate', $campaign);
    }

    public function removeSupporters(User $user, Campaign $campaign): bool
    {
        return $this->view($user, $campaign)
            && $user->hasCampaignPermission('campaign.supporters.remove', $campaign);
    }

    public function viewLists(User $user, Campaign $campaign): bool
    {
        return $this->view($user, $campaign)
            && $user->hasCampaignPermission('campaign.lists.view', $campaign);
    }

    public function createLists(User $user, Campaign $campaign): bool
    {
        return $this->view($user, $campaign)
            && $user->hasCampaignPermission('campaign.lists.create', $campaign);
    }

    public function updateLists(User $user, Campaign $campaign): bool
    {
        return $this->view($user, $campaign)
            && $user->hasCampaignPermission('campaign.lists.update', $campaign);
    }

    public function deleteLists(User $user, Campaign $campaign): bool
    {
        return $this->view($user, $campaign)
            && $user->hasCampaignPermission('campaign.lists.delete', $campaign);
    }

    public function exportLists(User $user, Campaign $campaign): bool
    {
        return $this->view($user, $campaign)
            && $user->hasCampaignPermission('campaign.lists.export', $campaign);
    }

    public function removeCampaignMembers(User $user, Campaign $campaign): bool
    {
        return $this->view($user, $campaign)
            && $user->hasCampaignPermission('campaign.members.remove', $campaign);
    }

    public function viewVotationPoint(User $user, Campaign $campaign): bool
    {
        return $this->view($user, $campaign)
            && $user->hasCampaignPermission('campaign.votation-point.view', $campaign);
    }

    public function manageVotationPoint(User $user, Campaign $campaign): bool
    {
        return $this->view($user, $campaign)
            && $user->hasCampaignPermission('campaign.votation-point.manage', $campaign);
    }
}
