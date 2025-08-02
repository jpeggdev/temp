<?php

namespace App\Module\Stochastic\Feature\Uploads\Service;

use App\Entity\Company;
use App\Entity\CompanyDataImportJob;
use App\Exception\CouldNotReadSheet;
use App\Exception\ExcelFileIsCorrupted;
use App\Exception\FieldsAreMissing;
use App\Exception\FileDoesNotExist;
use App\Exception\NoFilePathWasProvided;
use App\Exception\UnsupportedFileTypeException;
use App\Exception\UnsupportedImportTypeException;
use App\Message\CompanyProspectImportCreated;
use App\Module\Stochastic\Feature\Uploads\DTO\Request\UploadCompanyProspectSourceDTO;
use App\Service\AbstractUploadService;
use App\Service\ProspectSourceUploadService;
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

readonly class UploadCompanyProspectSourceService extends AbstractUploadService
{
    public function __construct(
        private EntityManagerInterface $em,
        private MessageBusInterface $bus,
        LoggerInterface $logger,
        private ProspectSourceUploadService $prospectSourceUploadService,
        string $tempDirectory,
    ) {
        parent::__construct(
            $tempDirectory,
            $logger
        );
    }

    /**
     * Move the file to a safe location, create/import a DB record, and
     * dispatch a message to handle the file asynchronously.
     *
     * @throws FileDoesNotExist
     * @throws UnsupportedImportTypeException
     * @throws CouldNotReadSheet
     * @throws ExcelFileIsCorrupted
     * @throws FieldsAreMissing
     * @throws NoFilePathWasProvided
     * @throws UnsupportedFileTypeException
     * @throws Exception
     * @throws SyntaxError
     * @throws UnavailableStream
     * @throws IOException
     * @throws ReaderNotOpenedException
     */
    public function handle(
        UploadCompanyProspectSourceDTO $dto,
        Company $company,
        UploadedFile $uploadedFile,
    ): int {
        // Validate the software/importType if needed
        if ('acxiom' !== $dto->software) {
            throw new UnsupportedImportTypeException($dto->software);
        }
        if ('prospects' !== $dto->importType) {
            throw new UnsupportedImportTypeException($dto->importType);
        }

        // Move the file to our temp directory
        $uploadedFilePath = $this->moveUploadedFile(
            $uploadedFile,
            $company,
            'prospect-sources'
        );

        // Create an import record in the DB (reuse CompanyDataImportJob or your own entity)
        $import = new CompanyDataImportJob();
        $import->setIntacctId($company->getIntacctId());
        $import->setCompany($company);
        $import->setFilePath($uploadedFilePath);
        $import->setStatus('PENDING');
        $import->setProgress('');
        $import->setTag($dto->tags ?? null);

        // Optionally store the software / trade / type in these or new fields
        $import->setSoftware($dto->software);
        $import->setTrade('');

        // Example: if you want to track “prospect” file differently:
        // You could add a new boolean in CompanyDataImportJob or store some text
        $import->setMemberFile(false);
        $import->setActiveClubMemberFile(false);
        $import->setJobsOrInvoiceFile(false);
        $import->setProspectsFile(true);

        $this->prospectSourceUploadService->preProcessProspectsFile($import);

        // Persist and flush
        $this->em->persist($import);
        $this->em->flush();

        // Dispatch a new message with this importId
        $this->bus->dispatch(
            new CompanyProspectImportCreated($import->getId())
        );

        // Return the importId so controller can respond
        return $import->getId();
    }
}
