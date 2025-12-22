<div>
    @if ($mode === 'desktop')
        <div class="hide-mobile">
            <select class="min-w-3xs" wire:change="selectCampaign($event.target.value)" wire:model='campaign_selected'>
                <option value="" hidden="">Seleccione</option>
                @foreach ($campaigns as $campaign)
                    <option value="{{ $campaign->code }}"> {{ $campaign->name }}</option>
                @endforeach
            </select>
        </div>
    @endif
    @if ($mode === 'mobile')
        <select class="hide-desktop"  wire:change="selectCampaign($event.target.value)" wire:model='campaign_selected'>
            <option value="" hidden="">Seleccione</option>
            @foreach ($campaigns as $campaign)
                <option value="{{ $campaign->code }}"> {{ $campaign->name }}</option>
            @endforeach
        </select>
    @endif
</div>
