<?php

namespace App\Services\Verify;

class UssdSessionService
{
    protected VerificationEngine $verificationEngine;

    public function __construct(VerificationEngine $verificationEngine)
    {
        $this->verificationEngine = $verificationEngine;
    }

    /**
     * Process USSD input step (A16).
     * Returns string formatted for USSD gateway (CON / END).
     */
    public function handleUssdRequest(string $sessionId, string $phoneNumber, string $text): string
    {
        $input = trim($text);

        if (empty($input)) {
            return "CON Mkulima Forum Verify\nChagua Huduma:\n1. Kagua Mbegu / Seed\n2. Kagua Mbolea / Fertilizer\n3. Kagua Dawa / Pesticide\n4. Ingiza Namba / Serial Code";
        }

        $parts = explode('*', $input);

        if (count($parts) === 1 && in_array($parts[0], ['1', '2', '3', '4'])) {
            return "CON Ingiza Namba ya Usajili au Serial Code:\n(Enter Registration or Serial Code)";
        }

        // Final step: process code
        $code = end($parts);
        $result = $this->verificationEngine->verify($code, 'ussd');

        $statusText = match ($result['status']) {
            'VERIFIED', 'REGISTERED_SOURCE_CONFIRMED' => "✅ IMETHIBITISHWA: {$result['product']['trade_name']}",
            'RECALLED', 'SUSPENDED' => "⚠️ IMERUDISHWA SOKONI / SUSPENDED: Usitumie bidhaa hii!",
            'SUSPICIOUS' => "❌ SHAKA! Inatiliwa shaka. Usinunue wala kutumia.",
            default => "ℹ️ HAIJAPATIKANA: Namba hii haipo kwenye daftari ya sasa.",
        };

        return "END Mkulima Verify:\n{$statusText}\nSoma zaidi: mkulimaforum.app";
    }
}
