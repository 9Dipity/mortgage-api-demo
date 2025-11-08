<?php

namespace Tests\Feature;

use App\Models\MortgageApplication;
use App\Models\Applicant;
use App\Models\Lender;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Mortgage Application API Test
 * 
 * Feature tests for mortgage application endpoints.
 * Demonstrates testing best practices with factories and database transactions.
 */
class MortgageApplicationApiTest extends TestCase
{
    use RefreshDatabase;

    protected Lender $lender;
    protected Applicant $applicant;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->lender = Lender::create([
            'name' => 'Test Lender',
            'code' => 'TEST001',
            'email' => 'test@lender.com',
            'is_active' => true,
            'min_credit_score' => 600,
            'max_ltv_ratio' => 95,
            'interest_rate_base' => 4.5,
        ]);

        $this->applicant = Applicant::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '07700900000',
            'date_of_birth' => '1990-01-01',
            'employment_status' => 'employed',
            'employer_name' => 'Test Company',
            'job_title' => 'Software Developer',
            'employment_start_date' => '2020-01-01',
            'annual_income' => 60000,
            'other_income' => 0,
            'monthly_expenses' => 1500,
            'existing_debt' => 5000,
            'credit_score' => 750,
            'address_line_1' => '123 Test Street',
            'city' => 'London',
            'postcode' => 'SW1A 1AA',
        ]);
    }

    /**
     * Test creating a mortgage application.
     */
    public function test_can_create_mortgage_application(): void
    {
        $response = $this->postJson('/api/v1/applications', [
            'lender_id' => $this->lender->id,
            'applicant_id' => $this->applicant->id,
            'property_value' => 300000,
            'loan_amount' => 270000,
            'deposit_amount' => 30000,
            'loan_term_years' => 25,
            'interest_rate' => 4.5,
            'property_address' => '456 Property Lane, London, SW1A 2BB',
            'property_type' => 'semi_detached',
            'purchase_type' => 'purchase',
        ]);

        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Application created successfully'
                 ]);

        $this->assertDatabaseHas('mortgage_applications', [
            'applicant_id' => $this->applicant->id,
            'lender_id' => $this->lender->id,
            'loan_amount' => 270000,
        ]);
    }

    /**
     * Test retrieving mortgage applications list.
     */
    public function test_can_list_mortgage_applications(): void
    {
        // Create test applications
        MortgageApplication::create([
            'lender_id' => $this->lender->id,
            'applicant_id' => $this->applicant->id,
            'property_value' => 300000,
            'loan_amount' => 270000,
            'deposit_amount' => 30000,
            'loan_term_years' => 25,
            'interest_rate' => 4.5,
            'property_address' => 'Test Address',
            'property_type' => 'flat',
            'purchase_type' => 'purchase',
            'status' => 'submitted',
        ]);

        $response = $this->getJson('/api/v1/applications');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data',
                     'meta' => [
                         'current_page',
                         'last_page',
                         'per_page',
                         'total',
                     ]
                 ]);
    }

    /**
     * Test filtering applications by status.
     */
    public function test_can_filter_applications_by_status(): void
    {
        MortgageApplication::create([
            'lender_id' => $this->lender->id,
            'applicant_id' => $this->applicant->id,
            'property_value' => 300000,
            'loan_amount' => 270000,
            'deposit_amount' => 30000,
            'loan_term_years' => 25,
            'interest_rate' => 4.5,
            'property_address' => 'Test Address',
            'property_type' => 'flat',
            'purchase_type' => 'purchase',
            'status' => 'approved',
        ]);

        $response = $this->getJson('/api/v1/applications?status=approved');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data');
    }

    /**
     * Test updating application status.
     */
    public function test_can_update_application_status(): void
    {
        $application = MortgageApplication::create([
            'lender_id' => $this->lender->id,
            'applicant_id' => $this->applicant->id,
            'property_value' => 300000,
            'loan_amount' => 270000,
            'deposit_amount' => 30000,
            'loan_term_years' => 25,
            'interest_rate' => 4.5,
            'property_address' => 'Test Address',
            'property_type' => 'flat',
            'purchase_type' => 'purchase',
            'status' => 'submitted',
        ]);

        $response = $this->patchJson("/api/v1/applications/{$application->id}/status", [
            'status' => 'under_review',
            'notes' => 'Moving to manual review'
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                 ]);

        $this->assertDatabaseHas('mortgage_applications', [
            'id' => $application->id,
            'status' => 'under_review',
        ]);
    }

    /**
     * Test validation errors on invalid input.
     */
    public function test_validation_fails_with_invalid_data(): void
    {
        $response = $this->postJson('/api/v1/applications', [
            'lender_id' => 999, // Non-existent lender
            'applicant_id' => $this->applicant->id,
            'property_value' => -100, // Invalid negative value
        ]);

        $response->assertStatus(422); // Validation error
    }
}
