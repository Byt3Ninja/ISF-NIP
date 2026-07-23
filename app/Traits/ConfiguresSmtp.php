<?php

namespace App\Traits;

use App\Settings;
use App\Services\MailConfig;
use Illuminate\Support\Facades\Log;

trait ConfiguresSmtp
{
    /**
     * Configure SMTP settings for the notification
     * 
     * @param mixed $smtpSettings
     * @return array
     */
    protected function configureSmtp($smtpSettings): array
    {
        $mailConfig = [];
        
        if ($smtpSettings && ($smtpSettings->smtp_host || $smtpSettings->email_provider === 'resend')) {
            try {
                // Get mail configuration (SMTP or Resend)
                $mailConfig = $this->getSmtpConfig($smtpSettings);

                if ($mailConfig) {
                    // Set new mail config (forgetMailers() is called inside MailConfig::set())
                    MailConfig::set($mailConfig);

                    \Log::info('Mail configuration applied:', [
                        'transport' => $mailConfig['transport'] ?? 'smtp',
                        'host' => $mailConfig['host'] ?? null,
                        'port' => $mailConfig['port'] ?? null,
                        'username' => $mailConfig['username'] ?? null,
                        'organization_id' => $smtpSettings->organization_id ?? null
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Failed to configure mail:', [
                    'error' => $e->getMessage(),
                    'organization_id' => $smtpSettings->organization_id ?? null
                ]);
            }
        }
        else
        {
            $setting = Settings::first();
            // get platform settings
            $mailConfig = [
                'transport' => 'smtp',
                'host' => $setting->host,
                'port' => $setting->Port,
                'encryption' => $setting->encryption,
                'username' => $setting->Mail,
                'password' => $setting->Mailpassword,
                'timeout' => 30,
                'auth_mode' => null,
                'from_name' => $setting->mail_from_name ?? $setting->Platform_Name ?? config('app.name'),
                'from_address' => $setting->mail_from_address ?? $setting->Mail,
            ];
            MailConfig::set($mailConfig);
        }
        return $mailConfig;
    }
    
    /**
     * Get the SMTP configuration
     * 
     * @param mixed $smtpSettings
     * @return array|null
     */
    protected function getSmtpConfig($smtpSettings)
    {
        if (!$smtpSettings) {
            return null;
        }

        try {
            // Resend provider
            if ($smtpSettings->email_provider === 'resend' && $smtpSettings->resend_api_key) {
                $fromAddress = $smtpSettings->smtp_from_address ?? config('mail.from.address', 'noreply@trsana.net');
                $fromName = $smtpSettings->smtp_from_name ?? config('app.name');

                \Log::info('Using organization Resend configuration:', [
                    'from_address' => $fromAddress,
                    'from_name' => $fromName,
                    'organization_id' => $smtpSettings->organization_id ?? null
                ]);

                return [
                    'transport' => 'resend',
                    'api_key' => $smtpSettings->resend_api_key,
                    'from_address' => $fromAddress,
                    'from_name' => $fromName,
                ];
            }

            // SMTP provider — if no host, return null to use default
            if (!$smtpSettings->smtp_host) {
                return null;
            }
            // Get the from address that matches the SMTP username to avoid authentication issues
            // Use SMTP username as from address to ensure authentication compatibility
            // Only use custom from_address if it matches the username domain (some servers allow this)
            $smtpUsername = $smtpSettings->smtp_username;
            $customFromAddress = $smtpSettings->smtp_from_address ?? null;
            
            // Extract domain from SMTP username
            $smtpDomain = null;
            if ($smtpUsername && str_contains($smtpUsername, '@')) {
                $smtpDomain = substr(strrchr($smtpUsername, "@"), 1);
            }
            
            // Use custom from address only if it's from the same domain as SMTP username
            // Otherwise, use SMTP username to avoid authentication errors
            if ($customFromAddress && $smtpDomain && str_ends_with($customFromAddress, '@' . $smtpDomain)) {
                $fromAddress = $customFromAddress;
            } else {
                $fromAddress = $smtpUsername; // Use the authenticated email as from address
            }
            
            $fromName = $smtpSettings->smtp_from_name ?? config('app.name');

            $mailConfig = [
                'transport' => 'smtp',
                'host' => $smtpSettings->smtp_host,
                'port' => $smtpSettings->smtp_port,
                'encryption' => $smtpSettings->smtp_encryption,
                'username' => $smtpSettings->smtp_username,
                'password' => $smtpSettings->smtp_password,
                'timeout' => 30,
                'auth_mode' => null,
                'from_address' => $fromAddress,
                'from_name' => $fromName,
            ];

            // Log the SMTP settings being used
            \Log::info('Using organization SMTP Configuration:', [
                'host' => $smtpSettings->smtp_host,
                'port' => $smtpSettings->smtp_port,
                'username' => $smtpSettings->smtp_username,
                'from_address' => $fromAddress,
                'from_name' => $fromName,
                'organization_id' => $smtpSettings->organization_id ?? null
            ]);
            
            return $mailConfig;
        } catch (\Exception $e) {
            \Log::error('Failed to configure SMTP:', [
                'error' => $e->getMessage(),
                'organization_id' => $smtpSettings->organization_id ?? null
            ]);
            return null;
        }
    }
    
    /**
     * Get the from address and name for emails
     * 
     * @param mixed $smtpSettings
     * @return array [fromAddress, fromName]
     */
    /**
     * Build the organization data array for email template branding.
     * Returns an array of variables that email views can use for org identity.
     *
     * @param mixed $organization
     * @return array
     */
    protected function buildEmailOrganizationData($organization): array
    {
        $setting = Settings::first();
        $defaultName = $setting ? ( $setting->Platform_Name ?? config('app.name') ) : config('app.name');

        return [
            'org_name'      => $organization?->name ?? $defaultName,
            'org_logo_url'  => $organization?->image_url ?? null,
            'org_address'   => $organization?->address ?? null,
            'org_website'   => $organization?->website_url ?? null,
            'org_linkedin'  => $organization?->linkedin_company_url ?? null,
            'org_twitter'   => $organization?->twitter_handle ?? null,
            'org_facebook'  => $organization?->facebook_page_url ?? null,
            'org_instagram' => $organization?->instagram_profile_url ?? null,
        ];
    }

    protected function getFromAddress($smtpSettings): array
    {
        $setting = Settings::first();
        $app_name = config('mail.from.name', config('app.name'));
        $app_address = config('mail.from.address', 'noreply@trsana.net');
        $fromName = $setting ? ( $setting->mail_from_name ?? ( $setting->Platform_Name ?? $app_name ) ) : $app_name;
        
        // Default values from config
        $fromAddress = $setting ? ( $setting->mail_from_address ?? $setting->Mail ?? $app_address ) : $app_address;
        

        // If we have valid settings, use them
        if ($smtpSettings) {
            // For Resend, use from_address directly (domain verification is Resend's responsibility)
            if ($smtpSettings->email_provider === 'resend') {
                if (!empty($smtpSettings->smtp_from_address)) {
                    $fromAddress = $smtpSettings->smtp_from_address;
                }
                if (!empty($smtpSettings->smtp_from_name)) {
                    $fromName = $smtpSettings->smtp_from_name;
                } elseif (!empty($smtpSettings->organization->name)) {
                    $fromName = $smtpSettings->organization->name;
                }

                \Log::info('Sending email from (Resend):', [
                    'address' => $fromAddress,
                    'name' => $fromName,
                    'organization_id' => $smtpSettings->organization_id ?? null
                ]);

                return [
                    'address' => $fromAddress,
                    'name' => $fromName
                ];
            }

            // SMTP: Use SMTP username as from address to ensure authentication compatibility
            // Only use custom from_address if it matches the username domain
            $smtpUsername = $smtpSettings->smtp_username;
            $customFromAddress = $smtpSettings->smtp_from_address ?? null;
            
            if ($smtpUsername) {
                // Extract domain from SMTP username
                $smtpDomain = null;
                if (str_contains($smtpUsername, '@')) {
                    $smtpDomain = substr(strrchr($smtpUsername, "@"), 1);
                }
                
                // Use custom from address only if it's from the same domain as SMTP username
                // Otherwise, use SMTP username to avoid authentication errors
                if ($customFromAddress && $smtpDomain && str_ends_with($customFromAddress, '@' . $smtpDomain)) {
                    $fromAddress = $customFromAddress;
                } else {
                    $fromAddress = $smtpUsername; // Use the authenticated email as from address
                }
            } elseif (!empty($customFromAddress)) {
                // Fallback to custom from address if no SMTP username
                $fromAddress = $customFromAddress;
            }
            
            // Use organization name if available
            if (!empty($smtpSettings->organization->name)) {
                $fromName = $smtpSettings->organization->name;
            }
            
            // Override with specific from name if set
            if (!empty($smtpSettings->smtp_from_name)) {
                $fromName = $smtpSettings->smtp_from_name;
            }
        }

        \Log::info('Sending email from:', [
            'address' => $fromAddress,
            'name' => $fromName,
            'organization_id' => $smtpSettings->organization_id ?? null
        ]);

        return [
            'address' => $fromAddress,
            'name' => $fromName
        ];
    }
}
