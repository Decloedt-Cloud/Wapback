<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Password;
use App\Mail\ClientConfirmationMail;
use App\Mail\IntervenantConfirmationMail;
use App\Mail\PasswordResetMail;
use App\Models\Intervenant;
use App\Models\IntervenantDisponibilite;
use App\Models\IntervenantDocument;
use App\Repositories\Interfaces\IntervenantRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IntervenantRepository implements IntervenantRepositoryInterface
{

    public function store($request)
    {

        $validator = Validator::make($request->all(), [
            'type_entreprise' => 'nullable|in:Auto-Entrepreneur,Freelancer,Entreprise',
            'nom_entreprise' =>'nullable|string',
            'activite_entreprise' => 'nullable|string',
            'ville' => 'nullable|string',
            'adresse' => 'nullable|string',
            'telephone' => 'nullable|string',
            'sexe' => 'nullable|in:Homme,Femme',
            'prenom' => 'nullable|string',
            'nom' => 'nullable|string',
            'date_naissance' => 'nullable|date',
            'langue_maternelle' => 'nullable|string',
            'lieu_naissance' => 'nullable|string',
            'competences' => 'nullable|array', // tableau attendu
            'competences.*' => 'string',       // chaque compétence doit être une chaîne

            // Disponibilités
            'disponibilites' => 'nullable|array',
            'disponibilites.*.jour' => 'required_with:disponibilites|string',
            'disponibilites.*.heure_debut' => 'required_with:disponibilites|date_format:H:i',
            'disponibilites.*.heure_fin' => 'required_with:disponibilites|date_format:H:i|after:disponibilites.*.heure_debut',

            // files
            'photo_profil' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'documents.*' => 'nullable|file|mimes:pdf,doc,docx|max:5120',


        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();

            // Vérifie si l'intervenant existe déjà pour cet utilisateur
            $intervenant = Intervenant::where('user_id', $user->id)->first();

            if (!$intervenant) {
                $intervenant = new Intervenant();
                $intervenant->user_id = $user->id;
            }

            $langue_maternelle = explode(",", $request->langue_maternelle);

            // encodage en JSON
            $json_langue_maternelle = json_encode(value: $langue_maternelle);
            DB::commit();
            $intervenant->type_entreprise = $request->type_entreprise;
            $intervenant->nom_entreprise = $request->nom_entreprise;
            $intervenant->activite_entreprise = $request->activite_entreprise;
            $intervenant->categorie_activite = $request->categorie_activite;
            $intervenant->ville = $request->ville;
            $intervenant->adresse = $request->adresse;
            $intervenant->telephone = $request->indicatif . $request->telephone;
            $intervenant->sexe = $request->sexe;
            $intervenant->prenom = $request->prenom;
            $intervenant->nom = $request->nom;
            $intervenant->date_naissance = $request->date_naissance;
            $intervenant->langue_maternelle = $json_langue_maternelle;
            $intervenant->lieu_naissance = $request->lieu_naissance;
            $intervenant->competences = $request->competences;
            $intervenant->save();


            $user = Auth::user();
            $user->name = $request->nom . '  ' . $request->prenom;
            $user->profil_rempli = true;
            $user->save();


            //🛠️ save les disponibilités avant la création
            $disponibilites = $request->disponibilites ?? [];
            foreach ($disponibilites as $dispo) {
                IntervenantDisponibilite::create([
                    'intervenant_id' => $intervenant->id,
                    'jour' => $dispo['jour'],
                    'heure_debut' => $dispo['heure_debut'],
                    'heure_fin' => $dispo['heure_fin'],
                ]);
            }
            // save files
            if ($request->hasFile('files')) {
                $file = $request->file('file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $filepath = $file->storeAs('', $filename, 'intervenant_attachments'); // stocke dans storage/app/intervenant_attachments
                // Sauvegarder en base de données
                $doc = new IntervenantDocument();
                $doc->intervenant_id = $intervenant->id;
                $doc->type = $request->type;
                $doc->filename = $filename;
                $doc->filepath = $filepath; // chemin relatif dans storage
                $doc->mime_type = $file->getClientMimeType();
                $doc->size = $file->getSize();
                $doc->save();

                return response()->json([
                    'message' => 'Fichier téléchargé avec succès',
                    'document' => $doc,
                ], 201);
            }

            $savedFiles = [];

            // id intervenant
            $folder = $intervenant->id;
            // save images adn documents
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $filepath = $file->storeAs($folder, $filename, 'intervenant_attachments');

                    $doc = new IntervenantDocument();
                    $doc->intervenant_id = $intervenant->id;
                    $doc->type = 'documents';
                    $doc->filename = $filename;
                    $doc->filepath = $filepath;
                    $doc->mime_type = $file->getClientMimeType();
                    $doc->size = $file->getSize();
                    $doc->save();

                    $savedFiles[] = $doc;
                }
            }

            if ($request->hasFile('photo_profil')) {
                $file = $request->file('photo_profil');
                $filename = time() . '_' . $file->getClientOriginalName();
                $filepath = $file->storeAs($folder, $filename, 'intervenant_attachments');


                $photo = new IntervenantDocument();
                $photo->intervenant_id = $intervenant->id;
                $photo->type = 'photo_profil';
                $photo->filename = $filename;
                $photo->filepath = $filepath;
                $photo->mime_type = $file->getClientMimeType();
                $photo->size = $file->getSize();
                $photo->save();

                $savedFiles[] = $photo;
            }
            DB::commit();

            return response()->json([
                'message' => 'Intervenant enregistré avec succès',
                'data' => $intervenant
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur serveur',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
