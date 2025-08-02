<?php

namespace App\Services;

use App\Entity\Address;
use App\Entity\Company;
use App\Entity\Prospect;
use App\Exceptions\AddressIsInvalid;
use App\Exceptions\PostProcessingServiceException;
use App\Repository\AddressRepository;
use App\Repository\CompanyRepository;
use App\Repository\ProspectRepository;
use App\Repository\RestrictedAddressRepository;
use App\Services\Address\AddressService;
use App\ValueObjects\AddressObject;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Throwable;

class PostProcessingService
{
    private const PROSPECT_BATCH_SIZE = 2000;
    private int $recordLimit = 1;

    private ?Company $contextCompany = null;
    private array $persistAddressMap = [];
    private array $persistProspectMap = [];

    public function __construct(
        private readonly AddressRepository $addressRepository,
        private readonly RestrictedAddressRepository $restrictedAddressRepository,
        private readonly ProspectRepository $prospectRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly AddressService $addressService,
        private readonly CompanyRepository $companyRepository,
        private readonly ManagerRegistry $registry,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws PostProcessingServiceException
     * @throws AddressIsInvalid
     */
    public function processRecords(?string $contextCompanyIdentifier = null): void
    {
        $this->checkAndResetEntityManager();
        if ($contextCompanyIdentifier) {
            $this->contextCompany =
                $this
                    ->companyRepository
                    ->findOneByIdentifier(
                        $contextCompanyIdentifier
                    );
        }
        $this->processProspects();
        $this->logger->info(
            'After processProspects: Flushing and Clearing entities'
        );
        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    /**
     * @throws PostProcessingServiceException
     */
    public function processProspects(): void
    {
        $this->checkAndResetEntityManager();
        /** @var Prospect[] $prospects */
        $prospects = $this->prospectRepository->getProspectsForPostProcessingQuery(
            $this->recordLimit,
            $this->contextCompany
        )->getResult();

        $this->logger->info(
            'Processing prospects',
            [
                'contextCompany' => $this->contextCompany?->getIdentifier(),
                'count' => count($prospects)
            ]
        );
        $prospectCount = 0;
        foreach ($prospects as $prospect) {
            if ($prospect->isNew()) {
                // Note from Chris:
                // I'm trying to understand how this branch
                // is at all possible.
                // How can the prospect be "new" if it's one
                // of the prospects that we're iterating over?
                if (isset($this->persistProspectMap[$prospect->getExternalId()])) {
                    //override with pre-existing PROSPECT
                    $prospect = $this->persistProspectMap[$prospect->getExternalId()];
                } else {
                    //track this new PROSPECT
                    $this->persistProspectMap[$prospect->getExternalId()] = $prospect;
                }
            }

            $prospectCount++;
            if ($prospectCount % 100 === 0) {
                $this->logger->info(
                    'Processed prospects',
                    [
                        'contextCompany' => $this->contextCompany?->getIdentifier(),
                        'count' => $prospectCount
                    ]
                );
            }
            try {
                $this->processProspect($prospect);
                if ($prospectCount % self::PROSPECT_BATCH_SIZE === 0) {
                    $this->logger->info(
                        'Flushing prospects Batch',
                        [
                            'contextCompany' => $this->contextCompany?->getIdentifier(),
                            'count' => $prospectCount
                        ]
                    );
                    $this->flushEntities();
                }
            } catch (PostProcessingServiceException $e) {
                $this->flushEntities();
                throw $e;
            }
        }
        $this->logger->info(
            'Flushing remaining prospects',
            [
                'contextCompany' => $this->contextCompany?->getIdentifier(),
                'count' => $prospectCount
            ]
        );
        $this->flushEntities();
    }

    /**
     * @throws PostProcessingServiceException
     */
    public function processProspect(Prospect $prospect): void
    {
        $address = null;
        $prospectsLinkedToAddress = [];
        $company = null;

        $addressObject = new AddressObject([
            'address1' => $prospect->getAddress1(),
            'address2' => $prospect->getAddress2(),
            'city' => $prospect->getCity(),
            'stateCode' => $prospect->getState(),
            'postalCode' => $prospect->getPostalCode(),
        ]);
        try {
            $company = $prospect->getCompany();
            if ($company) {
                $company = $this->companyRepository->findOneByIdentifier(
                    $company->getIdentifier()
                );
            } else {
                $this->logger->warning(
                    'Prospect has no company',
                    [
                        'prospectId' => $prospect->getId(),
                        'prospectExternalId' => $prospect->getExternalId()
                    ]
                );
            }
            $address = $this->addressRepository->findOneBy([
                'company' => $company,
                'externalId' => $addressObject->getKey()
            ]) ?? (new Address())->fromValueObject($addressObject);

            if ($address->isNew()) {
                if (isset($this->persistAddressMap[$address->getExternalId()])) {
                    //override with pre-existing ADDRESS
                    $address = $this->persistAddressMap[$address->getExternalId()];
                } else {
                    //track this new ADDRESS
                    $this->persistAddressMap[$address->getExternalId()] = $address;
                }
            }

            $restrictedAddressRecord = $this->restrictedAddressRepository->findOneByExternalId(
                $address->getExternalId()
            );
            if ($restrictedAddressRecord) {
                $address->setGlobalDoNotMail(true);
                $address->setDoNotMail(true);
            }
            $address->setCompany($company);

            if ($address->isEligibleForAddressVerification()) {
                try {
                    $address = $this->addressService->verifyAndNormalize($address);
                } catch (Throwable $e) {
                    throw new PostProcessingServiceException($e->getMessage());
                }
            }

            $address->setCompany($company);

            $address->detectAndAssignBusinessStatus();

            $prospect->addAddress($address);
            $prospect->setPreferredAddress($address);

            // Unset / remove 'preferred' status
            // By default all prospects are created in the system with prospect.preferred = true.
            // After processing, there should be only one prospect for the given address
            // that has prospect.preferred = true, to avoid sending multiple mailers to the same address
            // (even if there are multiple prospects who share the same address).
            if ($address->getId() && $address->isVerified()) {
                foreach ($address->getProspects() as $existingProspect) {
                    $existingProspect->setPreferred(false);
                    $prospectsLinkedToAddress[] = $existingProspect;
                }

                $prospect->setPreferred(true);
            }
        } catch (AddressIsInvalid $e) {
            $this->logger->warning(
                'Invalid address',
                [
                    'prospectId' => $prospect->getId(),
                    'address' => $addressObject->toJson(),
                    'error' => $e->getMessage()
                ]
            );
        }

        $prospect->setProcessedAt(date_create_immutable());

        if ($company) {
            $this->companyRepository->persist($company);
        }
        foreach ($prospectsLinkedToAddress as $prospectLinkedToAddress) {
            $this->prospectRepository->persist($prospectLinkedToAddress);
        }
        if ($address) {
            $this->addressRepository->persist($address);
        }
        $this->prospectRepository->persist($prospect);
    }

    public function setRecordLimit(int $recordLimit): static
    {
        $this->recordLimit = $recordLimit;

        return $this;
    }

    /**
     * @return void
     */
    private function flushEntities(): void
    {
        if ($this->entityManager->isOpen()) {
            $this->entityManager->flush();
        }
    }

    /**
     * @return void
     */
    private function checkAndResetEntityManager(): void
    {
        if (!$this->entityManager->isOpen()) {
            $this->registry->resetManager();
        }
    }
}
