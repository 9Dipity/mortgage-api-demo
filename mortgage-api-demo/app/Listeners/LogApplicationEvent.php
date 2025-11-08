<?php

namespace App\Listeners;

use App\Events\ApplicationStatusChanged;
use App\Models\ApplicationEvent;

/**
 * Log Application Event Listener
 * 
 * Creates an audit trail entry whenever application status changes.
 * Ensures compliance and full history tracking.
 */
class LogApplicationEvent
{
    /**
     * Handle the event.
     */
    public function handle(ApplicationStatusChanged $event): void
    {
        ApplicationEvent::create([
            'mortgage_application_id' => $event->application->id,
            'event_type' => 'status_change',
            'old_value' => $event->oldStatus,
            'new_value' => $event->newStatus,
            'description' => "Status changed from {$event->oldStatus} to {$event->newStatus}",
            'metadata' => json_encode([
                'timestamp' => now(),
                'application_id' => $event->application->id,
                'applicant_id' => $event->application->applicant_id,
            ]),
        ]);
    }
}
