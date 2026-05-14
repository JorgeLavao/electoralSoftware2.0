<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'birth_date',
        'birth_day',
        'birth_month',
        'gender_id',
        'age_range_id',
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

    protected $casts = [
        'birth_date' => 'date',
        'birth_day' => 'integer',
        'birth_month' => 'integer',
    ];

    public function foreign_gender(){
        return $this->hasOne(Gender::class, 'id', 'gender_id');
    }

    public function foreign_occupations(){
        return $this->hasOne(Occupation::class, 'id', 'occupation_id');
    }

    public function foreign_user(){
        return $this->belongsTo(User::class);
    }

    public function foreign_range_age(){
        return $this->belongsTo(AgeRange::class, 'age_range_id', 'id');
    }
}
