<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Notifications\CustomResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'document_type_id',
        'document_number',
        'first_name',
        'middle_name',
        'paternal_surname',
        'maternal_surname',
        'celphone',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('middle_name', 'like', "%{$search}%")
              ->orWhere('paternal_surname', 'like', "%{$search}%")
              ->orWhere('maternal_surname', 'like', "%{$search}%")
              ->orWhereRaw("CONCAT_WS(' ', first_name, middle_name, paternal_surname, maternal_surname) like ?", ["%{$search}%"]);
        });
    }

    protected function fullName(): Attribute {
        return Attribute::get(fn () => trim(
            implode(' ', array_filter([
                $this->first_name,
                $this->middle_name,
                $this->paternal_surname,
                $this->maternal_surname,
            ]))
        ));
    }
    public function sendPasswordResetNotification($token){
        $this->notify(new CustomResetPassword($token));
    }

    public function foreign_document_type(){
       return $this->belongsTo(DocumentType::class);
    }

    public function foreing_aditional_info(){
        return $this->hasOne(UserProfile::class);
    }
}
