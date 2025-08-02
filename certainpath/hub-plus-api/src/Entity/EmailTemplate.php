<?php

namespace App\Entity;

use App\Repository\EmailTemplateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: EmailTemplateRepository::class)]
#[ORM\HasLifecycleCallbacks]
class EmailTemplate
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $emailSubject = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $emailContent = null;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private ?bool $isActive = true;

    /**
     * @var Collection<int, EmailTemplateEmailTemplateCategoryMapping>
     */
    #[ORM\OneToMany(targetEntity: EmailTemplateEmailTemplateCategoryMapping::class, mappedBy: 'emailTemplate')]
    private Collection $emailTemplateEmailTemplateCategoryMappings;

    public function __construct()
    {
        $this->emailTemplateEmailTemplateCategoryMappings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getEmailSubject(): ?string
    {
        return $this->emailSubject;
    }

    public function setEmailSubject(string $emailSubject): static
    {
        $this->emailSubject = $emailSubject;

        return $this;
    }

    public function getEmailContent(): ?string
    {
        return $this->emailContent;
    }

    public function setEmailContent(string $emailContent): static
    {
        $this->emailContent = $emailContent;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return Collection<int, EmailTemplateCategory>
     */
    public function getEmailTemplateCategories(): Collection
    {
        $categories = new ArrayCollection();

        foreach ($this->emailTemplateEmailTemplateCategoryMappings as $emailTemplateEmailTemplateCategoryMapping) {
            $categories->add($emailTemplateEmailTemplateCategoryMapping->getEmailTemplateCategory());
        }

        return $categories;
    }

    /**
     * @return Collection<int, EmailTemplateEmailTemplateCategoryMapping>
     */
    public function getEmailTemplateEmailTemplateCategoryMappings(): Collection
    {
        return $this->emailTemplateEmailTemplateCategoryMappings;
    }

    public function addEmailTemplateEmailTemplateCategoryMapping(
        EmailTemplateEmailTemplateCategoryMapping $emailTemplateEmailTemplateCategoryMapping,
    ): static {
        if (
            !$this->emailTemplateEmailTemplateCategoryMappings->contains($emailTemplateEmailTemplateCategoryMapping)
        ) {
            $this->emailTemplateEmailTemplateCategoryMappings->add($emailTemplateEmailTemplateCategoryMapping);
            $emailTemplateEmailTemplateCategoryMapping->setEmailTemplate($this);
        }

        return $this;
    }

    public function removeEmailTemplateEmailTemplateCategoryMapping(
        EmailTemplateEmailTemplateCategoryMapping $emailTemplateEmailTemplateCategoryMapping,
    ): static {
        if (
            $this->emailTemplateEmailTemplateCategoryMappings->removeElement($emailTemplateEmailTemplateCategoryMapping)
            && $emailTemplateEmailTemplateCategoryMapping->getEmailTemplate() === $this) {
            $emailTemplateEmailTemplateCategoryMapping->setEmailTemplate(null);
        }

        return $this;
    }
}
