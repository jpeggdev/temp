<?php

namespace App\Services\AddressVerification;

use App\Entity\AbstractAddress;
use App\Exceptions\Smarty\AddressVerificationFailedException;
use App\Exceptions\Smarty\NoAddressCandidateFoundException;
use App\Exceptions\Smarty\RequestAddressCandidateFailedException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use SmartyStreets\PhpSdk\US_Street\Candidate;
use SmartyStreets\PhpSdk\US_Street\Client;
use SmartyStreets\PhpSdk\US_Street\Lookup;
use Symfony\Component\Serializer\SerializerInterface;

use function App\Functions\app_getPostalCodeShort;

class SmartyAddressVerificationService implements AddressVerificationServiceInterface
{
    public const API_TYPE = 'smarty';

    private const ADDRESS_VALIDATION_FULL_MATCH = 'AABB';

    private const DPV_MATCH_CONFIRMED = 'Y';

    private const ADDRESS_VACANT_CONFIRMATION = 'Y';

    private const ADDRESS_ACTIVE_CONFIRMATION = 'Y';

    private const ADDRESS_TYPE_COMMERCIAL = 'Commercial';

    public function __construct(
        private readonly Client $client,
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function getApiType(): string
    {
        return self::API_TYPE;
    }

    /**
     * @throws NoAddressCandidateFoundException
     * @throws RequestAddressCandidateFailedException
     * @throws AddressVerificationFailedException
     */
    public function verifyAndNormalize(AbstractAddress $address): AbstractAddress
    {
        $addressCandidate = $this->requestAddressCandidate($address);

        if ($addressCandidate === null) {
            $this->handleFailedAddressVerificationAttempt($address);
            throw new NoAddressCandidateFoundException();
        }

        if (!$this->verifyAddressCandidate($addressCandidate)) {
            $this->handleFailedAddressVerificationAttempt($address);
            throw new AddressVerificationFailedException();
        }

        return $this->normalizeAddress($address, $addressCandidate);
    }

    private function normalizeAddress(
        AbstractAddress $address,
        Candidate $addressCandidate
    ): AbstractAddress {
        $countryCode = 'USA';
        $postalCode = $addressCandidate->getComponents()->getZipCode();
        $apiResponse = $this->serializer->serialize($addressCandidate, 'json');

        $address->setAddress1($addressCandidate->getDeliveryLine1());
        $address->setAddress2($addressCandidate->getDeliveryLine2());
        $address->setCity($addressCandidate->getComponents()->getCityName());
        $address->setStateCode($addressCandidate->getComponents()->getStateAbbreviation());
        $address->setCountryCode($countryCode);
        $address->setPostalCode($postalCode);
        $address->setBusiness(false);
        $address->setVacant(false);
        $address->setActive(false);
        $address->setVerifiedAt(date_create_immutable());
        $address->incrementVerificationAttempts();
        $address->setApiResponse($apiResponse);
        $address->setApiType($this->getApiType());

        if ($this->isVacant($addressCandidate)) {
            $address->setVacant(true);
        }

        if ($this->isActive($addressCandidate)) {
            $address->setActive(true);
        }

        if ($this->isCommercial($addressCandidate)) {
            $address->setBusiness(true);
        }

        return $address;
    }

    private function createLookup(AbstractAddress $address): Lookup
    {
        $lookup = new Lookup();
        $lookup->setStreet($address->getAddress1());
        $lookup->setStreet2($address->getAddress2());
        $lookup->setCity($address->getCity());
        $lookup->setState($address->getStateCode());
        $lookup->setZipCode(app_getPostalCodeShort($address->getPostalCode()));
        $lookup->setMatchStrategy(Lookup::STRICT);

        return $lookup;
    }

    /**
     * @throws RequestAddressCandidateFailedException
     */
    private function requestAddressCandidate(AbstractAddress $address): ?Candidate
    {
        $lookup = $this->createLookup($address);

        try {
            $this->client->sendLookup($lookup);
            $result = $lookup->getResult();

            return $result[0] ?? null;
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            throw new RequestAddressCandidateFailedException();
        }
    }

    private function verifyAddressCandidate(Candidate $candidate): bool
    {
        $isDpvFootnotesValid = $candidate->getAnalysis()->getDpvFootnotes() === self::ADDRESS_VALIDATION_FULL_MATCH;
        $isDpvMatchCodeValid = $candidate->getAnalysis()->getDpvMatchCode() === self::DPV_MATCH_CONFIRMED;

        return $isDpvFootnotesValid && $isDpvMatchCodeValid;
    }

    private function handleFailedAddressVerificationAttempt(AbstractAddress $address): void
    {
        $address->incrementVerificationAttempts();

        $this->entityManager->persist($address);
        $this->entityManager->flush();
    }

    private function isCommercial(Candidate $candidate): bool
    {

        return $candidate->getMetadata()->getRdi() === self::ADDRESS_TYPE_COMMERCIAL;
    }

    private function isVacant(Candidate $candidate): bool
    {
        return $candidate->getAnalysis()->getVacant() === self::ADDRESS_VACANT_CONFIRMATION;
    }

    private function isActive(Candidate $candidate): bool
    {
        return $candidate->getAnalysis()->getActive() === self::ADDRESS_ACTIVE_CONFIRMATION;
    }
}
