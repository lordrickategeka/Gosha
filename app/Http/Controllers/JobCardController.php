<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\JobCardService;
use Illuminate\Http\JsonResponse;

class JobCardController extends Controller
{
    protected JobCardService $jobCardService;

    public function __construct(JobCardService $jobCardService)
    {
        $this->jobCardService = $jobCardService;
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'staff_id' => 'required|exists:users,id',
            'service_type_id' => 'nullable|exists:service_types,id',
            'service_types' => 'nullable|array',
            'service_types.*' => 'exists:service_types,id',

            // Customer data
            'phone' => 'required|string|max:20',
            'customer_name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'contact_person' => 'nullable|string|max:255',
            'address' => 'nullable|string',

            // Vehicle data
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'vehicle_name' => 'required|string|max:255',
            'number_plate' => 'required|string|max:20',
            'chasis_number' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',

            // Other data
            'image_attachment' => 'nullable|string',
            'status' => 'nullable|string|in:pending,in_progress,completed,collected',
            'notes' => 'nullable|string',
            'intake_datetime' => 'nullable|date',
        ]);

        try {
            $jobCard = $this->jobCardService->createJobCard($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Job card created successfully',
                'data' => $jobCard
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create job card',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function searchCustomers(Request $request): JsonResponse
    {
        $query = $request->input('query', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $customers = $this->jobCardService->searchCustomers($query);

        return response()->json($customers);
    }

    public function searchVehicles(Request $request): JsonResponse
    {
        $query = $request->input('query', '');
        $customerId = $request->input('customer_id');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $vehicles = $this->jobCardService->searchVehicles($query, $customerId);

        return response()->json($vehicles);
    }
}
