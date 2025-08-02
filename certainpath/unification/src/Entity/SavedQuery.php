<?php

namespace App\Entity;

use App\Repository\SavedQueryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SavedQueryRepository::class)]
#[ORM\HasLifecycleCallbacks]
class SavedQuery
{
    use Traits\StatusEntity;
    use Traits\TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $dql = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $recordCount = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastRun = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $entityType = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $parameters = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $firstResult = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $maxResults = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    public function __construct()
    {
        $this->setName(
            self::generateName()
        );
    }

    private static function generateName()
    {
        return sprintf(
            'Saved Query %s',
            date_create()->format('Y-m-d h:i:s')
        );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntityType(): ?string
    {
        return $this->entityType;
    }

    public function setEntityType(string $entityType): static
    {
        $this->entityType = $entityType;

        return $this;
    }

    public function getDql(): ?string
    {
        return $this->dql;
    }

    public function setDql(string $dql): static
    {
        $this->dql = $dql;

        return $this;
    }

    public function getRecordCount(): ?int
    {
        return $this->recordCount;
    }

    public function setRecordCount(?int $recordCount): static
    {
        $this->recordCount = $recordCount;

        return $this;
    }

    public function getLastRun(): ?\DateTimeImmutable
    {
        return $this->lastRun;
    }

    public function setLastRun(?\DateTimeImmutable $lastRun): static
    {
        $this->lastRun = $lastRun;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getParameters(): array
    {
        return json_decode((string)$this->parameters, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws \JsonException
     */
    public function setParameters(array $parameters): static
    {
        $this->parameters = json_encode($parameters, JSON_THROW_ON_ERROR);

        return $this;
    }

    public function getFirstResult(): ?int
    {
        return $this->firstResult;
    }

    public function setFirstResult(?int $firstResult): static
    {
        $this->firstResult = $firstResult;

        return $this;
    }

    public function getMaxResults(): ?int
    {
        return $this->maxResults;
    }

    public function setMaxResults(?int $maxResults): static
    {
        $this->maxResults = $maxResults;

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
}
