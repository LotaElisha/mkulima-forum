<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Accounts created before KYC existed were stored as "pending" with no
 * documents, and the submit endpoint treats pending as "already submitted".
 * Those rows were never submitted. This moves only that case to
 * not_submitted. A row that actually has documents stays pending.
 */
final class KycBackfill
{
    public static function notSubmittedWithoutDocuments(): int
    {
        return DB::table('users')
            ->whereNull('kyc_documents')
            ->where('kyc_status', 'pending')
            ->update(['kyc_status' => 'not_submitted']);
    }
}
