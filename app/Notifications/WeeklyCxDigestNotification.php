<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Weekly rollup of the commissioning programme across a tenant.
 *
 * Delivered via mail + database every Monday morning (see
 * `app/Console/Commands/SendWeeklyCxDigest.php`). This is the first
 * artefact of a week-in-week-out programme rhythm — Cx directors open
 * their inbox and see a single email showing FPT pass-rate movement,
 * open deficiencies, PFC completion, and upcoming handovers without
 * needing to log in.
 */
class WeeklyCxDigestNotification extends Notification
{
    /**
     * @param  array<string, mixed>  $snapshot
     */
    public function __construct(
        private readonly array $snapshot,
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
        $s = $this->snapshot;
        $url = url('/reports/commissioning');

        $mail = (new MailMessage)
            ->subject(sprintf('Cx Weekly Digest · %s', $s['period_label'] ?? 'Last 7 days'))
            ->greeting(sprintf('Hello %s,', $notifiable->name ?? 'team'))
            ->line('Here is your commissioning programme snapshot for the past week.');

        $mail->line(sprintf(
            '**FPT:** %d runs · **%s%%** pass rate · %d witnessed',
            $s['fpt_total'] ?? 0,
            number_format((float) ($s['fpt_pass_rate'] ?? 0), 1),
            $s['fpt_witnessed'] ?? 0,
        ));

        $mail->line(sprintf(
            '**PFC:** %d completions · **%s%%** clean · %d in-flight',
            $s['pfc_total'] ?? 0,
            number_format((float) ($s['pfc_clean_rate'] ?? 0), 1),
            $s['pfc_in_flight'] ?? 0,
        ));

        $mail->line(sprintf(
            '**Deficiencies:** %d open · %d opened this week · %d closed this week',
            $s['open_deficiencies'] ?? 0,
            $s['deficiencies_opened_this_week'] ?? 0,
            $s['deficiencies_closed_this_week'] ?? 0,
        ));

        if (! empty($s['top_failing_script'])) {
            $mail->line(sprintf(
                '**Top failing script:** %s — %s%% fail rate.',
                $s['top_failing_script']['name'],
                number_format((float) $s['top_failing_script']['fail_rate'], 1),
            ));
        }

        return $mail
            ->action('Open Commissioning Analytics', $url)
            ->line('Drill into trends, aging, and Cx-level breakdown in the analytics workspace.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return array_merge($this->snapshot, [
            'type' => 'cx_weekly_digest',
        ]);
    }
}
