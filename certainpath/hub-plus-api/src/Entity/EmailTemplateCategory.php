<?php

namespace App\Entity;

use App\Repository\EmailTemplateCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: EmailTemplateCategoryRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_email_template_category_name', columns: ['name'])]
#[ORM\HasLifecycleCallbacks]
class EmailTemplateCategory
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $displayedName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Color $color = null;

    /**
     * @var Collection<int, EmailTemplateEmailTemplateCategoryMapping>
     */
    #[ORM\OneToMany(targetEntity: EmailTemplateEmailTemplateCategoryMapping::class, mappedBy: 'emailTemplateCategory')]
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

    public function getDisplayedName(): ?string
    {
        return $this->displayedName;
    }

    public function setDisplayedName(string $displayedName): static
    {
        $this->displayedName = $displayedName;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getColor(): ?Color
    {
        return $this->color;
    }

    public function setColor(?Color $color): static
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @return Collection<int, EmailTemplateEmailTemplateCategoryMapping>
     */
    public function getEmailTemplateEmailTemplateCategoryMappings(): Collection
    {
        return $this->emailTemplateEmailTemplateCategoryMappings;
    }

    public function addEmailTemplateEmailTemplateCategoryMapping(EmailTemplateEmailTemplateCategoryMapping $emailTemplateEmailTemplateCategoryMapping): static
    {
        if (!$this->emailTemplateEmailTemplateCategoryMappings->contains($emailTemplateEmailTemplateCategoryMapping)) {
            $this->emailTemplateEmailTemplateCategoryMappings->add($emailTemplateEmailTemplateCategoryMapping);
            $emailTemplateEmailTemplateCategoryMapping->setEmailTemplateCategory($this);
        }

        return $this;
    }

    public function removeEmailTemplateEmailTemplateCategoryMapping(EmailTemplateEmailTemplateCategoryMapping $emailTemplateEmailTemplateCategoryMapping): static
    {
        if ($this->emailTemplateEmailTemplateCategoryMappings->removeElement($emailTemplateEmailTemplateCategoryMapping)) {
            // set the owning side to null (unless already changed)
            if ($emailTemplateEmailTemplateCategoryMapping->getEmailTemplateCategory() === $this) {
                $emailTemplateEmailTemplateCategoryMapping->setEmailTemplateCategory(null);
            }
        }

        return $this;
    }
}
