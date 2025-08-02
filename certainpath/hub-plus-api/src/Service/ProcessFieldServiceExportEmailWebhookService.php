<?php

declare(strict_types=1);

namespace App\Service;

use App\Constants\S3Buckets;
use App\Entity\FieldServiceExport;
use App\Entity\FieldServiceExportAttachment;
use App\Repository\CompanyRepository;
use App\Repository\WebhookConfigurationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

readonly class ProcessFieldServiceExportEmailWebhookService
{
    public function __construct(
        private AmazonS3Service $amazonS3Service,
        private EntityManagerInterface $entityManager,
        private CompanyRepository $companyRepository,
        private MailerInterface $mailer,
        private LoggerInterface $logger,
        private WebhookConfigurationRepository $webhookConfigurationRepository,
    ) {
    }

    public function authenticateWebhook(Request $request): bool
    {
        $mandrillSignature = $request->headers->get('X-Mandrill-Signature');
        $postParams = $request->request->all();

        $webhookConfig = $this->webhookConfigurationRepository->findOneBy([
            'url' => $request->getUri(),
        ]);

        if (!$webhookConfig) {
            $this->logger->error('Webhook configuration not found for URL: '.$request->getUri());

            return false;
        }

        $signedData = $webhookConfig->getUrl();

        ksort($postParams);
        foreach ($postParams as $key => $value) {
            $signedData .= $key.$value;
        }

        $expectedSignature = base64_encode(hash_hmac('sha1', $signedData, $webhookConfig->getKey(), true));

        return hash_equals($expectedSignature, $mandrillSignature ?? '');
    }

    public function processWebhook(Request $request): void
    {
        $payload = $request->request->get('mandrill_events');
        $events = json_decode($payload, true);

        if (!$events) {
            throw new \InvalidArgumentException('Invalid webhook payload.');
        }

        foreach ($events as $event) {
            if ('inbound' !== $event['event']) {
                continue;
            }

            $this->processInboundEmail($event['msg']);
        }
    }

    private function processInboundEmail(array $msg): void
    {
        $fromEmail = $msg['from_email'];
        $toEmails = array_column($msg['to'], 0);
        $attachments = $msg['attachments'] ?? [];
        $emailSubject = $msg['subject'] ?? '';
        $emailText = $msg['text'] ?? '';
        $emailHtml = $msg['html'] ?? '';

        foreach ($toEmails as $toEmail) {
            if (!$this->isValidSender($fromEmail, $toEmail)) {
                $this->logger->warning('Invalid sender: '.$fromEmail);
                continue;
            }

            $tenantId = $this->getTenantId($fromEmail, $toEmail);

            if (!$tenantId) {
                $this->logger->warning('Tenant ID not found for email: '.$fromEmail);
                continue;
            }

            $softwareType = $this->getSoftwareType($tenantId);

            $fieldServiceExport = new FieldServiceExport();
            $fieldServiceExport->setIntacctId($tenantId);
            $fieldServiceExport->setFromEmail($fromEmail);
            $fieldServiceExport->setToEmail($toEmail);
            $fieldServiceExport->setSubject($emailSubject);
            $fieldServiceExport->setEmailText($emailText);
            $fieldServiceExport->setEmailHtml($emailHtml);

            foreach ($attachments as $attachmentData) {
                $originalFilename = $attachmentData['name'] ?? 'attachment';
                $newFilename = $this->generateNewFilename($tenantId, $softwareType, $originalFilename);
                $fileContent = $attachmentData['content'];

                if ($attachmentData['base64']) {
                    $fileContent = base64_decode($fileContent, true);
                    if (false === $fileContent || '' === $fileContent) {
                        continue;
                    }
                }

                $fileType = $attachmentData['type'] ?? 'application/octet-stream';
                if (in_array($fileType, ['text/csv', 'text/plain'])) {
                    $fileContent = str_replace("\r\r\n", "\n", $fileContent);
                    $fileContent = str_replace(["\r\n", "\r"], "\n", $fileContent);
                }

                $contentType = $attachmentData['type'] ?? 'application/octet-stream';

                if ($this->performSanityCheck($fileContent, $originalFilename)) {
                    $objectKey = $newFilename;
                    $this->amazonS3Service->uploadFile(
                        S3Buckets::MEMBERSHIP_FILES_BUCKET,
                        $fileContent,
                        'field-services/'.$objectKey,
                        $contentType
                    );

                    $attachmentEntity = new FieldServiceExportAttachment();
                    $attachmentEntity->setOriginalFilename($originalFilename);
                    $attachmentEntity->setBucketName(S3Buckets::MEMBERSHIP_FILES_BUCKET);
                    $attachmentEntity->setObjectKey($objectKey);
                    $attachmentEntity->setContentType($contentType);
                    $attachmentEntity->setFieldServiceExport($fieldServiceExport);
                    $this->entityManager->persist($attachmentEntity);

                    $fieldServiceExport->addFieldServiceExportAttachment($attachmentEntity);
                } else {
                    $this->logger->error('Failed sanity check: '.$originalFilename);
                }
            }

            $this->entityManager->persist($fieldServiceExport);
            $this->entityManager->flush();

            $this->sendConfirmationEmail($fromEmail, $fieldServiceExport);
        }
    }

    private function isValidSender(string $fromEmail, string $toEmail): bool
    {
        $tenantId = $this->getTenantId($fromEmail, $toEmail);

        return null !== $tenantId;
    }

    private function getTenantId(string $fromEmail, string $toEmail): ?string
    {
        $toEmailParts = explode('@', $toEmail);
        $intacctId = $toEmailParts[0];

        $company = $this->companyRepository->findOneBy(['intacctId' => $intacctId]);

        if ($company) {
            return $company->getIntacctId();
        }

        return $this->findTenantIdByDomain($fromEmail);
    }

    private function findTenantIdByDomain(string $fromEmail): ?string
    {
        $fromEmailDomain = strtolower(explode('@', $fromEmail)[1]);
        $fromEmailDomain = preg_replace('/^www\./', '', $fromEmailDomain);

        $company = $this->companyRepository->findOneByEmailDomain($fromEmailDomain);

        return $company ? $company->getIntacctId() : null;
    }

    private function getSoftwareType(string $tenantId): ?string
    {
        $company = $this->companyRepository->findOneBy([
            'intacctId' => $tenantId,
        ]);

        if ($company && $company->getFieldServiceSoftware()) {
            return $company->getFieldServiceSoftware()->getName();
        }

        return null;
    }

    private function generateNewFilename(string $tenantId, ?string $softwareType, string $originalFilename): string
    {
        $timestamp = time();
        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
        $sanitizedFilename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', pathinfo($originalFilename, PATHINFO_FILENAME));
        $softwareTypePart = $softwareType ? "{$softwareType}_" : '';

        return "{$tenantId}_{$softwareTypePart}{$sanitizedFilename}_{$timestamp}.{$extension}";
    }

    private function performSanityCheck(string $fileContent, string $filename): bool
    {
        if (empty($fileContent)) {
            $this->logger->error('File content is empty: '.$filename);

            return false;
        }

        $maxFileSize = 5 * 1024 * 1024;
        if (strlen($fileContent) > $maxFileSize) {
            $this->logger->error('File exceeds size limit: '.$filename);

            return false;
        }

        $allowedExtensions = ['csv', 'xml', 'json'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions, true)) {
            $this->logger->error('Invalid file extension: '.$filename);

            return false;
        }

        return true;
    }

    private function sendConfirmationEmail(string $toEmail, FieldServiceExport $fieldServiceExport): void
    {
        try {
            $attachmentList = array_map(function (FieldServiceExportAttachment $attachment) {
                return $attachment->getOriginalFilename();
            }, $fieldServiceExport->getFieldServiceExportAttachments()->toArray());

            $attachmentNames = implode(', ', $attachmentList);

            $emailBody = "Your files '{$attachmentNames}' have been uploaded and are being processed.";

            $email = (new Email())
                ->from('noreply@yourdomain.com')
                ->to($toEmail)
                ->subject('File Upload Confirmation')
                ->text($emailBody);

            $this->mailer->send($email);
        } catch (\Exception $e) {
            $this->logger->error('Error sending confirmation email: '.$e->getMessage());
        }
    }
}
