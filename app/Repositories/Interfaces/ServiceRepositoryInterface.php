<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Http\Request;



interface ServiceRepositoryInterface{

    public function store( $data);
    public function index();
    public function delete($id);

}
