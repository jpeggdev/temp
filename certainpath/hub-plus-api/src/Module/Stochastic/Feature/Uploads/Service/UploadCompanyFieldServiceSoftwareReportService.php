<?php

namespace App\Module\Stochastic\Feature\Uploads\Service;

use App\Entity\Company;
use App\Entity\CompanyDataImportJob;
use App\Entity\FieldServiceSoftware;
use App\Entity\Trade;
use App\Exception\CouldNotReadSheet;
use App\Exception\ExcelFileIsCorrupted;
use App\Exception\FieldsAreMissing;
use App\Exception\FileDoesNotExist;
use App\Exception\IntentWasUnclear;
use App\Exception\NoFilePathWasProvided;
use App\Exception\UnsupportedFileTypeException;
use App\Exception\UnsupportedSoftware;
use App\Exception\UnsupportedTrade;
use App\Message\CompanyFieldServiceImportCreated;
use App\Module\Stochastic\Feature\Uploads\DTO\Request\UploadCompanyFieldServiceSoftwareReportDTO;
use App\Repository\FieldServiceSoftwareRepository;
use App\Repository\TradeRepository;
use App\Service\AbstractUploadService;
use App\Service\FieldServicesUploadService;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Exception;
use League\Csv\SyntaxError;
use League\Csv\UnavailableStream;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class UploadCompanyFieldServiceSoftwareReportService extends AbstractUploadService
{
    public function __construct(
        private EntityManagerInterface $em,
        private MessageBusInterface $bus,
        LoggerInterface $logger,
        private FieldServicesUploadService $fieldServicesUploadService,
        private TradeRepository $tradeRepository,
        private FieldServiceSoftwareRepository $fieldServiceSoftwareRepository,
        string $tempDirectory,
    ) {
        parent::__construct($tempDirectory, $logger);
    }

    /**
     * @throws FieldsAreMissing
     * @throws UnsupportedFileTypeException
     * @throws Exception
     * @throws NoFilePathWasProvided
     * @throws UnsupportedTrade
     * @throws IntentWasUnclear
     * @throws ExcelFileIsCorrupted
     * @throws ReaderNotOpenedException
     * @throws UnsupportedSoftware
     * @throws FileDoesNotExist
     * @throws IOException
     * @throws UnavailableStream
     * @throws CouldNotReadSheet
     * @throws SyntaxError
     */
    public function handle(
        UploadCompanyFieldServiceSoftwareReportDTO $dto,
        Company $company,
        UploadedFile $uploadedFile,
    ): int {
        if (
            false === $dto->isJobsOrInvoiceFile
            && false === $dto->isActiveClubMemberFile
            && false === $dto->isMemberFile
        ) {
            $message = 'Please specify one of: isJobsOrInvoiceFile, isActiveClubMemberFile, or isMemberFile';
            throw new IntentWasUnclear($message);
        }

        $uploadedFilePath = $this->moveUploadedFile(
            $uploadedFile,
            $company,
            'field-services'
        );

        $trade = $this->tradeRepository->getTrade(
            Trade::fromLongName($dto->trade)
        );
        if (null === $trade) {
            throw new UnsupportedTrade($dto->trade);
        }
        $software = $this->fieldServiceSoftwareRepository->getSoftware(
            FieldServiceSoftware::fromName($dto->software)
        );
        if (null === $software) {
            throw new UnsupportedSoftware($dto->software);
        }

        if ($dto->isJobsOrInvoiceFile) {
            $this->fieldServicesUploadService->preProcessJobsOrInvoiceFile(
                $uploadedFilePath,
                $company,
                $trade,
                $software
            );
        } elseif ($dto->isActiveClubMemberFile) {
            $this->fieldServicesUploadService->preProcessMembersFile(
                $uploadedFilePath,
                $company,
                true,
                $trade,
                $software
            );
        } elseif ($dto->isMemberFile) {
            $this->fieldServicesUploadService->preProcessMembersFile(
                $uploadedFilePath,
                $company,
                false,
                $trade,
                $software
            );
        } else {
            $message = 'Please specify one of: 1) isJobsOrInvoiceFile, 2) isActiveClubMemberFile, 3) isMemberFile';
            throw new IntentWasUnclear($message);
        }

        $import = new CompanyDataImportJob();
        $import->setJobsOrInvoiceFile($dto->isJobsOrInvoiceFile);
        $import->setActiveClubMemberFile($dto->isActiveClubMemberFile);
        $import->setMemberFile($dto->isMemberFile);
        $import->setIntacctId($company->getIntacctId());

        $import->setTrade($dto->trade ?? '');
        $import->setSoftware($dto->software ?? '');
        $import->setFilePath($uploadedFilePath);

        $import->setStatus('PENDING');
        $import->setProgress('');
        $import->setCompany($company);

        $this->em->persist($import);
        $this->em->flush();

        $this->bus->dispatch(
            new CompanyFieldServiceImportCreated($import->getId())
        );

        return $import->getId();
    }
}
