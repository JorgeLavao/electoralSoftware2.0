<?php

namespace App\Livewire\Campaign;

use App\Mail\InviteToCampaign;
use App\Models\Campaign;
use App\Models\DocumentType;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.app')]
class AddSupporter extends Component
{
    public $documents_type = [];
    public $user;
    public $campaign;
    public $showForm = false;

    //form
    #[Validate('required',                  message : 'Selecciona el tipo de documento.')]
    #[Validate('exists:document_types,id',  message : 'Selecciona un tipo de documento válido.')]
    public $doc_type   = null;

    #[Validate('required',      message : 'Ingresa el Número de documento.')]
    #[Validate('min:3',         message : 'Ingresa un documento válido.')]
    public $document_number;

    #[Validate('required',  message : 'Este campo es obligatorio.')]
    #[Validate('string',    message : 'Este campo debe contener solo texto.')]
    #[Validate('max:50',    message : 'Este campo no puede tener más de 50 caracteres.')]
    public $first_name;

    #[Validate('sometimes')]
    #[Validate('string',    message : 'Este campo debe contener solo texto.')]
    #[Validate('max:50',    message : 'Este campo no puede tener más de 50 caracteres.')]
    public $middle_name;

    #[Validate('required',  message : 'Este campo es obligatorio.')]
    #[Validate('string',    message : 'Este campo debe contener solo texto.')]
    #[Validate('max:50',    message : 'Este campo no puede tener más de 50 caracteres.')]
    public $paternal_surname;

    #[Validate('sometimes')]
    #[Validate('string',    message : 'Este campo debe contener solo texto.')]
    #[Validate('max:50',    message : 'Este campo no puede tener más de 50 caracteres.')]
    public $maternal_surname;

    #[Validate('required',  message : 'Este campo es obligatorio.')]
    #[Validate('digits:10',    message : 'Este campo no puede tener más de 10 caracteres.')]
    public $celphone;

    #[Validate('required',  message : 'Este campo es obligatorio.')]
    #[Validate('string',    message : 'El correo electronico no es válido.')]
    #[Validate('lowercase', message : 'El correo electronico no es válido.')]
    #[Validate('email',     message : 'El correo electronico no es válido.')]
    #[Validate('max:100',   message : 'El correo electronico excede la longitud maxima permitida.')]
    public $email;


    public function rules(){
        return [
            'email' => 'unique:users,email,' . ($this->user->id ?? 'NULL'),
        ];
    }

    public function messages(){
        return [
            'email.unique'  => 'El correo ya esta siendo usado por otro usuario.'
        ];
    }

    public function mount(Campaign $campaign){
        $this->campaign       = $campaign;
        $this->documents_type = DocumentType::all();
    }

    public function searchUser(){
        $this->showForm = false;
        $this->validateOnly('doc_type');
        $this->validateOnly('document_number');
        $this->resetValidation();
        // Limpiar campos antes de llenar (evita mezclar datos viejos)
        $this->resetExcept(['doc_type', 'document_number', 'documents_type', 'campaign', 'user']);
        $this->user = User::where('document_type_id', $this->doc_type)->where('document_number', $this->document_number)->first();
        //setting data
        if ($this->user) {
            $this->fill( $this->user->only([
                    'first_name',
                    'middle_name',
                    'paternal_surname',
                    'maternal_surname',
                    'celphone',
                    'email'
                ])
            );
        }
        $this->showForm = true;
    }

    public function sendInvitation(){
        $this->validate();
        $this->resetValidation();
        try{
            DB::transaction(function () {
                $user = $this->updateUserData();
                $this->sendEmail($user);
            });
            session()->flash('success', 'Invitación enviada correctamente.');

            $this->redirectIntended(default: route('campaign.add-supporter', $this->campaign->code, absolute: false), navigate: true);
        }catch(\Throwable $e){
            session()->flash('error', 'Error al enviar la invitación. No se guardaron cambios.');
            Log::error('Campaign creation failed', ['exception' => $e]);
        }
    }

    private function updateUserData(){
        return User::updateOrCreate([
            'document_type_id' => $this->doc_type,
            'document_number'   => $this->document_number,
        ],[
            'first_name'         => $this->first_name,
            'middle_name'        => $this->middle_name ?: null,
            'paternal_surname'   => $this->paternal_surname,
            'maternal_surname'   => $this->maternal_surname ?: null,
            'celphone'           => $this->celphone,
            'email'              => strtolower($this->email),
        ]);
    }

    private function sendEmail($user){
        if($this->campaign->foreign_users->contains('id', $user->id)){
            session()->flash('error', 'El usuario ya pertenece a esta campaña.');
            abort(413);
        };
        Invitation::where('user_id', $user->id)->where('active', true)->update(['active' => false]);
        $token = Str::uuid()->toString();
        //create invitation
        $invitation = new Invitation();
        $invitation->user_id    = $user->id;
        $invitation->campaign_id= $this->campaign->id;
        $invitation->expires_at = now()->addHours(48);
        $invitation->token      = $token;
        $invitation->active     = true;
        $invitation->save();
        //send email
        Mail::to($user->email)->send(new InviteToCampaign($this->campaign, $user->first_name, $invitation->token, $invitation->expires_at));
    }
}
