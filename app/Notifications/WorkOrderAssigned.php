<?php

namespace App\Notifications;

use App\Models\WorkOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkOrderAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly WorkOrder $workOrder,
        private readonly string $assignedByName,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url("/work-orders/{$this->workOrder->id}");

        return (new MailMessage)
            ->subject("Work Order Assigned: {$this->workOrder->wo_number}")
            ->greeting("Hello {$notifiable->name},")
            ->line('A work order has been assigned to you.')
            ->line("**WO Number:** {$this->workOrder->wo_number}")
            ->line("**Title:** {$this->workOrder->title}")
            ->line('**Priority:** '.ucfirst($this->workOrder->priority))
            ->line("**Assigned By:** {$this->assignedByName}")
            ->action('View Work Order', $url)
            ->line('Please review and begin work as soon as possible.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'work_order_assigned',
            'wo_number' => $this->workOrder->wo_number,
            'title' => $this->workOrder->title,
            'priority' => $this->workOrder->priority,
            'assigned_by' => $this->assignedByName,
        ];
    }
}
