<?php

namespace App\Services\Importer;

use App\User;
use Carbon\Carbon;

/**
 * Matches by email. Creates founder user if missing; allots 'Registered' role
 * additively; returns { user, was_created, plain_password|null }.
 *
 * Password generation (per spec §10.5):
 *   - 16 chars, mixed letters/digits/symbols
 *   - Excludes ambiguous: 0 O l 1 I
 *   - Sent exactly once by email; only bcrypt hash persists.
 */
class UserProvisioner
{
    public function upsertFromImport(array $founderData, int $companyId): array
    {
        $email = mb_strtolower(trim($founderData['email'] ?? ''), 'UTF-8');
        if ($email === '') {
            throw new \InvalidArgumentException('email is required to provision founder');
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $plain = $this->generatePassword();
            $user = User::forceCreate([
                'email'             => $email,
                'first_name_en'     => $founderData['first_name'] ?? null,
                'first_name_ar'     => $founderData['first_name'] ?? null,
                'last_name_en'      => $founderData['last_name']  ?? null,
                'last_name_ar'      => $founderData['last_name']  ?? null,
                'gender'            => $founderData['gender']     ?? null,
                'phone'             => $founderData['phone']      ?? null,
                'country_code'      => $founderData['country_code']?? null,
                'country_id'        => $founderData['country_id'] ?? null,
                'state_id'          => $founderData['state_id']   ?? null,
                'password'          => bcrypt($plain),
                'email_verified_at' => Carbon::now(),
                'HaveCompany'       => 1,
                'company_id'        => $companyId,
            ]);
            if (method_exists($user, 'allotRole')) {
                $user->allotRole('Registered');
            }
            return ['user' => $user->fresh(), 'was_created' => true, 'plain_password' => $plain];
        }

        $updates = array_filter([
            'first_name_en'  => $founderData['first_name'] ?? null,
            'first_name_ar'  => $founderData['first_name'] ?? null,
            'last_name_en'   => $founderData['last_name']  ?? null,
            'last_name_ar'   => $founderData['last_name']  ?? null,
            'gender'         => $founderData['gender']     ?? null,
            'phone'          => $founderData['phone']      ?? null,
            'country_code'   => $founderData['country_code']?? null,
            'country_id'     => $founderData['country_id'] ?? null,
            'state_id'       => $founderData['state_id']   ?? null,
            'company_id'     => $companyId,
            'HaveCompany'    => 1,
        ], fn($v) => $v !== null);
        if (!empty($updates)) {
            $user->forceFill($updates)->save();
        }

        if (method_exists($user, 'allotRole')) {
            $user->allotRole('Registered');
        }
        return ['user' => $user->fresh(), 'was_created' => false, 'plain_password' => null];
    }

    private function generatePassword(): string
    {
        // Excludes ambiguous: 0 O l 1 I
        $chars = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%*?';
        $len = 16;
        $out = '';
        for ($i = 0; $i < $len; $i++) {
            $out .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $out;
    }
}
