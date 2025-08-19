<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'custom_category',
        'status',
        'description',
        'price_ht',
        'user_id',
    ];

    // 🔹 Relation avec l’utilisateur (propriétaire du service)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // 🔹 Relation avec la catégorie
    public function category()
    {
        return $this->belongsTo(Categorie::class, 'category_id');
    }
}
