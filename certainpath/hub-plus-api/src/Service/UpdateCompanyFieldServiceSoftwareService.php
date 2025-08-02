<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Request\UpdateCompanyFieldServiceSoftwareDTO;
use App\DTO\Response\UpdateFieldServiceSoftwareResponseDTO;
use App\Entity\Company;
use App\Exception\FieldServiceSoftwareNotFoundException;
use App\Repository\CompanyRepository;
use App\Repository\FieldServiceSoftwareRepository;

readonly class UpdateCompanyFieldServiceSoftwareService
{
    public function __construct(
        private CompanyRepository $companyRepository,
        private FieldServiceSoftwareRepository $fieldServiceSoftwareRepository,
    ) {
    }

    public function updateFieldServiceSoftware(
        Company $company,
        UpdateCompanyFieldServiceSoftwareDTO $dto,
    ): UpdateFieldServiceSoftwareResponseDTO {
        $fieldServiceSoftware = $this->fieldServiceSoftwareRepository->find($dto->fieldServiceSoftwareId);

        if (!$fieldServiceSoftware) {
            throw new FieldServiceSoftwareNotFoundException();
        }

        $company->setFieldServiceSoftware($fieldServiceSoftware);
        $this->companyRepository->save($company, true);

        return UpdateFieldServiceSoftwareResponseDTO::fromEntity($company);
    }
}
