<section class="dashboard__main__section">
    <div class="breadcrumbs">
        Campañas
    </div>

    <article class="dashboard__main__section__article">
        <div class="flex justify-end">
            @if(Auth::user()->is_super_admin)
            <button type="button" class="btn-primary" wire:click='addCampaign'>
                <x-icons.add-fill /> Agregar Campaña
            </button>
            @endif
        </div>

        @if (session()->has('success'))
        <x-toast.success-toast :message="session('success')" />
        @endif

        <ul class="list-horizontal wrap-primary">
            @foreach ($campaigns as $campaign)
            <li>
                <a href="javascript:void(0)">
                    <h3>{{ $campaign->name }}</h3>
                    <h4 class="mt-2">{{ $campaign->position }}</h4>
                    <hr>

                    <p>Coordinadores</p>

                    {{-- ðŸ”¥ AQUÃ ESTÃ LA CORRECCIÃ“N --}}
                    @forelse ($campaign->staff_users as $staff)
                    <h5>{{ $staff->fullName }}</h5>
                    @empty
                    <h5>Sin coordinadores asignados</h5>
                    @endforelse

                </a>

                <div class="container-h">
                    <a href="javascript:void(0)"
                        class="button btn-secundary"
                        wire:click='getIn_campaign("{{ $campaign->code }}")'>
                        Ingresar <x-icons.right-fill />
                    </a>

                    @if(Auth::user()->is_super_admin)
                    <button type="button"
                        class="btn-secundary"
                        wire:click='editCampaign({{ $campaign->id }})'>
                        <x-icons.edit-2-fill />
                    </button>
                    @endif
                </div>
            </li>
            @endforeach
        </ul>

        <div>
            {{ $campaigns->links() }}
        </div>
    </article>

    <livewire:campaign.add-campaign-modal />
    <livewire:campaign.edit-campaign-modal />
</section>