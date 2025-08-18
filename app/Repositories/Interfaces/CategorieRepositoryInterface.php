<?php

namespace App\Repositories\Interfaces;





interface CategorieRepositoryInterface
{

    public function index();
    public function categorieUser($request);
    public function store($request);
    public function update($request,$id);
    public function destroy($id);

}
