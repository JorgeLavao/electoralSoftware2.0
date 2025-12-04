<?php

namespace App\Livewire\Campaign;

use App\Models\Campaign;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class AddCampaignModal extends Component
{
    public $showModal = false;

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

    #[Validate('required',                                          message: 'Debe ingresar el código de la campaña.')]
    #[Validate('string',                                            message: 'El código de la campaña debe ser texto.')]
    #[Validate('max:50',                                            message: 'El código de campaña no debe exceder los 50 caracteres.')]
    #[Validate('regex:/^[a-zA-Z0-9]+[a-zA-Z0-9\-_]*[a-zA-Z0-9]+$/', message: 'El código de campaña no tiene un formato válido.')]
    #[Validate('unique:campaigns,code',                             message: 'El código de la campaña ya esta en uso.')]
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

    #[On('openCampaignModal')]
    public function openModal(): void
    {
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
            $this->createCampaign();
            $this->closeModal();
            session()->flash('success', 'Campaña creada exitosamente!');
            $this->redirectIntended(default: route('campaign.index', absolute: false), navigate: true);
        }catch(\Throwable $e){
            session()->flash('error', 'Error al guardar la campaña. Revisa los datos e intenta nuevamente.');
            Log::error('Campaign creation failed', ['exception' => $e]);
        }
    }

    protected function createCampaign(){
        return DB::transaction(function(){
            $campaign = Campaign::create([
                'name'              => $this->cpg_name,
                'candidate_name'    => $this->cand_name,
                'position'          => $this->position,
                'code'              => $this->cpg_code,
                'start_date'        => $this->start_date,
                'end_date'          => $this->end_date,
                'status'            => '1',
            ]);
            $campaign->foreign_users()->attach($this->user_ids);
            return $campaign;
        });
    }

    // Cerrar modal con tecla Escape
    public function onEscape(): void
    {
        $this->cerrarModal();
    }

    public function render()
    {
        return view('livewire.campaign.add-campaign-modal');
    }
}
