<?php

namespace App\Entity;

use App\Repository\EmailTemplateEmailTemplateCategoryMappingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmailTemplateEmailTemplateCategoryMappingRepository::class)]
class EmailTemplateEmailTemplateCategoryMapping
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'emailTemplateEmailTemplateCategoryMappings')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?EmailTemplate $emailTemplate = null;

    #[ORM\ManyToOne(inversedBy: 'emailTemplateEmailTemplateCategoryMappings')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?EmailTemplateCategory $emailTemplateCategory = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmailTemplate(): ?EmailTemplate
    {
        return $this->emailTemplate;
    }

    public function setEmailTemplate(?EmailTemplate $emailTemplate): static
    {
        $this->emailTemplate = $emailTemplate;

        return $this;
    }

    public function getEmailTemplateCategory(): ?EmailTemplateCategory
    {
        return $this->emailTemplateCategory;
    }

    public function setEmailTemplateCategory(?EmailTemplateCategory $emailTemplateCategory): static
    {
        $this->emailTemplateCategory = $emailTemplateCategory;

        return $this;
    }
}
