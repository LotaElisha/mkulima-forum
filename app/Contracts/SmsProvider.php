<?php

namespace App\Contracts;

use App\Services\Sms\SmsDeliveryResult;

/**
 * The seam between MkulimaForum and whoever actually delivers an SMS.
 *
 * Authentication, OTP and advisory code talks to SmsService; SmsService talks
 * to this interface; only the implementations below it know the difference
 * between Africa's Talking and Twilio. Adding a fourth aggregator — or moving
 * off one that raises its Tanzanian rates — is a new class plus one env var,
 * with no change to the OTP or login code that farmers depend on.
 *
 *   Authentication Service
 *          ↓
 *   OtpService
 *          ↓
 *   SmsService
 *          ↓
 *   SmsProvider  ← this interface
 *          ↓
 *   AfricasTalking | Twilio | Log | (future provider)
 */
interface SmsProvider
{
    /**
     * Deliver one message.
     *
     * Implementations must not throw for ordinary upstream failures — a
     * rejected message, a rate limit, an HTTP 500 from the gateway — and
     * should return a failed SmsDeliveryResult instead. A farmer waiting on a
     * login code should see "code could not be sent, try again", never a 500.
     *
     * @param  string  $phone  E.164, already normalised by SmsService.
     * @param  string  $message  Plain text. Implementations do not truncate;
     *                           multipart billing is the caller's concern.
     */
    public function send(string $phone, string $message): SmsDeliveryResult;

    /**
     * Whether this provider holds every credential it needs to send.
     *
     * Checked before an OTP is generated, so the platform can answer
     * "delivery is unavailable" rather than issuing a code that will never
     * arrive and locking the user out of their own sign-in.
     */
    public function isConfigured(): bool;

    /**
     * Short machine name recorded against every SmsLog row, so delivery
     * problems can be traced to the gateway that was live at the time.
     */
    public function name(): string;
}
