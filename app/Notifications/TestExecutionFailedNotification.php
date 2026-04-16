<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\TestExecution;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TestExecutionFailedNotification extends Notification
{
    public function __construct(
        private readonly TestExecution $execution,
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
        $url = url("/fpt/executions/{$this->execution->id}");
        $assetName = $this->execution->asset?->name ?? '—';
        $projectName = $this->execution->project?->name ?? '—';

        return (new MailMessage)
            ->error()
            ->subject("FPT Failed: {$this->execution->test_script_name} @ {$assetName}")
            ->greeting("Hello {$notifiable->name},")
            ->line("A Functional Performance Test has **failed** on {$projectName}.")
            ->line("**Script:** {$this->execution->test_script_name} (v{$this->execution->test_script_version})")
            ->line("**Asset:** {$assetName}")
            ->line("**Results:** {$this->execution->pass_count} pass · {$this->execution->fail_count} fail · {$this->execution->pending_count} pending")
            ->action('Review FPT Results', $url)
            ->line('Every failed step has been logged as a deficiency Issue and is ready for remediation.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'fpt_failed',
            'execution_id' => $this->execution->id,
            'script_name' => $this->execution->test_script_name,
            'script_version' => $this->execution->test_script_version,
            'asset_id' => $this->execution->asset_id,
            'asset_name' => $this->execution->asset?->name,
            'project_id' => $this->execution->project_id,
            'project_name' => $this->execution->project?->name,
            'pass_count' => $this->execution->pass_count,
            'fail_count' => $this->execution->fail_count,
        ];
    }
}
