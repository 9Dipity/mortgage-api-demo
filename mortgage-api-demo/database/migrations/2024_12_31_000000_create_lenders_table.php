<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lenders', function (Blueprint $table) {
            $table->id();
            
            // Lender information
            $table->string('name', 200);
            $table->string('code', 50)->unique();
            $table->string('email');
            $table->string('phone', 20)->nullable();
            $table->string('website')->nullable();
            $table->boolean('is_active')->default(true);
            
            // Approval criteria
            $table->integer('min_credit_score')->default(600);
            $table->decimal('max_ltv_ratio', 5, 2)->default(95.00);
            $table->decimal('min_deposit_percentage', 5, 2)->default(5.00);
            $table->decimal('max_loan_amount', 12, 2)->default(1000000.00);
            $table->decimal('min_loan_amount', 12, 2)->default(50000.00);
            $table->decimal('interest_rate_base', 5, 2)->default(4.50);
            $table->decimal('processing_fee', 10, 2)->default(0.00);
            
            $table->timestamps();
            
            // Indexes
            $table->index('code');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lenders');
    }
};
