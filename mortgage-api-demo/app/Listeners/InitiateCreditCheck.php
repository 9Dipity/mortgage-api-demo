<?php

namespace App\Listeners;

use App\Events\MortgageApplicationSubmitted;
use App\Services\CreditCheckService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Initiate Credit Check Listener
 * 
 * Automatically initiates a credit check when a mortgage application is submitted.
 * Runs asynchronously via queue for better performance.
 */
class InitiateCreditCheck implements ShouldQueue
{
    use InteractsWithQueue;

    protected CreditCheckService $creditCheckService;

    /**
     * Create the event listener.
     */
    public function __construct(CreditCheckService $creditCheckService)
    {
        $this->creditCheckService = $creditCheckService;
    }

    /**
     * Handle the event.
     */
    public function handle(MortgageApplicationSubmitted $event): void
    {
        // Initiate credit check for the applicant
        $this->creditCheckService->performCreditCheck(
            $event->application
        );

        // Update application status
        $event->application->updateStatus(
            MortgageApplication::STATUS_CREDIT_CHECK,
            'Credit check initiated'
        );
    }

    /**
     * Determine if the listener should be queued.
     */
    public function shouldQueue(MortgageApplicationSubmitted $event): bool
    {
        return $event->application->status === MortgageApplication::STATUS_SUBMITTED;
    }
}
