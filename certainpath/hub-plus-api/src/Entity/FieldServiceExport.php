<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\UuidTrait;
use App\Repository\FieldServiceExportRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: FieldServiceExportRepository::class)]
#[ORM\Table(name: 'field_service_export')]
#[ORM\HasLifecycleCallbacks]
#[ORM\Index(fields: ['intacctId'])]
#[ORM\UniqueConstraint(fields: ['uuid'])]
class FieldServiceExport
{
    use TimestampableEntity;
    use UuidTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $intacctId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fromEmail = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $toEmail = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $subject = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $emailText = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $emailHtml = null;

    /**
     * @var Collection<int, FieldServiceExportAttachment>
     */
    #[ORM\OneToMany(targetEntity: FieldServiceExportAttachment::class, mappedBy: 'fieldServiceExport')]
    private Collection $fieldServiceExportAttachments;

    public function __construct()
    {
        $this->fieldServiceExportAttachments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getFromEmail(): ?string
    {
        return $this->fromEmail;
    }

    public function setFromEmail(?string $fromEmail): static
    {
        $this->fromEmail = $fromEmail;

        return $this;
    }

    public function getToEmail(): ?string
    {
        return $this->toEmail;
    }

    public function setToEmail(?string $toEmail): static
    {
        $this->toEmail = $toEmail;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getEmailText(): ?string
    {
        return $this->emailText;
    }

    public function setEmailText(?string $emailText): static
    {
        $this->emailText = $emailText;

        return $this;
    }

    public function getEmailHtml(): ?string
    {
        return $this->emailHtml;
    }

    public function setEmailHtml(?string $emailHtml): static
    {
        $this->emailHtml = $emailHtml;

        return $this;
    }

    /**
     * @return Collection<int, FieldServiceExportAttachment>
     */
    public function getFieldServiceExportAttachments(): Collection
    {
        return $this->fieldServiceExportAttachments;
    }

    public function addFieldServiceExportAttachment(FieldServiceExportAttachment $fieldServiceExportAttachment): static
    {
        if (!$this->fieldServiceExportAttachments->contains($fieldServiceExportAttachment)) {
            $this->fieldServiceExportAttachments->add($fieldServiceExportAttachment);
            $fieldServiceExportAttachment->setFieldServiceExport($this);
        }

        return $this;
    }

    public function removeFieldServiceExportAttachment(FieldServiceExportAttachment $fieldServiceExportAttachment): static
    {
        if ($this->fieldServiceExportAttachments->removeElement($fieldServiceExportAttachment)) {
            // set the owning side to null (unless already changed)
            if ($fieldServiceExportAttachment->getFieldServiceExport() === $this) {
                $fieldServiceExportAttachment->setFieldServiceExport(null);
            }
        }

        return $this;
    }
}
