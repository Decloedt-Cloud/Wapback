<?php

namespace App\Repositories;

use App\Models\Service;
use App\Models\User;
use App\Models\Vendor;
use App\Repositories\Interfaces\ServiceRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;


class ServiceRepository implements ServiceRepositoryInterface
{


    protected $client;
    protected $baseUrl = 'http://89.116.111.8:8080/1.0/kb';

    public function __construct()
    {
        $this->client = new Client();
    }


    private function generateUniqueKey($length = 10, $column = 'api_key')
    {
        do {
            $key = Str::random($length);
            $exists = Vendor::where($column, $key)->exists();
        } while ($exists);

        return $key;
    }

    public function store($request)
    {
        $user = $request->user();
        $existingVendor = Vendor::where('user_id', $user->id)->first();

        DB::beginTransaction();
        try {
            if (!$existingVendor) {
                // 1️⃣ Create vendor & Kill Bill tenant/account
                $apiKey = $this->generateUniqueKey(10, 'api_key');
                $apiSecret = $this->generateUniqueKey(10, 'api_secret');

                // Create Kill Bill tenant
                $this->client->post("{$this->baseUrl}/tenants", [
                    'auth' => ['admin', 'password'],
                    'headers' => [
                        'X-Killbill-CreatedBy' => 'demo',
                        'X-Killbill-Reason' => 'demo',
                        'X-Killbill-Comment' => 'demo',
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                    'json' => [
                        'apiKey' => $apiKey,
                        'apiSecret' => $apiSecret,
                    ],
                ]);

                // Fetch tenant info
                $response = $this->client->get("{$this->baseUrl}/tenants", [
                    'auth' => ['admin', 'password'],
                    'headers' => ['Accept' => 'application/json'],
                    'query' => ['apiKey' => $apiKey],
                ]);
                $tenantData = json_decode($response->getBody(), true);

                // Save vendor
                $vendor = Vendor::create([
                    'name'       => $user->name,
                    'api_key'    => $apiKey,
                    'api_secret' => $apiSecret,
                    'tenant_id'  => $tenantData['tenantId'],
                    'user_id'    => $user->id,
                ]);

                // Create Kill Bill account
                $externalKey = $user->email . '-' . Str::slug($user->name);
                $headers = [
                    'X-Killbill-ApiKey'    => 'vdfqavrsjw',
                    'X-Killbill-ApiSecret' => 'eeg3ee7373',
                    'Content-Type'         => 'application/json',
                    'Accept'               => 'application/json',
                    'X-Killbill-CreatedBy' => 'demo',
                    'X-Killbill-Reason'    => 'demo',
                    'X-Killbill-Comment'   => 'demo',
                ];
                $this->client->post("{$this->baseUrl}/accounts", [
                    'auth' => ['admin', 'password'],
                    'headers' => $headers,
                    'json' => [
                        'name' => $user->name,
                        'email' => $user->email,
                        'currency' => 'EUR',
                        'externalKey' => $externalKey,
                    ],
                ]);

                $response = $this->client->get("{$this->baseUrl}/accounts", [
                    'auth' => ['admin', 'password'],
                    'headers' => $headers,
                    'query' => ['externalKey' => $externalKey],
                ]);
                $account = json_decode($response->getBody(), true);

                $vendor->account_id = $account['accountId'];
                $vendor->save();
            } else {
                $vendor = $existingVendor;
            }

            // 2️⃣ Validate & create service if request has service data
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

            $service = new Service([
                'name' => $request->name,
                'category_id' => $request->category_id,
                'custom_category' => $request->custom_category,
                'status' => 'en_attente',
                'description' => $request->description,
                'price_ht' => $request->price_ht,
                'user_id' => $user->id,
            ]);
            $service->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Service créé avec succès.\nEn attente de validation par l’Admin.",
                'vendor' => $vendor,
                'service' => $service,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Operation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function index()
    {
        try {
            $user = auth()->user();

            // Fetch all services including archived
            $services = Service::with('category:id,nom')
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Liste complète des services récupérée avec succès.',
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


    public function delete($id)
    {
        $user = auth()->user();

        DB::beginTransaction();
        try {
            $service = Service::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$service) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service introuvable ou non autorisé.'
                ], 404);
            }

            $service->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Service supprimé avec succès.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'La suppression du service a échoué.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function toggleArchive($id)
    {
        $user = auth()->user();

        DB::beginTransaction();
        try {

            $service = Service::where('id', $id)->where('user_id', $user->id)->first();



            if (!$service) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service introuvable ou non autorisé.'
                ], 404);
            }


            if ($service->archived_at) {
                // Désarchiver
                $service->archived_at = null;
                $message = 'Service désarchivé avec succès.';
            } else {
                // Archiver
                $service->archived_at = now();
                $message = 'Service archivé avec succès.';
            }
            $service->save();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'service' => $service
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l’archivage/désarchivage.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function update($request, $id)
    {
        $user = $request->user();
        DB::beginTransaction();
        try {
            // 1️⃣ Validate input
            $validator = Validator::make($request->all(), [
                'name'            => 'required|string|max:255',
                'category_id'     => 'nullable|integer|exists:categories,id',
                'custom_category' => 'nullable|string|max:255',
                'status'          => 'nullable|in:en_attente,validee,refusee,archivee',
                'description'     => 'required|string',
                'price_ht'        => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors'  => $validator->errors()
                ], 422);
            }

            $service = Service::where('id', $id)
                ->where('user_id', $user->id)
                ->first();


            if (!$service) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service introuvable ou non autorisé pour cet utilisateur.'
                ], 404);
            }
            $service->name = $request->name;
            $service->category_id = $request->category_id;
            $service->custom_category = $request->custom_category;
            $service->status = $request->status ?? $service->status;
            $service->description = $request->description;
            $service->price_ht = $request->price_ht;
            $service->save();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Service mis à jour avec succès.',
                'service' => $service,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Échec de la mise à jour',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
