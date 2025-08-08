<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Intervenant extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documents()
    {
        return $this->hasMany(IntervenantDocument::class);
    }

    public function disponibilites()
    {
        return $this->hasMany(IntervenantDisponibilite::class);
    }
}
