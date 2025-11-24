<section class="dashboard__main__section">
    <div class="breadcrumbs">
        Campañas
    </div>
    <article class="dashboard__main__section__article">
        <div class="flex justify-end">
            <button type="button" class="btn-primary" wire:click='addCampaign'><x-icons.add-fill/> Agregar Campaña</button>
        </div>
        @if (session()->has('success'))
            <x-toast.success-toast :message="session('success')"/>
        @endif
        <ul class="list-horizontal wrap-primary">
            @foreach ($campaigns as $campaign)
                <li>
                    <a href="javascript:void(0)">
                        <h3>{{ $campaign->name }}</h3>
                        <h4 class="mt-2">{{$campaign->position}}</h4>
                        <hr>
                        <p>Coordinadores</p>
                        @foreach ($campaign->foreign_users as $user)
                            <h5>{{ $user->fullName }}</h5>
                        @endforeach
                    </a>
                    <div class="container-h">
                        <a href="ererer" class="button btn-secundary">
                            ingresar <x-icons.right-fill/>
                        </a>
                        <button type="button" class="btn-secundary" wire:click='editCampaign({{ $campaign->id }})'>
                            <x-icons.edit-2-fill/>
                        </button>
                    </div>
                </li>
            @endforeach
        </ul>
        <div class="">
           {{ $campaigns->links() }}
        </div>
    </article>
    <livewire:campaign.add-campaign-modal/>
    <livewire:campaign.edit-campaign-modal/>
</section>
