<?php

namespace App\Services;

use App\Client\FileClient;
use App\Entity\Company;
use App\Entity\Prospect;
use App\Entity\SavedQuery;
use App\Exceptions\QueryManagerException;
use App\Exporters\AbstractExporter;
use App\Exporters\ExportFactory;
use App\Repository\CompanyRepository;
use App\Repository\SavedQueryRepository;
use App\ValueObjects\CompanyObject;
use App\ValueObjects\TempFile;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use RuntimeException;

readonly class ExportService
{
    public function __construct(
        private CompanyRepository $companyRepository,
        private FileConverter $fileConverter,
        private FileWriter $fileWriter,
        private QueryManager $queryManager,
        private SavedQueryRepository $savedQueryRepository,
        private EntityManagerInterface $entityManager,
        private FileClient $fileClient
    ) {
    }

    /**
     * @throws QueryManagerException
     */
    public function export(
        string $companyIdentifier,
        int $savedQueryId,
        string $dataTarget, //mailmanager
        string $dataType, //prospects
        array $options
    ): AbstractExporter {
        $company = $this->companyRepository->findOneBy([
            'identifier' => $companyIdentifier
        ]);

        if (!$company instanceof Company) {
            throw new InvalidArgumentException(sprintf("The company '%s' could not be found.", $companyIdentifier));
        }
        $this->fileConverter->setCompany($company);
        $companyObject = CompanyObject::fromEntity($company);

        if ($savedQueryId > 0) {
            $savedQuery = $this->savedQueryRepository->findOneBy([
                'id' => $savedQueryId,
                'company' => $company
            ]);

            if (!$savedQuery instanceof SavedQuery) {
                throw new InvalidArgumentException(sprintf("The saved query '%s' could not be found.", $savedQueryId));
            }
        } elseif ($dataType === 'prospects') {
            //most recent prospect created for a given address
            //exclude customer
            //exclude business address
            //is the name fully-populated
            //make sure prospect's address has been verified
            //prospect is_preferred is true
            //exclude prospects whose lifetime value is less than $2,500
            $query = $this
                ->entityManager
                ->createQueryBuilder()
                ->select('p')
                ->from(Prospect::class, 'p')
                ->join('p.company', 'a')
                ->where('a.identifier = :companyIdentifier')
                ->setParameter('companyIdentifier', $company->getIdentifier())
                ->orderBy('p.id', 'DESC')
                ->getQuery()
            ;
            $savedQuery = $this->queryManager->saveQuery(
                $company,
                $query,
                Prospect::class,
                'export' . time(),
                'All Records Service Export'
            );
        } else {
            throw new InvalidArgumentException(
                'Could not find or create query for export type: '
                . $dataType
                . ' and savedQueryId: '
                . $savedQueryId
            );
        }

        // Validate data-target, data-type
        $exportFactory = new ExportFactory(
            $this->entityManager,
            $this->fileClient,
            $dataTarget,
            $dataType
        );

        $records = $this->queryManager->getResultFromSavedQuery(
            $savedQuery
        );

        // Instantiate Exporter
        $export = $exportFactory
            ->getExporter($companyObject)
            ->setFileWriter($this->fileWriter)
            ->setFileConverter($this->fileConverter)
            ->setOptions($options);

        if ($export instanceof AbstractExporter) {
            $export->export($records);
            $exportedFilePath = $export->getOutputString();
            $exportedTempFile = TempFile::fromFullPath($exportedFilePath);
            $this->fileClient->upload(
                'stochastic-files',
                $exportedTempFile
            );
        } else {
            $exception = sprintf(
                "Unable to resolve exporter for %s",
                get_class($export)
            );

            throw new RuntimeException($exception, 1);
        }
        return $export;
    }
}
