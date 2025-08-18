<?php


namespace App\Repositories;

use App\Models\Categorie;
use App\Models\User;
use App\Repositories\Interfaces\CategorieRepositoryInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;



class CategorieRepository implements CategorieRepositoryInterface
{

    public function index()
    {
        $categorie = Categorie::where('status', 'active')->get();
         return response()->json([
            'success' => true,
            'categories' => $categorie
        ]);
    }

    public function categorieUser($request)
    {
        $user = $request->user(); // ou auth()->user()
        $categorie = Categorie::where('user_id', $user->id)->get();
        return $categorie;
    }


    public function store($request)
    {

        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255|unique:categories,nom',
            'description' => 'required|string',
        ], [
            'nom.required' => 'Le nom de la catégorie est obligatoire.',
            'nom.string' => 'Le nom de la catégorie doit être une chaîne de caractères.',
            'nom.max' => 'Le nom de la catégorie ne peut pas dépasser 255 caractères.',
            'nom.unique' => 'Cette catégorie existe déjà.',
            'description.required' => 'La description de la catégorie est obligatoire.',
            'description.string' => 'La description doit être une chaîne de caractères.',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();
            $user = $request->user();
            // $status = $user->hasRole('Intervenant') ? 'en_attente' : 'active';
            $categorie  = new Categorie();
            $categorie->user_id = $request->user()->id;
            $categorie->status = "active";
            $categorie->nom = $request->nom;
            $categorie->description = $request->description;
            $categorie->save();
            DB::commit();
            return response()->json([
                'message' => 'categorie enregistré avec succès',
                'data' => $categorie
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur serveur',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update($request, $id)
    {
        $categorie = Categorie::find($id);

        if (!$categorie) {
            return response()->json([
                'message' => 'Catégorie non trouvée.'
            ], 404);
        }

        // Validation with custom messages
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255|unique:categories,nom,' . $id,
            'description' => 'required|string',
        ], [
            'nom.required' => 'Le nom de la catégorie est obligatoire.',
            'nom.string' => 'Le nom de la catégorie doit être une chaîne de caractères.',
            'nom.max' => 'Le nom de la catégorie ne peut pas dépasser 255 caractères.',
            'nom.unique' => 'Cette catégorie existe déjà.',
            'description.required' => 'La description de la catégorie est obligatoire.',
            'description.string' => 'La description doit être une chaîne de caractères.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user = $request->user();
            // Keep same logic: Intervenant updates => en_attente, others => active
            // $status = $user->hasRole('Intervenant') ? 'en_attente' : 'active';

            $categorie->nom = $request->nom;
            $categorie->description = $request->description;
            $categorie->status = "active";
            $categorie->save();

            DB::commit();

            return response()->json([
                'message' => 'Catégorie mise à jour avec succès',
                'data' => $categorie
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur serveur',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function destroy($id)
    {
        $categorie = Categorie::find($id);

        if (!$categorie) {
            return response()->json([
                'message' => 'Catégorie non trouvée.'
            ], 404);
        }
        try {
            DB::beginTransaction();

            $categorie->delete();

            DB::commit();

            return response()->json([
                'message' => 'Catégorie supprimée avec succès',
                'data' => $categorie
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur serveur',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
