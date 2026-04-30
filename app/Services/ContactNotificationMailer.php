<?php

namespace App\Services;

use App\Mail\ContactMessageNotification;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class ContactNotificationMailer
{
    public function send(array $contactData): void
    {
        $smtpConfig = DB::table('mailer_creds')->first();

        if (! $smtpConfig) {
            throw new RuntimeException('SMTP notification settings are not configured.');
        }

        $username = trim((string) ($smtpConfig->username ?? ''));
        $password = (string) ($smtpConfig->password ?? '');
        $recipient = trim((string) ($smtpConfig->receiver_id ?? ''));
        $host = trim((string) ($smtpConfig->hosts ?? '')) ?: 'smtp.zohocloud.ca';
        $port = (int) (trim((string) ($smtpConfig->port ?? '')) ?: 587);

        if ($username === '' || $password === '' || $recipient === '') {
            throw new RuntimeException('SMTP notification settings are incomplete.');
        }

        config()->set('mail.mailers.smtp', [
            'transport' => 'smtp',
            'scheme' => 'tls',
            'url' => null,
            'host' => $host,
            'port' => $port,
            'username' => $username,
            'password' => $password,
            'timeout' => null,
            'local_domain' => parse_url((string) config('app.url', 'http://localhost'), PHP_URL_HOST) ?: 'localhost',
        ]);

        app(MailManager::class)->purge('smtp');

        Mail::mailer('smtp')
            ->to($recipient)
            ->send(new ContactMessageNotification($contactData, $username));
    }
}
