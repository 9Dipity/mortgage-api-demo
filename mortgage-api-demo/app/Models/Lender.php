<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Lender Model
 * 
 * Represents a mortgage lender in the multi-tenant system.
 * Each lender has their own approval criteria and configuration.
 */
class Lender extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'email',
        'phone',
        'website',
        'is_active',
        'min_credit_score',
        'max_ltv_ratio',
        'min_deposit_percentage',
        'max_loan_amount',
        'min_loan_amount',
        'interest_rate_base',
        'processing_fee',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'min_credit_score' => 'integer',
        'max_ltv_ratio' => 'decimal:2',
        'min_deposit_percentage' => 'decimal:2',
        'max_loan_amount' => 'decimal:2',
        'min_loan_amount' => 'decimal:2',
        'interest_rate_base' => 'decimal:2',
        'processing_fee' => 'decimal:2',
    ];

    /**
     * Get the applications for the lender.
     */
    public function applications(): HasMany
    {
        return $this->hasMany(MortgageApplication::class);
    }

    /**
     * Scope to filter active lenders.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if lender can accept an application based on criteria.
     */
    public function canAcceptApplication(MortgageApplication $application): bool
    {
        return $this->is_active &&
               $application->applicant->credit_score >= $this->min_credit_score &&
               $application->loan_to_value_ratio <= $this->max_ltv_ratio &&
               $application->loan_amount >= $this->min_loan_amount &&
               $application->loan_amount <= $this->max_loan_amount;
    }

    /**
     * Calculate interest rate for an application.
     */
    public function calculateInterestRate(MortgageApplication $application): float
    {
        $baseRate = $this->interest_rate_base;
        
        // Adjust based on credit score
        if ($application->applicant->credit_score < 700) {
            $baseRate += 0.5;
        }
        
        // Adjust based on LTV
        if ($application->loan_to_value_ratio > 80) {
            $baseRate += 0.25;
        }
        
        return round($baseRate, 2);
    }
}
