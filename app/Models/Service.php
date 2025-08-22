<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{


    protected $fillable = [
        'name',
        'category_id',
        'custom_category',
        'status',
        'description',
        'price_ht',
        'user_id',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Categorie::class, 'category_id');
    }
}
