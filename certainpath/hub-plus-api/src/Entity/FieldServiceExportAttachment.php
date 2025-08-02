<?php

namespace App\Entity;

use App\Entity\Trait\UuidTrait;
use App\Repository\FieldServiceExportAttachmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: FieldServiceExportAttachmentRepository::class)]
#[ORM\Table(name: 'field_service_export_attachment')]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(fields: ['uuid'])]
class FieldServiceExportAttachment
{
    use TimestampableEntity;
    use UuidTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $originalFilename = null;

    #[ORM\Column(length: 255)]
    private ?string $bucketName = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $objectKey = null;

    #[ORM\Column(length: 255)]
    private ?string $contentType = null;

    #[ORM\ManyToOne(inversedBy: 'fieldServiceExportAttachments')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?FieldServiceExport $fieldServiceExport = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOriginalFilename(): ?string
    {
        return $this->originalFilename;
    }

    public function setOriginalFilename(string $originalFilename): static
    {
        $this->originalFilename = $originalFilename;

        return $this;
    }

    public function getBucketName(): ?string
    {
        return $this->bucketName;
    }

    public function setBucketName(string $bucketName): static
    {
        $this->bucketName = $bucketName;

        return $this;
    }

    public function getObjectKey(): ?string
    {
        return $this->objectKey;
    }

    public function setObjectKey(string $objectKey): static
    {
        $this->objectKey = $objectKey;

        return $this;
    }

    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    public function setContentType(string $contentType): static
    {
        $this->contentType = $contentType;

        return $this;
    }

    public function getFieldServiceExport(): ?FieldServiceExport
    {
        return $this->fieldServiceExport;
    }

    public function setFieldServiceExport(?FieldServiceExport $fieldServiceExport): static
    {
        $this->fieldServiceExport = $fieldServiceExport;

        return $this;
    }
}
