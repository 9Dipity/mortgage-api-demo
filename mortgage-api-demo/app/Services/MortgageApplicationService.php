<?php

namespace App\Services;

use App\Models\MortgageApplication;
use App\Models\Applicant;
use App\Models\Lender;
use App\Repositories\MortgageApplicationRepository;
use Illuminate\Support\Facades\DB;

/**
 * Mortgage Application Service
 * 
 * Handles business logic for mortgage application processing.
 * Encapsulates complex operations and calculations.
 */
class MortgageApplicationService
{
    protected MortgageApplicationRepository $repository;

    public function __construct(MortgageApplicationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Create a new mortgage application with calculations.
     */
    public function createApplication(array $data): MortgageApplication
    {
        return DB::transaction(function () use ($data) {
            // Create the application
            $application = $this->repository->create($data);

            // Calculate derived values
            $application->loan_to_value_ratio = $application->calculateLtv();
            $application->monthly_payment = $application->calculateMonthlyPayment();
            
            // Calculate affordability ratio
            $monthlyIncome = $application->applicant->monthly_income;
            if ($monthlyIncome > 0) {
                $application->affordability_ratio = 
                    ($application->monthly_payment / $monthlyIncome) * 100;
            }

            // Calculate risk score
            $application->risk_score = $this->calculateRiskScore($application);

            $application->save();

            return $application->fresh();
        });
    }

    /**
     * Calculate risk score for an application.
     * 
     * Multi-factor risk assessment:
     * - Credit score: 40%
     * - Debt-to-income: 30%
     * - LTV ratio: 20%
     * - Employment stability: 10%
     */
    public function calculateRiskScore(MortgageApplication $application): int
    {
        $applicant = $application->applicant;
        
        // Credit score component (0-40 points)
        $creditScore = min(40, ($applicant->credit_score / 850) * 40);
        
        // Debt-to-income component (0-30 points)
        $dti = $applicant->calculateDebtToIncomeRatio();
        $dtiScore = max(0, 30 - ($dti / 2)); // Lower DTI = higher score
        
        // LTV component (0-20 points)
        $ltvScore = max(0, 20 - ($application->loan_to_value_ratio / 5));
        
        // Employment stability (0-10 points)
        $employmentMonths = $applicant->getEmploymentDurationMonths();
        $employmentScore = min(10, $employmentMonths / 6); // Max at 5 years
        
        $totalScore = $creditScore + $dtiScore + $ltvScore + $employmentScore;
        
        return (int) round($totalScore);
    }

    /**
     * Evaluate if application meets automated approval criteria.
     */
    public function evaluateForAutomatedApproval(MortgageApplication $application): array
    {
        $reasons = [];
        $approved = true;

        // Check credit score
        if ($application->applicant->credit_score < 700) {
            $approved = false;
            $reasons[] = 'Credit score below threshold';
        }

        // Check LTV
        if ($application->loan_to_value_ratio > 80) {
            $approved = false;
            $reasons[] = 'LTV ratio exceeds 80%';
        }

        // Check affordability
        if ($application->affordability_ratio > 35) {
            $approved = false;
            $reasons[] = 'Monthly payment exceeds 35% of income';
        }

        // Check employment
        if ($application->applicant->getEmploymentDurationMonths() < 12) {
            $approved = false;
            $reasons[] = 'Employment duration less than 12 months';
        }

        // Check risk score
        if ($application->risk_score < 60) {
            $approved = false;
            $reasons[] = 'Risk score below acceptable threshold';
        }

        return [
            'approved' => $approved,
            'reasons' => $reasons,
            'recommendation' => $approved ? 'Approve' : 'Manual review required',
        ];
    }

    /**
     * Process automated decision for an application.
     */
    public function processAutomatedDecision(MortgageApplication $application): void
    {
        $evaluation = $this->evaluateForAutomatedApproval($application);

        if ($evaluation['approved']) {
            $application->approve('Approved via automated system');
        } else {
            $application->updateStatus(
                MortgageApplication::STATUS_UNDER_REVIEW,
                'Requires manual review: ' . implode(', ', $evaluation['reasons'])
            );
        }
    }

    /**
     * Get applications filtered and paginated.
     */
    public function getApplications(array $filters = [], int $perPage = 15)
    {
        return $this->repository->getAllWithFilters($filters, $perPage);
    }

    /**
     * Get application with all relationships.
     */
    public function getApplicationById(int $id): ?MortgageApplication
    {
        return $this->repository->findWithRelations($id);
    }

    /**
     * Update application status with validation.
     */
    public function updateApplicationStatus(
        MortgageApplication $application,
        string $status,
        ?string $notes = null
    ): MortgageApplication {
        // Validate status transition
        if ($application->isFinalized() && $status !== MortgageApplication::STATUS_COMPLETED) {
            throw new \InvalidArgumentException('Cannot change status of finalized application');
        }

        $application->updateStatus($status, $notes);

        return $application->fresh();
    }
}
