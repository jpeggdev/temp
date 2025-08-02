<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\UuidTrait;
use App\Enum\ReportType;
use App\Repository\QuickBooksReportRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: QuickBooksReportRepository::class)]
#[ORM\Table(name: 'quickbooks_report')]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(fields: ['intacctId', 'date', 'reportId', 'reportType'])]
#[ORM\UniqueConstraint(fields: ['uuid'])]
#[ORM\Index(fields: ['intacctId'])]
class QuickBooksReport
{
    use TimestampableEntity;
    use UuidTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::GUID)]
    private ?string $reportId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $intacctId = null;

    #[ORM\Column(type: 'string', enumType: ReportType::class)]
    private ?ReportType $reportType = null;

    #[ORM\Column(length: 255)]
    private ?string $bucketName = null;

    #[ORM\Column(type: 'text')]
    private ?string $objectKey = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getReportId(): ?string
    {
        return $this->reportId;
    }

    public function setReportId(string $reportId): static
    {
        $this->reportId = $reportId;

        return $this;
    }

    public function getIntacctId(): ?string
    {
        return $this->intacctId;
    }

    public function setIntacctId(?string $intacctId): static
    {
        $this->intacctId = $intacctId;

        return $this;
    }

    public function getReportType(): ?ReportType
    {
        return $this->reportType;
    }

    public function setReportType(ReportType $reportType): static
    {
        $this->reportType = $reportType;

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
}
