<?php

namespace App\Client;

use MailchimpTransactional\ApiClient;

class MailchimpTransactionalClient
{
    private ApiClient $mailchimpClient;

    public function __construct(string $apiKey)
    {
        $this->mailchimpClient = new ApiClient();
        $this->mailchimpClient->setApiKey($apiKey);
    }

    public function pingApi(): array
    {
        return $this->mailchimpClient->users->ping();
    }

    public function publishTemplate(string $templateName): array
    {
        return $this->mailchimpClient->templates->publish([
            'name' => $templateName,
        ]);
    }

    public function setResponseFormat(string $format = 'json'): void
    {
        $this->mailchimpClient->setDefaultOutputFormat($format);
    }

    public function sendEmail(
        string $fromEmail,
        array $emailRecipients,
        string $subject,
        string $htmlContent,
        string $textContent = '',
        array $metadata = [],
        bool $trackOpens = true,
        bool $trackClicks = true,
    ): array {
        $formattedRecipients = $this->formatRecipients($emailRecipients);

        $message = $this->buildMessage(
            $fromEmail,
            $subject,
            $htmlContent,
            $textContent,
            $formattedRecipients,
            $metadata,
            $trackOpens,
            $trackClicks
        );

        return $this->mailchimpClient->messages->send(['message' => $message]);
    }

    private function buildMessage(
        string $fromEmail,
        string $subject,
        string $htmlContent,
        string $textContent,
        array $formattedRecipients,
        array $metadata = [],
        bool $trackOpens = true,
        bool $trackClicks = true,
    ): array {
        return [
            'from_email' => $fromEmail,
            'subject' => $subject,
            'html' => $htmlContent,
            'text' => $textContent,
            'to' => $formattedRecipients,
            'metadata' => $metadata,
            'track_opens' => $trackOpens,
            'track_clicks' => $trackClicks,
        ];
    }

    private function formatRecipients(array $emailRecipients): array
    {
        return array_map(static fn ($email) => ['email' => $email], $emailRecipients);
    }
}
