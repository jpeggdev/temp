<?php

namespace App\Services\DMER;

use App\DTO\Query\Invoice\DailySalesQueryDTO;
use App\Entity\Company;
use App\Exceptions\DomainException\DMER\DMERProcessingException;
use App\Message\UpdateDMERMessage;
use App\Repository\InvoiceRepository;
use App\Services\ExcelService;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Microsoft\Graph\Exception\GraphException;
use Microsoft\Graph\Model\WorkbookSessionInfo;
use Microsoft\Graph\Model\WorkbookWorksheet;
use Symfony\Component\Messenger\MessageBusInterface;

class DMERDataService
{
    public function __construct(
        private readonly ExcelService $excelService,
        private readonly InvoiceRepository $invoiceRepository,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function dispatchCompanyDMERUpdate(Company $company): UpdateDMERMessage
    {
        $updateDmerMessage = new UpdateDMERMessage(
            $company->getIdentifier()
        );

        $this->messageBus->dispatch(
            $updateDmerMessage
        );

        return $updateDmerMessage;
    }

    /**
     * @throws GuzzleException
     * @throws GraphException
     * @throws \DateMalformedPeriodStringException
     */
    public function insertSalesData(
        Company $company,
        WorkbookWorksheet $worksheet,
        WorkbookSessionInfo $sessionInfo = null
    ): void {
        $salesData = $this->invoiceRepository->getDMRDailySalesData(new DailySalesQueryDTO(
            $company->getIdentifier(),
            date_create_immutable('First day of this year')->setTime(0, 0),
            date_create_immutable('tomorrow')
        ));

        $flattened = $salesData->orderByDateAndServiceCategory()?->map(function ($collection) {
            return (new Collection($collection->toArray()))->flatten(1);
        })->values();

        $startingPoint = 'C4';
        $endingPoint = (Carbon::now()->isLeapYear()) ? 'N369' : 'N368';
        $this->excelService->insert(
            $startingPoint,
            $endingPoint,
            $flattened,
            $company,
            DMERTemplateFactory::generateFileName(
                $company->getPrimaryTradeName(),
                date('Y')
            ),
            $worksheet->getId(),
            $sessionInfo
        );
    }

    /**
     * @throws GraphException
     * @throws GuzzleException
     * @throws DMERProcessingException
     * @throws \DateMalformedPeriodStringException
     */
    public function updateReportData(Company $company)
    {
        $primaryTrade = $company->getPrimaryTrade();
        if (!$primaryTrade) {
            throw new DMERProcessingException(sprintf(
                '%s does not have a primaryTrade assigned.',
                $company->getIdentifier()
            ));
        }

        $fileName = DMERTemplateFactory::generateFileName(
            $primaryTrade->getName(),
            date('Y')
        );

        $session = $this->excelService->createExcelSession(
            $fileName,
            $company
        );
        $worksheets = $this->excelService->getExcelWorkbooks(
            $fileName,
            $company
        );
        $worksheetCollection = new Collection($worksheets);

        $salesWorksheet = $worksheetCollection->filter(function ($worksheet) use ($company) {
            return $worksheet->getName() === DMERTemplateFactory::getSalesWorksheetName($company);
        })->first();
        $this->insertSalesData($company, $salesWorksheet, $session);

        $this->excelService->closeWorkbookSession(
            $fileName,
            $company,
            $session
        );
    }
}
