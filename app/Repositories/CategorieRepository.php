<?php


namespace App\Repositories;

use App\Models\Categorie;
use App\Models\User;
use App\Repositories\Interfaces\CategorieRepositoryInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;

class CategorieRepository implements CategorieRepositoryInterface
{

    public function index()
    {
        $categorie = Categorie::orderBy('created_at', 'desc')->get();
        return response()->json([
            'success' => true,
            'categories' => $categorie
        ]);
    }

    public function categorieUser($request)
    {
        $user = $request->user();
        $categorie = Categorie::where('user_id', $user->id)->get();
        return $categorie;
    }


    public function store($request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255|unique:categories,nom',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,webp|max:5120',
            'is_visible' => 'required|boolean',
        ], [
            'nom.required' => 'Le nom de la catégorie est obligatoire.',
            'nom.string' => 'Le nom de la catégorie doit être une chaîne de caractères.',
            'nom.max' => 'Le nom de la catégorie ne peut pas dépasser 255 caractères.',
            'nom.unique' => 'Cette catégorie existe déjà.',
            'description.required' => 'La description de la catégorie est obligatoire.',
            'description.string' => 'La description doit être une chaîne de caractères.',
            'image.required' => 'L\'image est obligatoire.',
            'image.image' => 'Le fichier doit être une image.',
            'image.mimes' => 'Les extensions acceptées sont jpeg, png, webp.',
            'image.max' => 'La taille maximale autorisée est 5 MB.',
            'is_visible.required' => 'Le choix d\'affichage est obligatoire.',
            'is_visible.boolean' => 'Le choix d\'affichage doit être Oui ou Non.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }


        try {
            DB::beginTransaction();
            $categorie = new Categorie();
            $categorie->user_id = "1";
            $categorie->status = "active";
            $categorie->nom = $request->nom;
            $categorie->description = $request->description;
            $categorie->is_visible = $request->is_visible;

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = time() . '_' . $file->getClientOriginalName();

                // Store in the 'catégorie_attachments' disk
                $filepath = $file->storeAs('', $filename, 'categorie_attachments');

                // Save the relative path in DB
                $categorie->image_path = $filename;
            }
            $categorie->save();
            DB::commit();

            return response()->json([
                'message' => 'Catégorie enregistrée avec succès',
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
        $categorie = Categorie::where('id', $id)->first();

        if (!$categorie) {
            return response()->json([
                'message' => 'Catégorie non trouvée.'
            ], 404);
        }
        try {
            DB::beginTransaction();

            if ($categorie->image_path) {
                Storage::disk('categorie_attachments')->delete($categorie->image_path);
            }
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
