<?php

namespace App\Services;

class BrokerVerificationService
{
    /**
     * Initial status for a broker license number at registration.
     *
     * A license number can never be verified automatically: matching a format
     * (e.g. RERA-1234) proves nothing, since anyone can type it. Any license
     * starts as "pending" and only an administrator can mark it "verified"
     * (PUT /api/admin/users/{user}/verify-broker).
     */
    public function verify(?string $licenseNumber): string
    {
        if (empty(trim((string) $licenseNumber))) {
            return 'not_applicable';
        }

        return 'pending';
    }
}
