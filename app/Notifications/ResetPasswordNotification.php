<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Password reset link, in the language the farmer chose.
 *
 * The reset page is a first-party web route rather than a deep link so the
 * same mail works from a laptop, a shared phone or the app.
 */
class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $token) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $minutes = (int) config('auth.passwords.users.expire', 60);

        return ($notifiable->preferred_language === 'en')
            ? (new MailMessage)
                ->subject('Reset your MkulimaForum password')
                ->greeting('Hello '.$notifiable->name.',')
                ->line('We received a request to reset the password on your MkulimaForum account.')
                ->action('Reset password', $url)
                ->line("This link expires in {$minutes} minutes and can be used once.")
                ->line('If you did not request this, no action is needed — your password stays unchanged.')
                ->salutation('MkulimaForum')
            : (new MailMessage)
                ->subject('Weka upya nenosiri lako la MkulimaForum')
                ->greeting('Habari '.$notifiable->name.',')
                ->line('Tumepokea ombi la kuweka upya nenosiri la akaunti yako ya MkulimaForum.')
                ->action('Weka nenosiri jipya', $url)
                ->line("Kiungo hiki kitaisha baada ya dakika {$minutes} na kinatumika mara moja tu.")
                ->line('Kama hukuomba hili, hakuna hatua inayohitajika — nenosiri lako halijabadilika.')
                ->salutation('MkulimaForum');
    }
}
