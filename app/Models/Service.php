<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    //
      protected $fillable = [
        'name',
        'category_id',
        'custom_category',
        'status',
        'description',
        'price_ht',
        'user_id',
    ];
}
