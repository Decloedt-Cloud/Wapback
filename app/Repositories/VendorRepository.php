<?php

namespace App\Repositories;

use App\Models\Vendor;
use App\Repositories\Interfaces\VendorRepositoryIterface;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;





class VendorRepository implements VendorRepositoryIterface
{

    protected $client;
    protected $baseUrl = 'http://89.116.111.8:8080/1.0/kb';

    public function __construct()
    {
        $this->client = new Client();
    }


    public function index()
    {
        dd('aziz');
    }
    private function generateUniqueKey($length = 10, $column = 'api_key')
    {
        do {
            $key = Str::random($length);
            $exists = Vendor::where($column, $key)->exists();
        } while ($exists);

        return $key;
    }

    public function createAccountVendor($request)
    {
        $userId = $request->user()->id;
        $existingVendor = Vendor::where('user_id', $userId)->first();
        if ($existingVendor) {
            return response()->json([
                'success' => false,
                'message' => "Un compte fournisseur existe déjà pour cet utilisateur.",
            ], 409);
        } else {
            $validated = $request->validate([
                'confirm' => 'required|accepted',
            ], [
                'confirm.accepted' => 'You must confirm before creating the vendor account.',
            ]);

            if (!$request->confirm) {
                return response()->json([
                    'message' => 'Confirmation failed.'
                ], 400);
            }

            DB::beginTransaction();
            $user = $request->user();
            $name = $user->name;
            $email = $user->email;
            try {
                $apiKey = $this->generateUniqueKey(10, 'api_key');
                $apiSecret = $this->generateUniqueKey(10, 'api_secret');

                $response = $this->client->post("{$this->baseUrl}/tenants", [
                    'auth' => ['admin', 'password'],
                    'headers' => [
                        'X-Killbill-CreatedBy' => 'demo',
                        'X-Killbill-Reason'    => 'demo',
                        'X-Killbill-Comment'   => 'demo',
                        'Content-Type'         => 'application/json',
                        'Accept'               => 'application/json',
                    ],
                    'json' => [
                        'apiKey'      => $apiKey,
                        'apiSecret'   => $apiSecret,
                    ],
                ]);

                // Fetch tenant info after creation using query parameter
                $response = $this->client->get("{$this->baseUrl}/tenants", [
                    'auth' => ['admin', 'password'],
                    'headers' => [
                        'Accept' => 'application/json',
                    ],
                    'query' => [
                        'apiKey' => $apiKey
                    ],
                ]);


                $tenantData = json_decode($response->getBody(), true);


                $vendor = new Vendor([
                    'name'       =>  $name,
                    'api_key'    => $apiKey,
                    'api_secret' => $apiSecret,
                    'tenant_id'  => $tenantData['tenantId'],
                    'user_id'    => auth()->id(),
                ]);
                $vendor->save();



                $basicAuth = ['admin', 'password'];
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
                    'auth'    => $basicAuth,
                    'headers' => $headers,
                    'json'    => [
                        'name'        => $name,
                        'email'       => $email,
                        'currency'    => 'USD',
                        'externalKey' => $externalKey,
                    ],
                ]);

                $response = $this->client->get("{$this->baseUrl}/accounts", [
                    'auth'    => $basicAuth,
                    'headers' => $headers,
                    'query'   => [
                        'externalKey' => $externalKey,
                    ],
                ]);

                $account = json_decode($response->getBody(), true);

                // Update vendor with account_id
                $vendor->account_id = $account['accountId'];
                $vendor->save();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Vendor and Kill Bill account created successfully.',
                    'vendor'  => $vendor,
                    'account' => $account['accountId'],

                ]);
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Tenant not found or invalid credentials',
                    'error'   => $e->getMessage(),
                ], 400);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Configuration update failed',
                    'error'   => $e->getMessage(),
                ], 500);
            }
        }
    }
}
