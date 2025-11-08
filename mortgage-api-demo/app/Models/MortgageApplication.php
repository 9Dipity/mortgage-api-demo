<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Events\MortgageApplicationSubmitted;
use App\Events\ApplicationStatusChanged;

/**
 * Mortgage Application Model
 * 
 * Represents a mortgage application with full lifecycle management
 * including credit checks, document verification, and approval workflow.
 */
class MortgageApplication extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Application status constants
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_CREDIT_CHECK = 'credit_check';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_COMPLETED = 'completed';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'lender_id',
        'applicant_id',
        'property_value',
        'loan_amount',
        'deposit_amount',
        'loan_term_years',
        'interest_rate',
        'monthly_payment',
        'property_address',
        'property_type',
        'purchase_type',
        'status',
        'risk_score',
        'affordability_ratio',
        'loan_to_value_ratio',
        'submitted_at',
        'reviewed_at',
        'decision_at',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'property_value' => 'decimal:2',
        'loan_amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'monthly_payment' => 'decimal:2',
        'risk_score' => 'integer',
        'affordability_ratio' => 'decimal:2',
        'loan_to_value_ratio' => 'decimal:2',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'decision_at' => 'datetime',
    ];

    /**
     * The event map for the model.
     *
     * @var array<string, string>
     */
    protected $dispatchesEvents = [
        'created' => MortgageApplicationSubmitted::class,
    ];

    /**
     * Get the lender that owns the application.
     */
    public function lender(): BelongsTo
    {
        return $this->belongsTo(Lender::class);
    }

    /**
     * Get the applicant that owns the application.
     */
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    /**
     * Get the credit checks for the application.
     */
    public function creditChecks(): HasMany
    {
        return $this->hasMany(CreditCheck::class);
    }

    /**
     * Get the documents for the application.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class);
    }

    /**
     * Get the audit events for the application.
     */
    public function events(): HasMany
    {
        return $this->hasMany(ApplicationEvent::class);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by lender.
     */
    public function scopeForLender($query, int $lenderId)
    {
        return $query->where('lender_id', $lenderId);
    }

    /**
     * Scope to get recent applications.
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Calculate loan-to-value ratio.
     */
    public function calculateLtv(): float
    {
        if ($this->property_value <= 0) {
            return 0;
        }

        return round(($this->loan_amount / $this->property_value) * 100, 2);
    }

    /**
     * Calculate monthly payment.
     */
    public function calculateMonthlyPayment(): float
    {
        $monthlyRate = $this->interest_rate / 100 / 12;
        $numberOfPayments = $this->loan_term_years * 12;

        if ($monthlyRate == 0) {
            return $this->loan_amount / $numberOfPayments;
        }

        $monthlyPayment = $this->loan_amount * 
            ($monthlyRate * pow(1 + $monthlyRate, $numberOfPayments)) /
            (pow(1 + $monthlyRate, $numberOfPayments) - 1);

        return round($monthlyPayment, 2);
    }

    /**
     * Check if application meets basic criteria.
     */
    public function meetsBasicCriteria(): bool
    {
        return $this->loan_to_value_ratio <= 95 &&
               $this->affordability_ratio <= 40 &&
               $this->applicant->credit_score >= 600;
    }

    /**
     * Update application status.
     */
    public function updateStatus(string $status, ?string $notes = null): void
    {
        $oldStatus = $this->status;
        
        $this->update([
            'status' => $status,
            'notes' => $notes ?? $this->notes,
        ]);

        // Dispatch status changed event
        event(new ApplicationStatusChanged($this, $oldStatus, $status));
    }

    /**
     * Mark as submitted.
     */
    public function markAsSubmitted(): void
    {
        $this->update([
            'status' => self::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);
    }

    /**
     * Approve the application.
     */
    public function approve(?string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'decision_at' => now(),
            'notes' => $notes,
        ]);
    }

    /**
     * Reject the application.
     */
    public function reject(string $reason): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'decision_at' => now(),
            'notes' => $reason,
        ]);
    }

    /**
     * Check if application is pending.
     */
    public function isPending(): bool
    {
        return in_array($this->status, [
            self::STATUS_SUBMITTED,
            self::STATUS_UNDER_REVIEW,
            self::STATUS_CREDIT_CHECK,
        ]);
    }

    /**
     * Check if application is finalized.
     */
    public function isFinalized(): bool
    {
        return in_array($this->status, [
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
            self::STATUS_COMPLETED,
        ]);
    }
}
