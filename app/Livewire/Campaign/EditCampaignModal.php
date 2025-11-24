<?php

namespace App\Livewire\Campaign;

use App\Models\Campaign;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class EditCampaignModal extends Component
{
    public $showModal = false;
    public $campaign;

    #[Validate('required',  message: 'Debe ingresar el nombre de la campaña.')]
    #[Validate('string',    message: 'El nombre de la campaña debe ser texto.')]
    #[Validate('max:100',   message: 'El nombre no debe exceder 100 caracteres.')]
    public $cpg_name;

    #[Validate('required',  message: 'Debe ingresar el nombre del candidato.')]
    #[Validate('string',    message: 'El nombre del candidato debe ser texto.')]
    #[Validate('max:50',    message: 'El nombre del candidato no debe exceder 100 caracteres.')]
    public $cand_name;


    #[Validate('required',  message: 'Debe ingresar el cargo al que aspira el candidato.')]
    #[Validate('string',    message: 'El nombre del cargo debe ser texto.')]
    #[Validate('max:100',   message: 'El nombre del cargo no debe exceder 100 caracteres.')]
    public $position;

    #[Validate('required',              message: 'Debe ingresar el código de la campaña.')]
    #[Validate('string',                message: 'El código de la campaña debe ser texto.')]
    #[Validate('max:50',                message: 'El código de campaña no debe exceder los 50 caracteres.')]
    #[Validate('unique:campaigns,code', message: 'El código de la campaña ya esta en uso.')]
    public $cpg_code;

    #[Validate('required',              message: 'Debe seleccionar el inicio de la campaña.')]
    #[Validate('date',                  message: 'La fecha de inicio no tiene un formato válido.')]
    #[Validate('after_or_equal:today',  message: 'La fecha de inicio no puede ser en el pasado.')]
    public $start_date;

    #[Validate('required',          message: 'Debe seleccionar la finalización de la campaña.')]
    #[Validate('date',              message: 'La fecha de finalización no tiene un formato válido.')]
    #[Validate('after:start_date',  message: 'La fecha de finalización debe ser posterior a la fecha de inicio.')]
    public $end_date;

    #[Validate('required',          message: 'Debe seleccionar el administrador de la campaña.')]
    #[Validate('array',             message: 'Revisa la información e intentar nuevamente')]
    #[Validate('min:1',             message: 'Debe seleecionar al menos 1 administrador de la campaña')]
    public Array $user_ids = [];

    #[On('openEditModal')]
    public function showModal(Campaign $campaign){
        $this->campaign     = $campaign;
        //seter input data
        $this->cpg_name     = $campaign->name;
        $this->cand_name    = $campaign->candidate_name;
        $this->position     = $campaign->position;
        $this->cpg_code     = $campaign->code;
        $this->start_date   = $campaign->start_date;
        $this->end_date     = $campaign->end_date;
        $this->user_ids     = $campaign->foreign_users()->pluck('id')->toarray();

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
        $this->reset(['cpg_name', 'cand_name', 'position', 'cpg_code', 'start_date', 'end_date', 'user_ids']);
    }

    #[On('user-added')]
    public function onUserAdded($userId){
        if ($userId && !in_array($userId, $this->user_ids)) {
            $this->user_ids[] = $userId;
        }
    }

    #[On('user-removed')]
    public function onUserRemoved($userId){
        $this->user_ids = array_filter(
            $this->user_ids,
            fn ($id) => $id != $userId
        );
    }

    public function saveCampaign(){
        $this->validate();
        try {
            $this->updateCampaign();
            $this->closeModal();
            session()->flash('success', 'La campaña se Actualizó exitosamente!');
            $this->redirectIntended(default: route('campaign.index', absolute: false), navigate: true);
        }catch(\Throwable $e){
            session()->flash('error', 'Error al guardar la campaña. Revisa los datos e intenta nuevamente.');
            Log::error('Campaign creation failed', ['exception' => $e]);
        }
    }

    protected function createCampaign(){
        return DB::transaction(function(){

            $this->campaign->update([
                'name'              => $this->cpg_name,
                'candidate_name'    => $this->cand_name,
                'position'          => $this->position,
                'code'              => $this->cpg_code,
                'start_date'        => $this->start_date,
                'end_date'          => $this->end_date,
            ]);
            $this->campaign->foreign_users()->syncWithoutDetaching($this->user_ids);
            return $this->campaign;
        });
    }

    public function render()
    {
        return view('livewire.campaign.edit-campaign-modal');
    }
}
