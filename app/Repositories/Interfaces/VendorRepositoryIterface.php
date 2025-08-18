<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Http\Request;



interface VendorRepositoryIterface{

    public function index();

    public function  createAccountVendor($request);




}
