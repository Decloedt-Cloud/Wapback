<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Repositories\Interfaces\VendorRepositoryIterface;
use Illuminate\Http\Request;

class VendorController extends Controller
{


    protected $vendorRepository;

    public function __construct(VendorRepositoryIterface $vendorRepositoryIterface)
    {
        $this->vendorRepository = $vendorRepositoryIterface;
    }

    public function index()
    {
        $vendor = $this->vendorRepository->index();
        return $vendor;
    }


    public function createAccountVendor(Request $request)
    {
        return $this->vendorRepository->createAccountVendor($request);
    }


    public function store(Request $request)
    {
        //
    }


    public function show(Vendor $vendor)
    {
        //
    }

    public function edit(Vendor $vendor)
    {
        //
    }


    public function update(Request $request, Vendor $vendor)
    {
        //
    }

    public function destroy(Vendor $vendor)
    {
        //
    }
}
