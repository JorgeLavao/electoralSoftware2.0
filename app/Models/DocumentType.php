<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name'
    ];

    /* User relationship */
    public  function foreign_users(){
        return $this->hasMany(User::class);
    }
}
