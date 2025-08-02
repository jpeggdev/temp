<?php

declare(strict_types=1);

namespace App\Command\QuickBooks;

use App\Constants\S3Buckets;
use App\Entity\QuickBooksReport;
use App\Enum\ReportType;
use App\Repository\CompanyRepository;
use App\Repository\QuickBooksReportRepository;
use App\Service\AmazonS3Service;
use App\Service\CreateTransactionListReportService;
use App\SQL\GetTransactionListReportSQL;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:quickbooks:generate-transaction-list',
    description: 'Generate Transaction List reports for QuickBooks Reporting.',
)]
class GenerateTransactionListCommand extends Command
{
    public function __construct(
        private readonly GetTransactionListReportSQL $getTransactionListReportSQL,
        private readonly CreateTransactionListReportService $createTransactionListReportService,
        private readonly QuickBooksReportRepository $quickBooksReportRepository,
        private readonly AmazonS3Service $s3FileUploader,
        private readonly LoggerInterface $quickbooksReportingLogger,
        private readonly CompanyRepository $companyRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('tenant', InputArgument::OPTIONAL, 'Tenant identifier')
            ->addArgument('report_id', InputArgument::OPTIONAL, 'Report ID to generate');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $tenant = $input->getArgument('tenant');
        $reportId = $input->getArgument('report_id');
        $io->title('Generating Transaction List Reports');

        if ($tenant) {
            $this->handleSingleTenant($tenant, $reportId, $io);
        } else {
            $companies = $this->companyRepository->findAllWithIntacctId();

            if (empty($companies)) {
                $io->success('No companies found with a non-null Intacct ID.');

                return Command::SUCCESS;
            }

            $io->text('Processing all unprocessed Transaction List reports for all companies with an Intacct ID.');
            foreach ($companies as $company) {
                if (null === $company->getIntacctId() || '' === $company->getIntacctId()) {
                    continue;
                }

                $tenantForCompany = $company->getIntacctId();
                try {
                    $unprocessedReports = $this->getUnprocessedReports($tenantForCompany);
                } catch (\Exception $e) {
                    $this->quickbooksReportingLogger->error(
                        "Error retrieving unprocessed Transaction List reports for Tenant: $tenantForCompany",
                        ['exception_message' => $e->getMessage()]
                    );
                    $io->error("Could not retrieve unprocessed reports for Tenant: $tenantForCompany");
                    continue;
                }

                if (empty($unprocessedReports)) {
                    $io->section("No unprocessed reports for Tenant: $tenantForCompany");
                    continue;
                }

                foreach ($unprocessedReports as $report) {
                    $tenantId = $report['tenant'];
                    $rptId = $report['report_id'];
                    $io->section("Processing report for Tenant: $tenantId, Report ID: $rptId");
                    $this->processReport($tenantId, $rptId, $io);
                }
            }
        }

        $io->success('All applicable Transaction List reports processed successfully.');

        return Command::SUCCESS;
    }

    private function handleSingleTenant(string $tenant, ?string $reportId, SymfonyStyle $io): void
    {
        if ($reportId) {
            $io->text("Processing report for Tenant: $tenant, Report ID: $reportId");
            $this->processReport($tenant, $reportId, $io);
        } else {
            $io->text("Processing all unprocessed reports for Tenant: $tenant");
            try {
                $unprocessedReports = $this->getUnprocessedReports($tenant);
            } catch (\Exception $e) {
                $this->quickbooksReportingLogger->error(
                    "Error retrieving unprocessed Transaction List reports for Tenant: $tenant",
                    ['exception_message' => $e->getMessage()]
                );
                $io->error("Could not retrieve unprocessed reports for Tenant: $tenant");

                return;
            }

            if (empty($unprocessedReports)) {
                $io->success("No unprocessed reports for Tenant: $tenant.");

                return;
            }

            foreach ($unprocessedReports as $report) {
                $rptId = $report['report_id'];
                $io->section("Processing report for Tenant: $tenant, Report ID: $rptId");
                $this->processReport($tenant, $rptId, $io);
            }
        }
    }

    private function getUnprocessedReports(?string $tenant = null): array
    {
        $processedReports = $this->quickBooksReportRepository->findBy([
            'reportType' => ReportType::TRANSACTION_LIST,
        ]);

        $processedKeys = [];
        foreach ($processedReports as $processedReport) {
            $key = $processedReport->getIntacctId().'|'.$processedReport->getReportId();
            $processedKeys[$key] = true;
        }

        $allReports = $this->getTransactionListReportSQL->getAllUniqueReports();
        $unprocessedReports = [];

        foreach ($allReports as $report) {
            $key = $report['tenant'].'|'.$report['report_id'];
            if (isset($processedKeys[$key])) {
                continue;
            }
            if (null !== $tenant && $report['tenant'] !== $tenant) {
                continue;
            }
            $unprocessedReports[] = $report;
        }

        return $unprocessedReports;
    }

    private function processReport(string $tenant, string $reportId, SymfonyStyle $io): void
    {
        try {
            $existingReport = $this->quickBooksReportRepository->findOneBy([
                'intacctId' => $tenant,
                'reportId' => $reportId,
                'reportType' => ReportType::TRANSACTION_LIST,
            ]);

            if ($existingReport) {
                $io->warning("Report for Tenant: $tenant, Report ID: $reportId has already been processed.");

                return;
            }

            $rawData = $this->getTransactionListReportSQL->execute($tenant, $reportId);
            if (empty($rawData)) {
                $io->error("No data found for Tenant: $tenant, Report ID: $reportId.");

                return;
            }

            $excelContent = $this->createTransactionListReportService->generateReport($rawData);
            $s3Key = 'reports/excel/transaction_list/'.$tenant.'_'.$reportId.'.xlsx';
            $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

            $objectUrl = $this->s3FileUploader->uploadFile(
                S3Buckets::MEMBERSHIP_FILES_BUCKET,
                $excelContent,
                $s3Key,
                $contentType
            );

            $reportEntity = new QuickBooksReport();
            $reportEntity->setIntacctId($tenant);
            $reportEntity->setReportId($reportId);
            $reportEntity->setReportType(ReportType::TRANSACTION_LIST);
            $reportDate = new \DateTime($rawData[0]['report_date']);
            $reportEntity->setDate($reportDate);
            $reportEntity->setBucketName(S3Buckets::MEMBERSHIP_FILES_BUCKET);
            $reportEntity->setObjectKey($s3Key);

            $this->quickBooksReportRepository->save($reportEntity, true);
            $io->success("Report generated and saved at: $objectUrl");
        } catch (\Exception $e) {
            $this->quickbooksReportingLogger->error(
                "Error processing report for Tenant: $tenant, Report ID: $reportId",
                [
                    'exception_message' => $e->getMessage(),
                    'tenant' => $tenant,
                    'report_id' => $reportId,
                ]
            );
            $io->error("Failed to process report for Tenant: $tenant, Report ID: $reportId.");
        }
    }
}
