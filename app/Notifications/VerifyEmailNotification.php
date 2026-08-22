<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

/**
 * Email ownership proof, in the language the farmer chose.
 *
 * Queued so a slow or unreachable SMTP host can never block the registration
 * response — on a Tanzanian mobile connection an inline send would show up as
 * a "frozen" sign-up button.
 */
class VerifyEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  string|null  $emailOverride  Address being proved, when it differs
     *                                      from users.email (i.e. an email change
     *                                      staged in users.pending_email).
     */
    public function __construct(private readonly ?string $emailOverride = null) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $address = $this->emailOverride ?? $notifiable->getEmailForVerification();
        $url = $this->verificationUrl($notifiable, $address);
        $minutes = (int) config('auth.verification.expire', 60);

        return ($notifiable->preferred_language === 'en')
            ? (new MailMessage)
                ->subject('Confirm your MkulimaForum email address')
                ->greeting('Hello '.$notifiable->name.',')
                ->line('Confirm this email address to secure your MkulimaForum account and enable password recovery.')
                ->action('Confirm email address', $url)
                ->line("This link expires in {$minutes} minutes.")
                ->line('If you did not create this account, you can ignore this message.')
                ->salutation('MkulimaForum')
            : (new MailMessage)
                ->subject('Thibitisha barua pepe yako ya MkulimaForum')
                ->greeting('Habari '.$notifiable->name.',')
                ->line('Thibitisha barua pepe hii ili kulinda akaunti yako ya MkulimaForum na kuwezesha kurejesha nenosiri.')
                ->action('Thibitisha barua pepe', $url)
                ->line("Kiungo hiki kitaisha baada ya dakika {$minutes}.")
                ->line('Kama hukufungua akaunti hii, puuza ujumbe huu.')
                ->salutation('MkulimaForum');
    }

    /**
     * A temporary signed URL: the signature covers the user id, the address
     * hash and the expiry, so the link cannot be edited to verify a different
     * address or replayed after it lapses.
     */
    protected function verificationUrl(object $notifiable, string $address): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes((int) config('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($address),
            ]
        );
    }
}
