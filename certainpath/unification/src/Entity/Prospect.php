<?php

namespace App\Entity;

use App\Repository\ProspectRepository;
use App\ValueObjects\ProspectObject;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use function App\Functions\app_getPostalCodeShort;
use function App\Functions\app_lower;

#[ORM\Entity(repositoryClass: ProspectRepository::class)]
#[ORM\UniqueConstraint(name: "prospect_company_external_uniq", columns: ["company_id", "external_id"])]
#[ORM\Index(name: 'prospect_company_postal_code_idx', columns: ['company_id', 'postal_code'])]
#[ORM\Index(name: 'prospect_company_postal_code_short_idx', columns: ['company_id', 'postal_code_short'])]
#[ORM\Index(name: 'prospect_company_city_idx', columns: ['company_id', 'city'])]
#[ORM\Index(name: 'prospect_company_state_idx', columns: ['company_id', 'state'])]
#[ORM\Index(name: 'prospect_external_id_idx', columns: ['external_id'])]

#[ORM\HasLifecycleCallbacks]
class Prospect
{
    use Traits\ExternalIdEntity;
    use Traits\PostProcessEntity;
    use Traits\StatusEntity;
    use Traits\TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $fullName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $address1 = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $address2 = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $state = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $postalCode = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $postalCodeShort = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private ?bool $isPreferred = true;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private ?bool $doNotMail = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private ?bool $doNotContact = false;

    #[ORM\OneToOne(inversedBy: 'prospect', cascade: ['persist', 'remove'])]
    private ?Customer $customer = null;

    #[ORM\ManyToOne(inversedBy: 'prospects')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    /**
     * @var Collection<int, Batch>
     */
    #[ORM\ManyToMany(targetEntity: Batch::class, inversedBy: 'prospects')]
    #[ORM\JoinTable(name: 'batch_prospect')]
    private Collection $batches;

    #[ORM\ManyToOne]
    private ?Address $preferredAddress = null;

    /**
     * @var Collection<int, Address>
     */
    #[ORM\JoinTable(name: 'prospect_address')]
    #[ORM\JoinColumn(name: 'prospect_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'address_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: Address::class, inversedBy: 'prospects')]
    private Collection $addresses;

    #[ORM\OneToOne(mappedBy: 'prospect')]
    private ?ProspectDetails $prospectDetails = null;

    /**
     * @var Collection<int, ProspectSource>
     */
    #[ORM\OneToMany(targetEntity: ProspectSource::class, mappedBy: 'prospect', orphanRemoval: true)]
    private Collection $prospectSources;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\ManyToMany(targetEntity: Event::class, inversedBy: 'prospects')]
    private Collection $events;

    /**
     * @var Collection<int, Tag>
     */
    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'prospects')]
    private Collection $tags;

    public function __construct()
    {
        $this->addresses = new ArrayCollection();
        $this->batches = new ArrayCollection();
        $this->prospectSources = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): static
    {
        $this->postalCode = $postalCode;
        $this->postalCodeShort = null;
        if ($this->postalCode) {
            $this->setPostalCodeShort(
                app_getPostalCodeShort($this->postalCode)
            );
        }

        return $this;
    }

    public function getPostalCodeShort(): ?string
    {
        return $this->postalCodeShort;
    }

    public function setPostalCodeShort(string $postalCodeShort): static
    {
        $this->postalCodeShort = $postalCodeShort;

        return $this;
    }

    public function isNew(): bool
    {
        return $this->getId() === null;
    }

    public function isPreferred(): ?bool
    {
        return $this->isPreferred;
    }

    public function setPreferred(bool $isPreferred): static
    {
        $this->isPreferred = $isPreferred;

        return $this;
    }

    public function isDoNotMail(): bool
    {
        return $this->preferredAddress?->isDoNotMail() ?? $this->doNotMail;
    }

    public function setDoNotMail(bool $doNotMail): static
    {
        $this->doNotMail = $doNotMail;
        $this->preferredAddress?->setDoNotMail($doNotMail);

        return $this;
    }

    public function isDoNotContact(): bool
    {
        return $this->doNotContact;
    }

    public function setDoNotContact(bool $doNotContact): static
    {
        $this->doNotContact = $doNotContact;

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

    public function getAddress1(): ?string
    {
        return $this->address1;
    }

    public function setAddress1(?string $address1): static
    {
        $this->address1 = $address1;

        return $this;
    }

    public function getAddress2(): ?string
    {
        return $this->address2;
    }

    public function setAddress2(?string $address2): static
    {
        $this->address2 = $address2;

        return $this;
    }

    public function getBatches(): Collection
    {
        return $this->batches;
    }

    public function addBatch(Batch $batch): self
    {
        if (!$this->batches->contains($batch)) {
            $this->batches->add($batch);
        }

        return $this;
    }

    public function removeBatch(Batch $batch): static
    {
        $this->batches->removeElement($batch);

        return $this;
    }

    public function getPreferredAddress(): ?Address
    {
        return $this->preferredAddress;
    }

    public function setPreferredAddress(?Address $preferredAddress): static
    {
        $this->preferredAddress = $preferredAddress;

        return $this;
    }

    public function getAddressByExternalId(string $externalId): ?Address
    {
        foreach ($this->getAddresses() as $address) {
            if ($address->getExternalId() === $externalId) {
                return $address;
            }
        }

        return null;
    }

    public function getMostRecentValidAddress(): ?Address
    {
        $addresses = $this->getAddresses();
        if ($addresses->isEmpty()) {
            return null;
        }

        $validAddresses = $addresses->filter(function (Address $address) {
            return (
                $address->isActive() &&
                !$address->isPoBox() &&
                !$address->isVacant() &&
                !$address->isBusiness() &&
                !$address->isDoNotMail() &&
                !$address->isGlobalDoNotMail() &&
                $address->getVerifiedAt() !== null
            );
        });

        $validAddresses = $validAddresses->toArray();
        usort($validAddresses, static function (Address $a, Address $b) {
            return $b->getVerifiedAt() <=> $a->getVerifiedAt();
        });

        return $validAddresses[0] ?? null;
    }

    /**
     * @return Collection<int, Address>
     */
    public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    public function addAddress(Address $address): static
    {
        if (!$this->addresses->contains($address)) {
            $this->addresses->add($address);
            $address->addProspect($this);
        }

        return $this;
    }

    public function removeAddress(Address $address): static
    {
        $this->addresses->removeElement($address);
        $address->removeProspect($this);

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    public function isCustomer(): bool
    {
        return $this->customer !== null;
    }

    public function hasAddressRecord(): bool
    {
        return $this->addresses->count() > 0;
    }

    public function isPoBox(): bool
    {
        $address1 = $this->getAddress1();
        if (!$address1) {
            return false;
        }
        $match = preg_match('/^p\.? *o\.? *box/i', $address1);
        return ($match !== false) && ($match > 0);
    }

    public function hasValidatedAddress(): bool
    {
        if (!$this->hasAddressRecord()) {
            return !$this->isPoBox() && !$this->blankAddress();
        }
        /** @var Address $address */
        foreach ($this->getAddresses() as $address) {
            if (
                    $address->isActive()
                    &&
                    $address->isVerified()
                    &&
                    !$address->isPoBox()
                    &&
                    !$address->isVacant()
                    &&
                    !$address->isBusiness()
                    &&
                    !$address->isDoNotMail()
                    &&
                    !$address->isGlobalDoNotMail()
            ) {
                return true;
            }
        }
        return false;
    }

    private function blankAddress(): bool
    {
        return
            empty($this->getAddress1())
            ||
            empty($this->getCity())
            ||
            empty($this->getState())
            ||
            empty($this->getPostalCode());
    }

    public function fromValueObject(ProspectObject $prospectObject): static
    {
        $prospectObject->populate();
        $this
            ->setActive($prospectObject->isActive())
            ->setDeleted($prospectObject->isDeleted())
            ->setFullName($prospectObject->fullName)
            ->setExternalId($prospectObject->externalId)
            ->setFirstName($prospectObject->firstName)
            ->setLastName($prospectObject->lastName)
            ->setAddress1($prospectObject->address1)
            ->setAddress2($prospectObject->address2)
            ->setCity($prospectObject->city)
            ->setState($prospectObject->state)
            ->setPostalCode($prospectObject->postalCode)
            ->setDoNotMail($prospectObject->doNotMail)
            ->setDoNotContact($prospectObject->doNotContact);

        return $this;
    }

    public function getProspectDetails(): ?ProspectDetails
    {
        return $this->prospectDetails;
    }

    public function setProspectDetails(ProspectDetails $prospectDetails): static
    {
        if ($prospectDetails->getProspect() !== $this) {
            $prospectDetails->setProspect($this);
        }

        $this->prospectDetails = $prospectDetails;

        return $this;
    }

    /**
     * @return Collection<int, ProspectSource>
     */
    public function getProspectSources(): Collection
    {
        return $this->prospectSources;
    }

    public function getProspectSourceByName(string $name): ProspectSource
    {
        $name = app_lower(preg_replace('/\W+/', '_', $name));
        $sources = $this->getProspectSources()->filter(function (ProspectSource $source) use ($name) {
            return $source->getName() === $name;
        });

        if ($sources->count()) {
            return $sources->first();
        }

        $this->addProspectSource(
            (new ProspectSource())->setName($name)
        );

        return $this->getProspectSourceByName($name);
    }

    public function addProspectSource(ProspectSource $prospectSource): static
    {
        if (!$this->prospectSources->contains($prospectSource)) {
            $this->prospectSources->add($prospectSource);
            $prospectSource->setProspect($this);
        }

        return $this;
    }

    public function removeProspectSource(ProspectSource $prospectSource): static
    {
        if ($this->prospectSources->removeElement($prospectSource)) {
            // set the owning side to null (unless already changed)
            if ($prospectSource->getProspect() === $this) {
                $prospectSource->setProspect(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        $this->events->removeElement($event);

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
        }

        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        $this->tags->removeElement($tag);

        return $this;
    }
}
