<?php

namespace App\Events;

use App\Models\MortgageApplication;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Application Status Changed Event
 * 
 * Fired when an application status changes.
 * Used for audit trail and notifications.
 */
class ApplicationStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public MortgageApplication $application;
    public string $oldStatus;
    public string $newStatus;

    /**
     * Create a new event instance.
     */
    public function __construct(
        MortgageApplication $application,
        string $oldStatus,
        string $newStatus
    ) {
        $this->application = $application;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }
}
