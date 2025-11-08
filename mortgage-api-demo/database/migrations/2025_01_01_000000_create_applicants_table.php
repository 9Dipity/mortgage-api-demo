<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applicants', function (Blueprint $table) {
            $table->id();
            
            // Personal information
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email')->unique();
            $table->string('phone', 20);
            $table->date('date_of_birth');
            $table->string('national_insurance_number', 20)->nullable();
            
            // Employment information
            $table->enum('employment_status', [
                'employed',
                'self_employed',
                'unemployed',
                'retired'
            ]);
            $table->string('employer_name', 200)->nullable();
            $table->string('job_title', 100)->nullable();
            $table->date('employment_start_date')->nullable();
            
            // Financial information
            $table->decimal('annual_income', 12, 2);
            $table->decimal('other_income', 12, 2)->default(0);
            $table->decimal('monthly_expenses', 10, 2);
            $table->decimal('existing_debt', 12, 2)->default(0);
            $table->integer('credit_score')->nullable();
            
            // Address information
            $table->string('address_line_1', 200);
            $table->string('address_line_2', 200)->nullable();
            $table->string('city', 100);
            $table->string('postcode', 20);
            $table->string('country', 100)->default('United Kingdom');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['first_name', 'last_name']);
            $table->index('email');
            $table->index('credit_score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applicants');
    }
};
