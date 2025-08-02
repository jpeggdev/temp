<?php

declare(strict_types=1);

namespace App\DTO\Response;

use App\Entity\FieldServiceExport;

class FieldServiceExportResponseDTO
{
    /**
     * @param FieldServiceExportAttachmentResponseDTO[] $attachments
     */
    public function __construct(
        public string $uuid,
        public string $intacctId,
        public ?string $fromEmail,
        public ?string $toEmail,
        public ?string $subject,
        public ?string $emailText,
        public ?string $emailHtml,
        /** @var FieldServiceExportAttachmentResponseDTO[] */
        public array $attachments,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    public static function fromFieldServiceExport(FieldServiceExport $export): self
    {
        $attachments = array_map(
            fn ($attachment) => FieldServiceExportAttachmentResponseDTO::fromFieldServiceExportAttachment($attachment),
            $export->getFieldServiceExportAttachments()->toArray()
        );

        return new self(
            $export->getUuid(),
            $export->getIntacctId(),
            $export->getFromEmail(),
            $export->getToEmail(),
            $export->getSubject(),
            $export->getEmailText(),
            $export->getEmailHtml(),
            $attachments,
            $export->getCreatedAt()->format('Y-m-d H:i:s'),
            $export->getUpdatedAt()->format('Y-m-d H:i:s'),
        );
    }
}
