<section class="dashboard__main__section">
    <div class="breadcrumbs">
        Campañas
    </div>

    <article class="dashboard__main__section__article">
        <div class="flex justify-end">
            @can('create', App\Models\Campaign::class)
                <button type="button" class="btn-primary" wire:click="addCampaign">
                    <x-icons.add-fill/> Agregar Campaña
                </button>
            @endcan
        </div>

        @if (session()->has('success'))
            <x-toast.success-toast :message="session('success')"/>
        @endif

        <ul class="">
            @forelse ($campaigns as $campaign)
                <li>
                    <div>
                        <h3>{{ $campaign->name }}</h3>
                        <h4 class="mt-2">{{ $campaign->position }}</h4>
                        <hr>
                        <p>Coordinadores</p>

                        @forelse ($campaign->staff_users ?? [] as $user)
                            <h5>{{ $user->fullName }}</h5>
                        @empty
                            <h5 class="text-gray-400">Sin coordinadores</h5>
                        @endforelse
                    </div>

                    <div class="container-h">
                        @can('view', $campaign)
                            <button type="button" wire:click='getIn_campaign("{{ $campaign->code }}")'>
                                Ingresar <x-icons.right-fill/>
                            </button>
                        @endcan

                        @can('update', $campaign)
                            <button
                                type="button"
                                class="btn-secundary"
                                wire:click="editCampaign({{ $campaign->id }})"
                            >
                                <x-icons.edit-2-fill/>
                            </button>
                        @endcan
                    </div>
                </li>
            @empty
                <li>
                    <p class="text-center text-gray-400">
                        No hay campañas registradas
                    </p>
                </li>
            @endforelse
        </ul>

        <div>
            {{ $campaigns->links() }}
        </div>
    </article>

    <livewire:campaign.add-campaign-modal />
    <livewire:campaign.edit-campaign-modal />
</section>
