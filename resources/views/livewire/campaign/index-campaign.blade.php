<section
    class="dashboard__main__section"
    x-data="{ joinModal: @js($errors->has('campaign_code')) }"
    x-on:campaign-joined.window="joinModal = false">
    <div class="breadcrumbs">
        Campañas
    </div>

    <article class="dashboard__main__section__article">
        <div class="flex justify-end">
            @if(Auth::user()->is_super_admin)
            <button type="button" class="btn-primary" wire:click='addCampaign'>
                <x-icons.add-fill /> Agregar Campaña
            </button>
            @else
            <button type="button" class="btn-primary" wire:click="resetJoinCampaignForm" @click="joinModal = true">
                Unirme a Campaña
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

                    @if(Auth::user()->is_super_admin)
                    <h5 class="mt-2">Codigo: {{ $campaign->code }}</h5>
                    @endif

                    <hr>

                    <p>Coordinadores</p>

                    @forelse ($campaign->staff_users as $staff)
                    <h5>{{ $staff->fullName }}</h5>
                    @empty
                    <h5>Sin coordinadores asignados</h5>
                    @endforelse
                </a>

                <div class="container-h">
                    @if(Auth::user()->is_super_admin)
                    <a href="javascript:void(0)"
                        class="button btn-secundary"
                        wire:click='getIn_campaign("{{ $campaign->code }}")'>
                        Ingresar <x-icons.right-fill />
                    </a>

                    <button type="button"
                        class="btn-secundary"
                        wire:click='editCampaign({{ $campaign->id }})'>
                        <x-icons.edit-2-fill />
                    </button>
                    @elseif(in_array($campaign->id, $supporterCampaignIds, true))
                    <a href="javascript:void(0)"
                        class="button btn-secundary"
                        wire:click='getIn_campaign("{{ $campaign->code }}")'>
                        Ingresar <x-icons.right-fill />
                    </a>

                    <button type="button"
                        class="btn-secundary"
                        title="Abandonar campana"
                        aria-label="Abandonar campana"
                        wire:click='leaveCampaign({{ $campaign->id }})'>
                        <x-icons.log-out />
                    </button>
                    @elseif(in_array($campaign->id, $pendingCampaignIds, true))
                    <span class="button btn-secundary cursor-not-allowed opacity-70">
                        Pendiente de aprobación
                    </span>
                    @elseif(in_array($campaign->id, $availableCampaignIds, true))
                    <a href="javascript:void(0)"
                        class="button btn-secundary"
                        wire:click='getIn_campaign("{{ $campaign->code }}")'>
                        Ingresar <x-icons.right-fill />
                    </a>
                    @endif
                </div>
            </li>
            @endforeach
        </ul>

        <x-pagination :paginator="$campaigns" :livewire="true" />
    </article>

    @if(!Auth::user()->is_super_admin)
    <template x-if="joinModal">
        <div class="modal-container show" @click="joinModal = false">
            <div class="modal-inner modal-md" x-on:click.stop>
                <button
                    type="button"
                    class="button modal-close"
                    @click="joinModal = false">
                    <x-icons.close />
                </button>

                <div class="modal-inner__data space-y-5">
                    <header class="section-header">
                        <div class="section-header__title">
                            <hgroup>
                                <h3 class="text-grey-400">Unirme a Campaña</h3>
                            </hgroup>
                        </div>
                        <hr>
                    </header>

                    @if ($joinCampaignMessage || $errors->has('campaign_code'))
                        @php
                            $joinAlertClass = $joinCampaignMessageType === 'error' || $errors->has('campaign_code')
                                ? 'border-red-200 bg-red-50 text-red-700'
                                : 'border-blue-200 bg-blue-50 text-blue-700';
                        @endphp
                        <div class="rounded-md border px-3 py-2 text-sm font-medium {{ $joinAlertClass }}">
                            {{ $joinCampaignMessage ?: $errors->first('campaign_code') }}
                        </div>
                    @endif

                    <div class="group-form-v">
                        <label for="campaign_code_modal">Código de Campaña</label>
                        <input
                            id="campaign_code_modal"
                            type="text"
                            wire:model.defer="campaign_code"
                            placeholder="Digite el código de la campaña">
                    </div>

                    <p class="text-sm text-gray-500">
                        Digita el código de la campaña a la que deseas vincularte.
                    </p>

                    <div class="flex justify-center w-full">
                        <button
                            type="button"
                            class="btn-primary"
                            wire:click="joinCampaign">
                            <x-icons.right-fill /> Vincularme
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
    @endif

    <livewire:campaign.add-campaign-modal />
    <livewire:campaign.edit-campaign-modal />
</section>
