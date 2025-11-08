<?php

namespace App\Services;

use App\Models\MortgageApplication;
use App\Models\CreditCheck;

/**
 * Credit Check Service
 * 
 * Handles credit check operations for mortgage applications.
 * In production, this would integrate with external credit bureaus.
 */
class CreditCheckService
{
    /**
     * Perform credit check for an application.
     * 
     * @param MortgageApplication $application
     * @return CreditCheck
     */
    public function performCreditCheck(MortgageApplication $application): CreditCheck
    {
        // In production, this would call external credit bureau APIs
        // For demo purposes, we use the applicant's existing credit score
        
        return CreditCheck::create([
            'mortgage_application_id' => $application->id,
            'credit_score' => $application->applicant->credit_score,
            'credit_report_data' => [
                'bureaus_checked' => ['Experian', 'Equifax', 'TransUnion'],
                'checked_at' => now(),
                'debt_to_income_ratio' => $application->applicant->calculateDebtToIncomeRatio(),
                'payment_history' => 'Good',
                'credit_utilization' => 35,
                'length_of_credit_history' => 10,
            ],
            'status' => 'completed',
            'checked_at' => now(),
        ]);
    }
    
    /**
     * Get latest credit check for an application.
     */
    public function getLatestCreditCheck(MortgageApplication $application): ?CreditCheck
    {
        return $application->creditChecks()
            ->orderBy('checked_at', 'desc')
            ->first();
    }
}
