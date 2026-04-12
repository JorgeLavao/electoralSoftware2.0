<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Notifications\CustomResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Casts\Attribute;

use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles;

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
        'current_campaign',
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

    public function scopeSearch($query, $search){
        if(empty($search)){
            return $query;
        }
        return $query->where(function ($q) use ($search) {
            $q->whereRaw(
                "TRIM(CONCAT_WS(' ', first_name, middle_name, paternal_surname, maternal_surname)) LIKE ?",
                ["%{$search}%"]
            )->orWhere('document_number', 'like', "%{$search}%");
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
       return $this->belongsTo(DocumentType::class, 'document_type_id', 'id');
    }

    public function foreing_aditional_info(){
        return $this->hasOne(UserProfile::class);
    }

    public function foreign_campaings(){
        return $this->belongsToMany(Campaign::class)->withPivot('validate');
    }

    public function foreign_lists(){
        return $this->belongsToMany(CampaignList::class, 'list_user', 'user_id', 'list_id');
    }
}
