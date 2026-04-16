<?php

namespace App\Notifications;

use App\Models\WorkOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SlaBreachWarning extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly WorkOrder $workOrder,
        private readonly bool $breached,
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
        $subject = $this->breached
            ? "SLA BREACHED: {$this->workOrder->wo_number}"
            : "SLA Warning: {$this->workOrder->wo_number}";

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting("Hello {$notifiable->name},");

        if ($this->breached) {
            $message->line('The SLA deadline for the following work order has been breached.');
        } else {
            $message->line('The SLA deadline for the following work order is approaching (75% elapsed).');
        }

        return $message
            ->line("**WO Number:** {$this->workOrder->wo_number}")
            ->line("**Title:** {$this->workOrder->title}")
            ->line("**Deadline:** {$this->workOrder->sla_deadline?->format('M d, Y h:i A')}")
            ->action('View Work Order', $url)
            ->line('Please take immediate action.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'sla_breach_warning',
            'wo_number' => $this->workOrder->wo_number,
            'title' => $this->workOrder->title,
            'deadline' => $this->workOrder->sla_deadline?->toIso8601String(),
            'breached' => $this->breached,
        ];
    }
}
