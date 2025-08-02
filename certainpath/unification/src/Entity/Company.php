<?php

namespace App\Entity;

use App\Repository\CompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use function App\Functions\app_lower;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Index(name: 'company_company_identifier_idx', columns: ['identifier'])]

class Company
{
    use Traits\StatusEntity;
    use Traits\TimestampableEntity;

    public const HOST_SYSTEM_TAG = 'UNIF';

    public const SYSTEM_TAGS = [
        self::HOST_SYSTEM_TAG,
        'HUB',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, unique: true)]
    private ?string $identifier = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $name = null;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'companies')]
    private Collection $users;

    /**
     * @var Collection<int, Customer>
     */
    #[ORM\OneToMany(targetEntity: Customer::class, mappedBy: 'company')]
    private Collection $customers;

    /**
     * @var Collection<int, Campaign>
     */
    #[ORM\OneToMany(targetEntity: Campaign::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $campaigns;

    /**
     * @var Collection<int, Prospect>
     */
    #[ORM\OneToMany(targetEntity: Prospect::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $prospects;

    /**
     * @var Collection<int, Trade>
     */
    #[ORM\ManyToMany(targetEntity: Trade::class)]
    private Collection $trades;

    /**
     * @var Collection<int, Report>
     */
    #[ORM\OneToMany(targetEntity: Report::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $reports;

    /**
     * @var Collection<int, Tag>
     */
    #[ORM\OneToMany(targetEntity: Tag::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $tags;

    /**
     * @var Collection<int, Location>
     */
    #[ORM\OneToMany(targetEntity: Location::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $locations;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->customers = new ArrayCollection();
        $this->campaigns = new ArrayCollection();
        $this->prospects = new ArrayCollection();
        $this->trades = new ArrayCollection();
        $this->reports = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->locations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): static
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getExternalIdentifier(): ?string
    {
        return sprintf(
            '%s_%s',
            $this->getIdentifier(),
            self::HOST_SYSTEM_TAG
        );
    }

    public static function getIdentifierFromExtId(string $extId): string
    {
        foreach (self::SYSTEM_TAGS as $systemTag) {
            $extId = str_replace(
                sprintf('_%s', $systemTag),
                '',
                $extId
            );

            $extId = str_replace(
                sprintf('_%s', app_lower($systemTag)),
                '',
                $extId
            );
        }
        return trim($extId);
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

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addCompany($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            $user->removeCompany($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Customer>
     */
    public function getCustomers(): Collection
    {
        return $this->customers;
    }

    public function addCustomer(Customer $customer): static
    {
        if (!$this->customers->contains($customer)) {
            $this->customers->add($customer);
            $customer->setCompany($this);
        }

        return $this;
    }

    public function removeCustomer(Customer $customer): static
    {
        if ($this->customers->removeElement($customer)) {
            // set the owning side to null (unless already changed)
            if ($customer->getCompany() === $this) {
                $customer->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Campaign>
     */
    public function getCampaigns(): Collection
    {
        return $this->campaigns;
    }

    public function addCampaign(Campaign $campaign): static
    {
        if (!$this->campaigns->contains($campaign)) {
            $this->campaigns->add($campaign);
            $campaign->setCompany($this);
        }

        return $this;
    }

    public function removeCampaign(Campaign $campaign): static
    {
        if ($this->campaigns->removeElement($campaign)) {
            // set the owning side to null (unless already changed)
            if ($campaign->getCompany() === $this) {
                $campaign->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Prospect>
     */
    public function getProspects(): Collection
    {
        return $this->prospects;
    }

    public function addProspect(Prospect $prospect): static
    {
        if (!$this->prospects->contains($prospect)) {
            $this->prospects->add($prospect);
            $prospect->setCompany($this);
        }

        return $this;
    }

    public function removeProspect(Prospect $prospect): static
    {
        if ($this->prospects->removeElement($prospect)) {
            // set the owning side to null (unless already changed)
            if ($prospect->getCompany() === $this) {
                $prospect->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Trade>
     */
    public function getTrades(): Collection
    {
        return $this->trades;
    }

    public function getPrimaryTrade(): ?Trade
    {
        if ($this->hasTrades()) {
            return $this->getTrades()->first();
        }

        return null;
    }

    public function getPrimaryTradeName(): string
    {
        return $this->getPrimaryTrade()?->getName() ?? '';
    }

    public function hasTrades(): bool
    {
        return !$this->trades->isEmpty();
    }

    public function hasTrade(string $trade): bool
    {
        return in_array($trade, $this->getTrades()->toArray());
    }

    public function addTrade(Trade $trade): static
    {
        if (!$this->trades->contains($trade)) {
            $this->trades->add($trade);
        }

        return $this;
    }

    public function removeTrade(Trade $trade): static
    {
        $this->trades->removeElement($trade);

        return $this;
    }

    /**
     * @return Collection<int, Report>
     */
    public function getReports(): Collection
    {
        return $this->reports;
    }

    public function addReport(Report $report): static
    {
        if (!$this->reports->contains($report)) {
            $this->reports->add($report);
            $report->setCompany($this);
        }

        return $this;
    }

    public function removeReport(Report $report): static
    {
        if ($this->reports->removeElement($report)) {
            // set the owning side to null (unless already changed)
            if ($report->getCompany() === $this) {
                $report->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->setCompany($this);
        }

        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        if ($this->tags->removeElement($tag)) {
            // set the owning side to null (unless already changed)
            if ($tag->getCompany() === $this) {
                $tag->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getLocations(): Collection
    {
        return $this->locations;
    }

    public function addLocation(Location $location): static
    {
        if (!$this->locations->contains($location)) {
            $this->locations->add($location);
            $location->setCompany($this);
        }

        return $this;
    }

    public function removeLocation(Location $location): static
    {
        if ($this->locations->removeElement($location)) {
            if ($location->getCompany() === $this) {
                $location->setCompany(null);
            }
        }

        return $this;
    }
}
