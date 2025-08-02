<?php

namespace App\Services\DMER;

use App\Entity\Company;
use App\Exceptions\DomainException\DMER\DMERFileExistsException;
use App\Services\ExcelService;
use App\Services\OneDriveService;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Promise\Utils;
use Illuminate\Support\Collection;
use Microsoft\Graph\Exception\GraphException;
use Microsoft\Graph\Model\SharingLink;
use Throwable;

class DMERFileService
{
    private ?int $year = null;

    public function __construct(
        private readonly OneDriveService $oneDriveService,
        private readonly DMERTemplateFactory $dmerTemplateFactory,
        private readonly ExcelService $excelService
    ) {
    }

    public function setYear(int $year): self
    {
        $this->year = $year;
        return $this;
    }

    public function getYear(): int
    {
        return $this->year ?? date('Y');
    }

    /**
     * @throws DMERFileExistsException
     * @throws GuzzleException
     * @throws GraphException
     * @throws Throwable
     */
    public function generateNewReport(Company $company): SharingLink
    {
        $this->oneDriveService->createDirectoryResourceForCompany($company);
        if (
            $this->oneDriveService->fileExists($company, DMERTemplateFactory::generateFileName(
                $company->getPrimaryTradeName(),
                $this->getYear()
            ))
        ) {
            throw new DMERFileExistsException();
        }

        $promise = $this->oneDriveService->copyFile(
            $company,
            $this->dmerTemplateFactory->getTemplate($company),
            DMERTemplateFactory::generateFileName(
                $company->getPrimaryTradeName(),
                $this->getYear()
            )
        );
        $promise->then(function () use ($company) {
            $this->updateCompanyName($company);
            $this->updateReportYear($company);
        });
        // Wait for the dmer report to be copied from template to account dir before updating the company name and
        // report year
        Utils::settle($promise)->wait();

        return $this->oneDriveService->getShareLink($company, DMERTemplateFactory::generateFileName(
            $company->getPrimaryTradeName(),
            $this->getYear()
        ));
    }

    public function getReportLink(Company $company): false|SharingLink
    {

        $fileName = DMERTemplateFactory::generateFileName(
            $company->getPrimaryTradeName(),
            $this->getYear()
        );

        if ($this->oneDriveService->fileExists($company, $fileName)) {
            return $this->oneDriveService->getShareLink(
                $company,
                $fileName
            );
        }
        return false;
    }

    /**
     * @throws GuzzleException
     * @throws GraphException
     */
    private function updateCompanyName(Company $company): void
    {
        $name = '_CO';
        $data = new Collection($company->getName());
        $this->excelService->insertByName(
            $name,
            $data,
            $company,
            DMERTemplateFactory::generateFileName(
                $company->getPrimaryTradeName(),
                $this->getYear()
            ),
        );
    }

    /**
     * @throws GraphException
     * @throws GuzzleException
     */
    private function updateReportYear(Company $company): void
    {
        $name = '_YR';
        $year = (string) $this->getYear() ?: date('Y');
        $data = new Collection($year);
        $this->excelService->insertByName(
            $name,
            $data,
            $company,
            DMERTemplateFactory::generateFileName(
                $company->getPrimaryTradeName(),
                $this->getYear()
            ),
        );
    }
}
