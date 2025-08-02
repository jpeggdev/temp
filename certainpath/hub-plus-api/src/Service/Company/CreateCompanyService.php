<?php

declare(strict_types=1);

namespace App\Service\Company;

use App\DTO\Request\Company\CreateCompanyDTO;
use App\DTO\Response\Company\CreateCompanyResponseDTO;
use App\Entity\Company;
use App\Exception\CompanyAlreadyExistsException;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class CreateCompanyService
{
    public function __construct(
        private CompanyRepository $companyRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function createCompany(
        CreateCompanyDTO $createCompanyDTO,
        ?Company $activeCompany = null,
    ): CreateCompanyResponseDTO {
        $this->entityManager->beginTransaction();

        try {
            $companyName = $createCompanyDTO->companyName;
            $websiteUrl = '' === $createCompanyDTO->websiteUrl ? null : $createCompanyDTO->websiteUrl;
            $salesforceId = '' === $createCompanyDTO->salesforceId ? null : $createCompanyDTO->salesforceId;
            $intacctId = '' === $createCompanyDTO->intacctId ? null : $createCompanyDTO->intacctId;
            $companyEmail = '' === $createCompanyDTO->companyEmail ? null : $createCompanyDTO->companyEmail;

            if ($this->companyRepository->findOneBy(['companyName' => $companyName])) {
                throw new CompanyAlreadyExistsException(sprintf('A company with the name "%s" already exists.', $companyName));
            }

            if ($salesforceId && $this->companyRepository->findOneBy(['salesforceId' => $salesforceId])) {
                throw new CompanyAlreadyExistsException(sprintf('A company with the Salesforce ID "%s" already exists.', $salesforceId));
            }

            if ($intacctId && $this->companyRepository->findOneBy(['intacctId' => $intacctId])) {
                throw new CompanyAlreadyExistsException(sprintf('A company with the Intacct ID "%s" already exists.', $intacctId));
            }

            $company = new Company();
            $company->setCompanyName($companyName);
            $company->setWebsiteUrl($websiteUrl);
            $company->setSalesforceId($salesforceId);
            $company->setIntacctId($intacctId);
            $company->setCompanyEmail($companyEmail);

            $this->companyRepository->save($company, true);

            $this->entityManager->commit();

            return new CreateCompanyResponseDTO(
                $company->getId(),
                $company->getCompanyName(),
                $company->getWebsiteUrl(),
                $company->getUuid(),
                $company->getSalesforceId(),
                $company->getIntacctId(),
                $company->getCompanyEmail()
            );
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        } finally {
            $this->entityManager->clear();
        }
    }
}
