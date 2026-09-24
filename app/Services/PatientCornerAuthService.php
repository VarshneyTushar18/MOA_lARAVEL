<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Hash;

class PatientCornerAuthService
{
    public const KEY_USERNAME = 'patient_corner_username';

    public const KEY_PASSWORD_HASH = 'patient_corner_password_hash';

    public const DEFAULT_USERNAME = 'Ltbi@patient';

    public const DEFAULT_PASSWORD = 'accessdata';

    public function ensureDefaults(): void
    {
        if (SiteSetting::getValue(self::KEY_USERNAME) === null) {
            SiteSetting::setValue(self::KEY_USERNAME, self::DEFAULT_USERNAME);
        }

        if (SiteSetting::getValue(self::KEY_PASSWORD_HASH) === null) {
            SiteSetting::setValue(self::KEY_PASSWORD_HASH, Hash::make(self::DEFAULT_PASSWORD));
        }
    }

    public function username(): string
    {
        $this->ensureDefaults();

        return (string) SiteSetting::getValue(self::KEY_USERNAME, self::DEFAULT_USERNAME);
    }

    public function verify(string $username, string $password): bool
    {
        $this->ensureDefaults();

        $storedUsername = $this->username();
        $storedHash = SiteSetting::getValue(self::KEY_PASSWORD_HASH);

        if ($storedHash === null) {
            return false;
        }

        return $username === $storedUsername && Hash::check($password, $storedHash);
    }

    public function updateCredentials(string $username, string $password): void
    {
        SiteSetting::setValue(self::KEY_USERNAME, $username);
        SiteSetting::setValue(self::KEY_PASSWORD_HASH, Hash::make($password));
    }
}
