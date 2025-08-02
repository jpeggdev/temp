<?php

namespace App\Services;

use App\Entity\Company;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Microsoft\Graph\Exception\GraphException;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\WorkbookNamedItem;
use Microsoft\Graph\Model\WorkbookRange;
use Microsoft\Graph\Model\WorkbookSessionInfo;
use Microsoft\Graph\Model\WorkbookWorksheet;

readonly class ExcelService
{
    public function __construct(
        private OneDriveService $oneDriveService,
        private Graph $graphClient,
    ) {
    }

    /**
     * @throws GraphException
     * @throws GuzzleException
     */
    public function createExcelSession(string $fileName, Company $company): WorkbookSessionInfo
    {
        $endpoint = sprintf(
            '%s/%s:/workbook/createSession',
            $this->oneDriveService->getDirectoryResourceForCompany($company),
            $fileName
        );

        $docGrabber = $this->graphClient->createRequest(
            'POST',
            $endpoint
        )
            ->attachBody(
                [
                    'persistChanges' => true
                ]
            )
            ->setReturnType(WorkbookSessionInfo::class);
        return $docGrabber->execute();
    }

    /**
     * @throws GraphException
     * @throws GuzzleException
     */
    public function closeWorkbookSession(string $fileName, Company $company, WorkbookSessionInfo $session)
    {
        $endpoint = sprintf(
            '%s/%s:/workbook/closeSession',
            $this->oneDriveService->getDirectoryResourceForCompany($company),
            $fileName
        );
        return $this->graphClient->createRequest(
            'POST',
            $endpoint
        )
            ->addHeaders(
                [
                    "workbook-session-id" => $session->getId()
                ]
            )
            ->execute();
    }

    /**
     * @throws GraphException
     * @throws GuzzleException
     */
    public function insert(
        string $startingPoint,
        string $endingPoint,
        Collection $records,
        Company $company,
        string $fileName,
        string $worksheetId,
        WorkbookSessionInfo $session = null
    ): ?WorkbookRange {
        $endpoint = sprintf(
            "%s/%s:/workbook/worksheets/%s/range(address='%s:%s')",
            $this->oneDriveService->getDirectoryResourceForCompany($company),
            $fileName,
            $worksheetId,
            $startingPoint,
            $endingPoint
        );
        return $this->graphClient->createRequest(
            'PATCH',
            $endpoint
        )
            ->setReturnType(WorkbookRange::class)
            ->addHeaders(["workbook-session-id" => ($session) ? $session->getId() : null])
            ->attachBody([
                "values" => $records->toArray(),
            ])
            ->execute();
    }

    /**
     * @throws GraphException
     * @throws GuzzleException
     */
    public function getExcelWorkbooks(string $fileName, Company $company): ?array
    {
        $endpoint = sprintf(
            '%s/%s:/workbook/worksheets',
            $this->oneDriveService->getDirectoryResourceForCompany($company),
            $fileName
        );
        return $this->graphClient->createCollectionRequest(
            "GET",
            $endpoint
        )
            ->setReturnType(WorkbookWorksheet::class)
            ->execute();
    }

    /**
     * @throws GraphException
     * @throws GuzzleException
     */
    public function unprotectWorksheet(string $fileName, Company $company, string $worksheetId): void
    {
        $endpoint = sprintf(
            '%s/%s:/workbook/worksheets/%s/protection/unprotect',
            $this->oneDriveService->getDirectoryResourceForCompany($company),
            $fileName,
            $worksheetId
        );
        $this->graphClient->createRequest(
            'POST',
            $endpoint
        )
            ->execute();
    }

    /**
     * @throws GraphException
     * @throws GuzzleException
     */
    public function protectWorksheet(string $fileName, Company $company, string $worksheetId): void
    {
        $endpoint = sprintf(
            '%s/%s:/workbook/worksheets/%s/protection/protect',
            $this->oneDriveService->getDirectoryResourceForCompany($company),
            $fileName,
            $worksheetId
        );
        $this->graphClient->createRequest(
            'POST',
            $endpoint
        )
            ->execute();
    }

    /**
     * @throws GraphException
     * @throws GuzzleException
     */
    public function getWorkbookNames(string $fileName, Company $company): array
    {
        $endpoint = sprintf(
            '%s/%s:/workbook/names',
            $this->oneDriveService->getDirectoryResourceForCompany($company),
            $fileName
        );
        return $this->graphClient->createRequest(
            'GET',
            $endpoint
        )
            ->setReturnType(WorkbookNamedItem::class)
            ->execute();
    }

    /**
     * @throws GraphException
     * @throws GuzzleException
     */
    public function insertByName($name, Collection $data, Company $company, string $generateFileName): void
    {
        $endpoint = sprintf(
            '%s/%s:/workbook/names/%s/range',
            $this->oneDriveService->getDirectoryResourceForCompany($company),
            $generateFileName,
            $name
        );
        $this->graphClient->createRequest(
            'PATCH',
            $endpoint
        )
            ->addHeaders(
                [
                    "workbook-session-id" => null
                ]
            )
            ->attachBody(
                [
                    "values" => [$data->toArray()]
                ]
            )
            ->execute();
    }
}
