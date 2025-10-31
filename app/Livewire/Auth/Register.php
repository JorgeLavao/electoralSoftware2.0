<?php

namespace App\Livewire\Auth;

use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Register extends Component
{
    public int      $page = 1;
    //page 1
    public          $documents_type;
    public int      $doc_type;
    public string   $doc_number;
    //page 2
    public string   $first_name;
    public string   $middle_name;
    public string   $paternal_surname;
    public string   $maternal_surname;
    // page 3
    public string   $celphone;
    public string   $email;
    public string   $password = '';
    public string   $password_confirmation = '';


    public function mount(){
        $this->documents_type = DocumentType::all();
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $this->validate([
            'celphone'  => ['required', 'digits:10'],
            'email'     => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password'  => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ],[
            'celphone.required' => 'El número de celular es obligatorio.',
            'celphone.digits'   => 'El número de celular debe tener 10 dígitos.',
            'email.required'    => 'El correo electronico es obligatorio.',
            'email.string'      => 'El correo electronico no es válido.',
            'email.lowercase'   => 'El correo electronico no es válido.',
            'email.email'       => 'El correo electronico no es válido.',
            'email.max'         => 'El correo electronico no es válido.',
            'email.unique'      => 'El correo electronico ya está en uso',
            'password.required' => 'La contraseña es obligatoria',
            'password.string'   => 'La contraseña tiene un formato inválido',
            'password.confirmed'=> 'Las contraseñas no coinciden.',
            'password.min'      => 'La contraseña debe tener al menos 8 caracteres.',
            'password.mixed_case' => 'La contraseña debe contener al menos una letra mayúscula y una minúscula.',
            'password.letters'  => 'La contraseña debe contener al menos una letra.',
            'password.numbers'  => 'La contraseña debe contener al menos un número.',
            'password.symbols'  => 'La contraseña debe contener al menos un símbolo.',
            'password.uncompromised' => 'La contraseña proporcionada ha sido comprometida en una filtración de datos. Por favor, elige otra.',
        ]);
        $user = User::create([
            'document_type_id'  => $this->doc_type,
            'document_number'   => $this->doc_number,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'paternal_surname' => $this->paternal_surname,
            'maternal_surname' => $this->maternal_surname,
            'celphone' => $this->celphone,
            'email' => $this->email,
            'password' => bcrypt($this->password),
        ]);

        Auth::login($user);

        Session::regenerate();

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }

    public function selectedDoc(){
        if (empty($this->doc_type) || empty($this->documents_type)) {
            return null;
        }
        return $this->documents_type->firstWhere('id', $this->doc_type);
    }

    public function nextStep(){
        if ($this->page == 1) {
            $this->validate([
                'doc_type'      => ['required', 'exists:document_types,id'],
                'doc_number'    => [
                    'required',
                    Rule::unique('users', 'document_number')->where(function ($query) {
                        return $query->where('document_type_id', $this->doc_type);
                    })
                ],
            ], [
                'doc_type'              => 'El tipo de documento es obligatorio.',
                'doc_number.required'   => 'El número de documento es obligatorio.',
                'doc_number.unique'     => 'Ya existe un usuario registrado con este tipo y número de documento.',
            ]);
        } elseif ($this->page == 2) {
            $this->validate([
                'first_name'        => ['required', 'string', 'max:50'],
                'middle_name'       => ['sometimes', 'string', 'max:50'],
                'paternal_surname'  => ['required', 'string', 'max:50'],
                'maternal_surname'  => ['sometimes', 'string', 'max:50']
            ],[
                '*.required' => 'Este campo es obligatorio.',
                '*.string' => 'Este campo debe contener solo texto.',
                '*.max' => 'Este campo no puede tener más de :max caracteres.'
            ]);
        }
        $this->page++;
    }
}
