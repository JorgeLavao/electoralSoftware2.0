<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'gender_id',
        'occupation_id',
        'vehicle',
        'zone',
        'department',
        'municipality',
        'district_commune',
        'neighborhood_village_name',
        'latitude',
        'longitude',
        'current_location'
    ];

    public function foreign_gender(){
        return $this->hasOne(Gender::class);
    }

    public function foreign_occupations(){
        return $this->hasMany(Occupation::class);
    }

    public function foreign_user(){
        return $this->belongsTo(User::class);
    }
}
