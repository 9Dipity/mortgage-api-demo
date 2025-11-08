<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MortgageApplicationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| RESTful API routes for mortgage application management.
| All routes use the /api prefix automatically.
|
*/

Route::prefix('v1')->group(function () {
    
    // Mortgage Applications
    Route::controller(MortgageApplicationController::class)->group(function () {
        Route::get('/applications', 'index');
        Route::get('/applications/{id}', 'show');
        Route::post('/applications', 'store');
        Route::patch('/applications/{id}/status', 'updateStatus');
        Route::get('/applications/{id}/evaluate', 'evaluateApplication');
        Route::post('/applications/{id}/process-decision', 'processAutomatedDecision');
    });
    
    // Additional endpoints can be added here for:
    // - Lenders
    // - Credit checks
    // - Documents
    // - Statistics/Reports
});
