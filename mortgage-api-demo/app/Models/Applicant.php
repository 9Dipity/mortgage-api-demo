<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Applicant Model
 * 
 * Represents a mortgage applicant with their personal and financial information.
 */
class Applicant extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'national_insurance_number',
        'employment_status',
        'employer_name',
        'job_title',
        'employment_start_date',
        'annual_income',
        'other_income',
        'monthly_expenses',
        'existing_debt',
        'credit_score',
        'address_line_1',
        'address_line_2',
        'city',
        'postcode',
        'country',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'employment_start_date' => 'date',
        'annual_income' => 'decimal:2',
        'other_income' => 'decimal:2',
        'monthly_expenses' => 'decimal:2',
        'existing_debt' => 'decimal:2',
        'credit_score' => 'integer',
    ];

    protected $hidden = [
        'national_insurance_number',
    ];

    /**
     * Get the mortgage applications for the applicant.
     */
    public function applications(): HasMany
    {
        return $this->hasMany(MortgageApplication::class);
    }

    /**
     * Get full name attribute.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Calculate total monthly income.
     */
    public function getMonthlyIncomeAttribute(): float
    {
        return ($this->annual_income + $this->other_income) / 12;
    }

    /**
     * Calculate debt-to-income ratio.
     */
    public function calculateDebtToIncomeRatio(): float
    {
        $monthlyIncome = $this->monthly_income;
        
        if ($monthlyIncome <= 0) {
            return 0;
        }

        $monthlyDebt = $this->existing_debt / 12;
        
        return round(($monthlyDebt / $monthlyIncome) * 100, 2);
    }

    /**
     * Calculate maximum affordable mortgage.
     */
    public function calculateMaxAffordableMortgage(float $interestRate = 4.5): float
    {
        // UK standard: 4.5x annual income minus existing debt
        $maxBorrowing = ($this->annual_income * 4.5) - $this->existing_debt;
        
        return max(0, $maxBorrowing);
    }

    /**
     * Check if applicant is employed.
     */
    public function isEmployed(): bool
    {
        return in_array($this->employment_status, ['employed', 'self_employed']);
    }

    /**
     * Get employment duration in months.
     */
    public function getEmploymentDurationMonths(): int
    {
        if (!$this->employment_start_date) {
            return 0;
        }

        return $this->employment_start_date->diffInMonths(now());
    }
}
