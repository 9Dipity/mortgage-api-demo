<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MortgageApplication;
use App\Services\MortgageApplicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Mortgage Application API Controller
 * 
 * RESTful API for mortgage application management.
 * Follows Laravel best practices with service layer separation.
 */
class MortgageApplicationController extends Controller
{
    protected MortgageApplicationService $service;

    public function __construct(MortgageApplicationService $service)
    {
        $this->service = $service;
    }

    /**
     * Get all mortgage applications with filtering.
     * 
     * @param Request $request
     * @return JsonResponse
     * 
     * Query Parameters:
     * - status: Filter by application status
     * - lender_id: Filter by lender
     * - from_date: Filter applications from date
     * - to_date: Filter applications to date
     * - search: Search by applicant name/email
     * - sort_by: Sort field (default: created_at)
     * - sort_order: Sort direction (default: desc)
     * - per_page: Results per page (default: 15)
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'status',
            'lender_id',
            'from_date',
            'to_date',
            'search',
            'min_risk_score',
            'sort_by',
            'sort_order'
        ]);

        $perPage = $request->input('per_page', 15);

        $applications = $this->service->getApplications($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => $applications->items(),
            'meta' => [
                'current_page' => $applications->currentPage(),
                'last_page' => $applications->lastPage(),
                'per_page' => $applications->perPage(),
                'total' => $applications->total(),
            ]
        ]);
    }

    /**
     * Get a specific mortgage application.
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $application = $this->service->getApplicationById($id);

        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $application
        ]);
    }

    /**
     * Create a new mortgage application.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lender_id' => 'required|exists:lenders,id',
            'applicant_id' => 'required|exists:applicants,id',
            'property_value' => 'required|numeric|min:0',
            'loan_amount' => 'required|numeric|min:0',
            'deposit_amount' => 'required|numeric|min:0',
            'loan_term_years' => 'required|integer|min:1|max:40',
            'interest_rate' => 'required|numeric|min:0',
            'property_address' => 'required|string|max:500',
            'property_type' => 'required|in:detached,semi_detached,terraced,flat,bungalow',
            'purchase_type' => 'required|in:purchase,remortgage,first_time_buyer',
        ]);

        try {
            $application = $this->service->createApplication($validated);

            return response()->json([
                'success' => true,
                'message' => 'Application created successfully',
                'data' => $application
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create application',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update application status.
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $application = $this->service->getApplicationById($id);

        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found'
            ], 404);
        }

        $validated = $request->validate([
            'status' => 'required|in:draft,submitted,under_review,credit_check,approved,rejected,completed',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            $updatedApplication = $this->service->updateApplicationStatus(
                $application,
                $validated['status'],
                $validated['notes'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'data' => $updatedApplication
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get automated approval evaluation.
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function evaluateApplication(int $id): JsonResponse
    {
        $application = $this->service->getApplicationById($id);

        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found'
            ], 404);
        }

        $evaluation = $this->service->evaluateForAutomatedApproval($application);

        return response()->json([
            'success' => true,
            'data' => $evaluation
        ]);
    }

    /**
     * Process automated decision for application.
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function processAutomatedDecision(int $id): JsonResponse
    {
        $application = $this->service->getApplicationById($id);

        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found'
            ], 404);
        }

        try {
            $this->service->processAutomatedDecision($application);

            return response()->json([
                'success' => true,
                'message' => 'Automated decision processed',
                'data' => $application->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process decision',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
