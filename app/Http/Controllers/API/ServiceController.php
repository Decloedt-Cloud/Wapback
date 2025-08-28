<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Service;
use App\Repositories\Interfaces\ServiceRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;


class ServiceController extends Controller
{

    protected $client;
    protected $baseUrl = 'http://89.116.111.8:8080/1.0/kb';  // Adjust URL if necessary




    protected $serviceRepository;

    public function __construct(ServiceRepositoryInterface $serviceRepositoryInterface)
    {
        $this->serviceRepository = $serviceRepositoryInterface;
        $this->client = new Client();
    }







    public function store(Request $request)
    {
        // $data = $request->all();
        return  $this->serviceRepository->store($request);
    }

    public function index()
    {
        return $this->serviceRepository->index();
    }
    public function destroy($id)
    {
    return $this->serviceRepository->delete($id);
    }


    public function update(Request $request, $id){
        return $this->serviceRepository->update($request, $id);
    }

    // public function storeSimplePlan(Request $request)
    // {
    //     Log::info('storeSimplePlan called', ['user_id' => Auth::id(), 'request_data' => $request->all()]);

    //     $vendor = Auth::user()?->vendor;

    //     if (!$vendor) {
    //         Log::warning('No vendor linked to user', ['user_id' => Auth::id()]);
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'No vendor linked to the authenticated user.'
    //         ], 400);
    //     }

    //     if (empty($vendor->tenant_id)) {
    //         Log::warning('Vendor does not have a Kill Bill tenant', ['vendor_id' => $vendor->id]);
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Vendor does not have a Kill Bill tenant.'
    //         ], 400);
    //     }

    //     $validated = $request->validate([
    //         'planId'        => 'required|string|max:255',
    //         'productName'   => 'required|string|max:255',
    //         'currency'      => 'required|string|size:3',
    //         'amount'        => 'required|numeric|min:0',
    //         'billingPeriod' => 'required|in:DAILY,WEEKLY,MONTHLY,ANNUAL',
    //         'trialLength'   => 'nullable|integer|min:0',
    //         'trialTimeUnit' => 'nullable|in:DAYS,UNLIMITED',
    //     ]);

    //     $payload = [
    //         "planId" => $validated['planId'],
    //         "productName" => $validated['productName'],
    //         "productCategory" => "BASE",
    //         "currency" => strtoupper($validated['currency']),
    //         "amount" => $validated['amount'],
    //         "billingPeriod" => strtoupper($validated['billingPeriod']),
    //         "trialLength" => isset($validated['trialLength']) ? (int)$validated['trialLength'] : 0,
    //         "trialTimeUnit" => strtoupper($validated['trialTimeUnit'] ?? 'DAYS'),
    //         "availableBaseProducts" => []
    //     ];

    //     Log::info('Sending payload to Kill Bill', ['payload' => $payload, 'tenant' => $vendor->tenant_id]);

    //     try {
    //         $response = $this->client->post("{$this->baseUrl}/catalog/simplePlan", [
    //             'auth' => ['admin', 'password'],
    //             'headers' => [
    //                 'X-Killbill-ApiKey'    => $vendor->api_key,
    //                 'X-Killbill-ApiSecret' => $vendor->api_secret,
    //                 'X-Killbill-Tenant'    => $vendor->tenant_id,
    //                 'X-Killbill-CreatedBy' => 'demo',
    //                 'X-Killbill-Reason'    => 'demo',
    //                 'X-Killbill-Comment'   => 'demo',
    //                 'Content-Type'         => 'application/json',
    //                 'Accept'               => 'application/json',
    //             ],
    //             'json' => $payload,
    //         ]);

    //         Log::info('Kill Bill response', ['status' => $response->getStatusCode(), 'body' => (string)$response->getBody()]);

    //         if ($response->getStatusCode() === 201) {
    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'Plan created successfully!'
    //             ]);
    //         }

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Unexpected response code: ' . $response->getStatusCode()
    //         ], 400);
    //     } catch (\GuzzleHttp\Exception\ClientException $e) {
    //         $body = (string) $e->getResponse()->getBody();
    //         Log::error('KillBill ClientException', ['error' => $body]);
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'KillBill API error',
    //             'error' => $body
    //         ], 500);
    //     } catch (\Exception $e) {
    //         Log::error('Unexpected Exception', ['error' => $e->getMessage()]);
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Unexpected error',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function toggleArchive($id)
{
    return $this->serviceRepository->toggleArchive($id);
}

}
