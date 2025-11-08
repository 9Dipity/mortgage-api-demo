<?php

namespace App\Repositories;

use App\Models\MortgageApplication;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Mortgage Application Repository
 * 
 * Provides data access layer with optimized queries.
 * Separates database logic from business logic.
 */
class MortgageApplicationRepository
{
    /**
     * Get all applications with filters and pagination.
     * 
     * Optimized with eager loading to prevent N+1 queries.
     */
    public function getAllWithFilters(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = MortgageApplication::query()
            ->with(['applicant', 'lender']) // Eager load relationships
            ->select('mortgage_applications.*'); // Explicit select for joins

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by lender
        if (!empty($filters['lender_id'])) {
            $query->where('lender_id', $filters['lender_id']);
        }

        // Filter by date range
        if (!empty($filters['from_date'])) {
            $query->where('created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->where('created_at', '<=', $filters['to_date']);
        }

        // Filter by risk score
        if (!empty($filters['min_risk_score'])) {
            $query->where('risk_score', '>=', $filters['min_risk_score']);
        }

        // Search by applicant name
        if (!empty($filters['search'])) {
            $query->whereHas('applicant', function ($q) use ($filters) {
                $q->where('first_name', 'like', "%{$filters['search']}%")
                  ->orWhere('last_name', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        // Sort
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Find application by ID with all relationships.
     */
    public function findWithRelations(int $id): ?MortgageApplication
    {
        return MortgageApplication::with([
            'applicant',
            'lender',
            'creditChecks',
            'documents',
            'events' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }
        ])->find($id);
    }

    /**
     * Create a new application.
     */
    public function create(array $data): MortgageApplication
    {
        return MortgageApplication::create($data);
    }

    /**
     * Update an application.
     */
    public function update(MortgageApplication $application, array $data): MortgageApplication
    {
        $application->update($data);
        return $application->fresh();
    }

    /**
     * Get applications for a specific lender.
     */
    public function getByLender(int $lenderId, int $perPage = 15): LengthAwarePaginator
    {
        return MortgageApplication::with(['applicant'])
            ->where('lender_id', $lenderId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get recent applications statistics.
     * 
     * Optimized query for dashboard metrics.
     */
    public function getStatistics(int $lenderId = null): array
    {
        $query = MortgageApplication::query();

        if ($lenderId) {
            $query->where('lender_id', $lenderId);
        }

        return [
            'total' => $query->count(),
            'pending' => (clone $query)->whereIn('status', [
                MortgageApplication::STATUS_SUBMITTED,
                MortgageApplication::STATUS_UNDER_REVIEW,
                MortgageApplication::STATUS_CREDIT_CHECK,
            ])->count(),
            'approved' => (clone $query)->where('status', MortgageApplication::STATUS_APPROVED)->count(),
            'rejected' => (clone $query)->where('status', MortgageApplication::STATUS_REJECTED)->count(),
            'avg_loan_amount' => (clone $query)->avg('loan_amount'),
            'total_loan_value' => (clone $query)->where('status', MortgageApplication::STATUS_APPROVED)->sum('loan_amount'),
        ];
    }

    /**
     * Get applications requiring review.
     * 
     * Used by background jobs to process pending applications.
     */
    public function getApplicationsRequiringReview(int $limit = 50): array
    {
        return MortgageApplication::with(['applicant', 'lender'])
            ->where('status', MortgageApplication::STATUS_UNDER_REVIEW)
            ->orderBy('submitted_at', 'asc')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
