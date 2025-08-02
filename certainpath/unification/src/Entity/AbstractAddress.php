<?php

namespace App\Entity;

use App\Exceptions\AddressIsInvalid;
use App\ValueObjects\AddressDetection\AddressBusinessClassification;
use App\ValueObjects\AddressObject;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use function App\Functions\app_getPostalCodeShort;

#[ORM\MappedSuperclass]
abstract class AbstractAddress
{
    use Traits\ExternalIdEntity;
    use Traits\StatusEntity;
    use Traits\TimestampableEntity;

    public const MAX_VERIFICATION_ATTEMPTS = 3;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $address1 = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $address2 = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $city = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $stateCode = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $postalCode = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $postalCodeShort = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $countryCode = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    protected bool $isBusiness = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    protected ?bool $isVacant = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    protected ?DateTimeImmutable $verifiedAt = null;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    protected int $verificationAttempts = 0;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $apiType = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $apiResponse = null;

    public function getAddress1(): ?string
    {
        return $this->address1;
    }

    public function setAddress1(string $address1): static
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

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;
        return $this;
    }

    public function getStateCode(): ?string
    {
        return $this->stateCode;
    }

    public function setStateCode(?string $stateCode): static
    {
        $this->stateCode = $stateCode;
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

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(?string $countryCode): static
    {
        $this->countryCode = $countryCode;
        return $this;
    }

    public function isNew(): bool
    {
        return $this->getId() === null;
    }

    public function isBusiness(): ?bool
    {
        return $this->isBusiness;
    }

    public function setBusiness(bool $isBusiness): static
    {
        $this->isBusiness = $isBusiness;

        return $this;
    }

    public function isResidential(): bool
    {
        return !$this->isBusiness;
    }

    public function isVacant(): ?bool
    {
        return $this->isVacant;
    }

    public function setVacant(bool $isVacant): static
    {
        $this->isVacant = $isVacant;

        return $this;
    }

    public function getVerifiedAt(): ?DateTimeImmutable
    {
        return $this->verifiedAt;
    }

    public function setVerifiedAt(?DateTimeImmutable $verifiedAt): static
    {
        $this->verifiedAt = $verifiedAt;

        return $this;
    }

    public function isVerified(): bool
    {
        return ($this->getVerifiedAt() !== null);
    }

    public function setApiType(?string $apiType): static
    {
        $this->apiType = $apiType;

        return $this;
    }

    public function getApiType(): ?string
    {
        return $this->apiType;
    }

    public function getApiResponse(): ?string
    {
        return $this->apiResponse;
    }

    public function setApiResponse(?string $apiResponse): static
    {
        $this->apiResponse = $apiResponse;

        return $this;
    }

    public function hasFailedAddressVerification(): bool
    {
        return $this->getVerificationAttempts() > self::MAX_VERIFICATION_ATTEMPTS;
    }

    public function isEligibleForAddressVerification(): bool
    {
        if ($this->isVerified()) {
            return false;
        }

        if ($this->hasFailedAddressVerification()) {
            return false;
        }

        return true;
    }

    public function incrementVerificationAttempts(): static
    {
        $this->setVerificationAttempts(
            $this->getVerificationAttempts() + 1
        );

        return $this;
    }

    public function getVerificationAttempts(): ?int
    {
        return $this->verificationAttempts;
    }

    public function setVerificationAttempts(int $verificationAttempts): static
    {
        $this->verificationAttempts = $verificationAttempts;

        return $this;
    }

    public function makeExternalId(): string
    {
        return AddressObject::createKey([
            'address1' => $this->getAddress1(),
            'address2' => $this->getAddress2(),
            'city' => $this->getCity(),
            'stateCode' => $this->getStateCode(),
            'postalCode' => $this->getPostalCodeShort(),
        ]);
    }

    /**
     * @throws AddressIsInvalid
     */
    public function fromValueObject(AddressObject $addressObject): static
    {
        $addressObject->populate();
        if (!$addressObject->address1) {
            throw new AddressIsInvalid(
                $addressObject->toJson()
            );
        }
        $this
            ->setActive($addressObject->isActive())
            ->setDeleted($addressObject->isDeleted())
            ->setAddress1($addressObject->address1)
            ->setAddress2($addressObject->address2)
            ->setCity($addressObject->city)
            ->setStateCode($addressObject->stateCode)
            ->setPostalCode($addressObject->postalCode)
            ->setCountryCode($addressObject->countryISOCode)
            ->setVerifiedAt($addressObject->verifiedAt)
            ->setBusiness($addressObject->isBusiness)
            ->setVacant($addressObject->isVacant)
            ->setExternalId($addressObject->getKey());
        return $this;
    }

    #[ORM\PreUpdate]
    #[ORM\PrePersist]
    public function updateExternalId(): void
    {
        $this->setExternalId(
            $this->makeExternalId()
        );
    }

    public function detectAndAssignBusinessStatus(): void
    {
        $classifier = new AddressBusinessClassification();
        $result = $classifier->classifyAddress(
            $this->address1 ?? '',
            $this->address2 ?? '',
            $this->city ?? ''
        );

        $this->setBusiness($result->isBusiness);
    }
}
