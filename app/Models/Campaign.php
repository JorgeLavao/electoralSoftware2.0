<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'candidate_name',
        'position',
        'code',
        'start_date',
        'end_date',
        'status'
    ];

    public function foreign_users(){
        return $this->belongsToMany(User::class);
    }
}
