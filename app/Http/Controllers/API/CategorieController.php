<?php

namespace App\Http\Controllers\API;

use App\Models\Categorie;
use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\CategorieRepositoryInterface;
use Illuminate\Http\Request;

class CategorieController extends Controller
{



    protected $categorieRepository;

    public function __construct(CategorieRepositoryInterface $categorieRepositoryInterface)
    {
        $this->categorieRepository = $categorieRepositoryInterface;
    }

    public function index()
    {
        return $this->categorieRepository->index();
    }

    public function categorieUser(Request $request)
    {
        return $this->categorieRepository->categorieUser($request);
    }


    public function store(Request $request)
    {
        $categorie = $this->categorieRepository->store($request);
        return response()->json($categorie);
    }

    public function update(Request $request, $id)
    {
        $categorie  =  $this->categorieRepository->update($request, $id);
        return response()->json($categorie);
    }

    public function destroy($id){
        $categorie = $this->categorieRepository->destroy($id);
        return response()->json($categorie);

    }
}
