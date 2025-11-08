<?php

namespace App\Events;

use App\Models\MortgageApplication;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Mortgage Application Submitted Event
 * 
 * Fired when a new mortgage application is submitted.
 * Triggers listeners for credit checks, document verification, etc.
 */
class MortgageApplicationSubmitted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public MortgageApplication $application;

    /**
     * Create a new event instance.
     */
    public function __construct(MortgageApplication $application)
    {
        $this->application = $application;
    }
}
