<?php

namespace App\Repositories;

use App\Models\Service;
use App\Models\User;
use App\Repositories\Interfaces\ServiceRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;




class ServiceRepository implements ServiceRepositoryInterface
{


    protected $client;
    protected $baseUrl = 'http://89.116.111.8:8080/1.0/kb';

    public function __construct()
    {
        $this->client = new Client();
    }

    public function store($request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|integer|exists:categories,id',
            'custom_category' => 'nullable|string|max:255',
            'status' => 'nullable|in:en_attente,validee,refusee,archivee',
            'description' => 'required|string',
            'price_ht' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $amountCents = $request->price_ht;

            // Sauvegarder le service
            $service = new Service();
            $service->name = $request->name;
            $service->category_id  = $request->category_id;
            $service->custom_category = $request->custom_category; // en attente de validation admin
            $service->status = "en_attente";
            $service->description = $request->description;
            $service->price_ht =  $amountCents;
            $service->user_id  = $request->user()->id;
            $service->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Service créé avec succès et en attente de validation par l’administrateur.',
                'response'  => $service,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Échec de la création du service',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
public function index()
{
    try {
        $user = auth()->user();
        $services = Service::with('category:id,nom')->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();



        return response()->json([
            'success' => true,
            'message' => 'Liste des services récupérée avec succès.',
            'data'    => $services
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la récupération des services',
            'error'   => $e->getMessage()
        ], 500);
    }
}


}
