<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create Mortgage Applications Table
 * 
 * Includes optimized indexes for common queries and foreign key relationships.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mortgage_applications', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('lender_id')
                ->constrained()
                ->onDelete('cascade');
            $table->foreignId('applicant_id')
                ->constrained()
                ->onDelete('cascade');
            
            // Property information
            $table->decimal('property_value', 12, 2);
            $table->decimal('loan_amount', 12, 2);
            $table->decimal('deposit_amount', 12, 2);
            $table->integer('loan_term_years');
            $table->decimal('interest_rate', 5, 2);
            $table->decimal('monthly_payment', 10, 2)->nullable();
            $table->string('property_address', 500);
            $table->enum('property_type', [
                'detached',
                'semi_detached',
                'terraced',
                'flat',
                'bungalow'
            ]);
            $table->enum('purchase_type', [
                'purchase',
                'remortgage',
                'first_time_buyer'
            ]);
            
            // Application status and workflow
            $table->enum('status', [
                'draft',
                'submitted',
                'under_review',
                'credit_check',
                'approved',
                'rejected',
                'completed'
            ])->default('draft');
            
            // Calculated metrics
            $table->integer('risk_score')->nullable();
            $table->decimal('affordability_ratio', 5, 2)->nullable();
            $table->decimal('loan_to_value_ratio', 5, 2)->nullable();
            
            // Timestamps for workflow
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('decision_at')->nullable();
            
            // Additional information
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance optimization
            $table->index(['lender_id', 'status', 'created_at']);
            $table->index('status');
            $table->index('submitted_at');
            $table->index('risk_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mortgage_applications');
    }
};
