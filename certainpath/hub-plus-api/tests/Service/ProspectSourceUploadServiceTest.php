<?php

namespace App\Tests\Service;

use App\Entity\CompanyDataImportJob;
use App\Entity\FieldServiceSoftware;
use App\Exception\CompanyProcessDispatchException;
use App\Exception\CouldNotReadSheet;
use App\Exception\FieldsAreMissing;
use App\Exception\NoFilePathWasProvided;
use App\Exception\UnsupportedFileTypeException;
use App\Tests\AbstractKernelTestCase;
use League\Csv\Exception;
use League\Csv\SyntaxError;
use League\Csv\UnavailableStream;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ProspectSourceUploadServiceTest extends AbstractKernelTestCase
{
    public function setUp(): void
    {
        $this->doInitializeTrades = true;
        $this->doInitializeSoftware = true;
        parent::setUp();
    }
    /**
     * @throws FieldsAreMissing
     * @throws UnsupportedFileTypeException
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws \DateMalformedStringException
     * @throws CompanyProcessDispatchException
     * @throws ServerExceptionInterface
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws NoFilePathWasProvided
     * @throws IOException
     * @throws RedirectionExceptionInterface
     * @throws UnavailableStream
     * @throws TransportExceptionInterface
     * @throws ReaderNotOpenedException
     * @throws CouldNotReadSheet
     * @throws SyntaxError
     */
    public function testProspectSourceUpload(): void
    {
        $service = $this->getProspectSourceUploadService();
        self::assertNotNull($service);

        $companyIngestion = $this->getStochasticCompanyIngestionService();
        self::assertNotNull($companyIngestion);
        $companyIngestion->updateAllCompaniesFromStochasticRoster();
        $testCompany = $this->companyRepository->findOneByIdentifier('SM000250');

        $software = $this->fieldServiceSoftwareRepository->getSoftware(
            FieldServiceSoftware::serviceTitan()
        );

        $import = new CompanyDataImportJob();
        $import->setIntacctId($testCompany->getIntacctId());
        $import->setCompany($testCompany);
        $import->setFilePath(__DIR__.'/../Files/ACXIOM/from-eric-broken-acxiom.xlsx');
        $import->setStatus('PENDING');
        $import->setProgress('');
        $import->setTag('test');
        $import->setSoftware($software->getName());
        $import->setTrade('');
        $import->setMemberFile(false);
        $import->setActiveClubMemberFile(false);
        $import->setJobsOrInvoiceFile(false);
        $import->setProspectsFile(true);
        $import->setId(101);

        $this->ingestRepository->deleteAllRecordsForTenantAndTable(
            $testCompany->getIntacctId(),
            'prospects_stream'
        );

        $service->processProspectsFile($import);

        $this->ingestRepository->deleteAllRecordsForTenantAndTable(
            $testCompany->getIntacctId(),
            'prospects_stream'
        );
    }
}
