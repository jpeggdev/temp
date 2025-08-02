<?php

namespace App\Entity;

use App\Entity\Trait\TimestampableDateTimeTZTrait;
use App\Entity\Trait\UuidTrait;
use App\Repository\CompanyDataImportJobRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompanyDataImportJobRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(fields: ['uuid'])]
#[ORM\Table(name: 'company_data_import_job')]
#[ORM\Index(fields: ['intacctId'])]
class CompanyDataImportJob
{
    use TimestampableDateTimeTZTrait;
    use UuidTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $isJobsOrInvoiceFile = false;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $isActiveClubMemberFile = false;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $isMemberFile = false;

    #[ORM\Column(length: 255)]
    private ?string $trade = null;

    #[ORM\Column(length: 255)]
    private ?string $software = null;

    #[ORM\Column(length: 255)]
    private ?string $filePath = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column(length: 255)]
    private ?string $progress = null;

    #[ORM\ManyToOne(inversedBy: 'companyDataImportJobs')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?Company $company = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $isProspectsFile = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $dataSource = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $intacctId = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $tag = null;

    #[ORM\Column(name: 'progress_percent', type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $progressPercent = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $rowCount = 0;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $errorMessage = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $logStream = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $readyForUnificationProcessing = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isJobsOrInvoiceFile(): ?bool
    {
        return $this->isJobsOrInvoiceFile;
    }

    public function setJobsOrInvoiceFile(bool $isJobsOrInvoiceFile): static
    {
        $this->isJobsOrInvoiceFile = $isJobsOrInvoiceFile;

        return $this;
    }

    public function isActiveClubMemberFile(): ?bool
    {
        return $this->isActiveClubMemberFile;
    }

    public function setActiveClubMemberFile(bool $isActiveClubMemberFile): static
    {
        $this->isActiveClubMemberFile = $isActiveClubMemberFile;

        return $this;
    }

    public function isMemberFile(): ?bool
    {
        return $this->isMemberFile;
    }

    public function setMemberFile(bool $isMemberFile): static
    {
        $this->isMemberFile = $isMemberFile;

        return $this;
    }

    public function getTrade(): ?string
    {
        return $this->trade;
    }

    public function setTrade(string $trade): static
    {
        $this->trade = $trade;

        return $this;
    }

    public function getSoftware(): ?string
    {
        return $this->software;
    }

    public function setSoftware(string $software): static
    {
        $this->software = $software;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): static
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getProgress(): ?string
    {
        return $this->progress;
    }

    public function setProgress(string $progress): static
    {
        $this->progress = $progress;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function isProspectsFile(): ?bool
    {
        return $this->isProspectsFile;
    }

    public function setProspectsFile(bool $isProspectsFile): static
    {
        $this->isProspectsFile = $isProspectsFile;

        return $this;
    }

    public function getDataSource(): ?string
    {
        return $this->dataSource;
    }

    public function setDataSource(?string $dataSource): static
    {
        $this->dataSource = $dataSource;

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

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(?string $tag): static
    {
        $this->tag = $tag;

        return $this;
    }

    public function getProgressPercent(): ?string
    {
        return $this->progressPercent;
    }

    public function setProgressPercent(?string $progressPercent): self
    {
        $this->progressPercent = $progressPercent;

        return $this;
    }

    public function getRowCount(): ?int
    {
        return $this->rowCount;
    }

    public function setRowCount(int $rowCount): static
    {
        $this->rowCount = $rowCount;

        return $this;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): static
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }

    public function getLogStream(): ?string
    {
        return $this->logStream;
    }

    public function setLogStream(?string $logStream): static
    {
        $this->logStream = $logStream;

        return $this;
    }

    public function isReadyForUnificationProcessing(): ?bool
    {
        return $this->readyForUnificationProcessing;
    }

    public function setReadyForUnificationProcessing(bool $readyForUnificationProcessing): static
    {
        $this->readyForUnificationProcessing = $readyForUnificationProcessing;

        return $this;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }
}
