<?php

namespace App\Module\Stochastic\Feature\PostageUploadsSftp\Command;

use App\Module\Stochastic\Feature\PostageUploadsSftp\Service\SftpBatchPostageProcessorService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'hub:postage:process-sftp-directory',
    description: 'Process USPS postage files from SFTP directory with audit trail',
)]
class ProcessSftpPostageFilesCommand extends Command
{
    public function __construct(
        private readonly SftpBatchPostageProcessorService $processor
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('directory', InputArgument::REQUIRED, 'Directory containing SFTP files to process')
            ->addOption('reprocess', 'r', InputOption::VALUE_NONE, 'Reprocess files that have already been processed')
            ->setHelp('
This command processes all .txt files in the specified SFTP directory, importing postage data
into the batch_postage table with comprehensive audit trail tracking.

Examples:
  # Process all files in SFTP directory (skip already processed)
  bin/console hub:postage:process-sftp-directory /path/to/sftp/files

  # Reprocess all files including already processed ones
  bin/console hub:postage:process-sftp-directory /path/to/sftp/files --reprocess

The command will:
- Process all .txt files in the directory
- Extract Job ID, Number of Pieces, and Transaction Amount from each file
- Skip files that have already been processed (unless --reprocess is used)
- Create audit trail records in postage_processed_file table
- Report processing statistics and any errors
            ');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $directory = $input->getArgument('directory');
        $reprocess = $input->getOption('reprocess');

        if (!is_string($directory)) {
            $io->error('Directory argument must be a string');
            return Command::FAILURE;
        }

        if (!is_dir($directory)) {
            $io->error(sprintf('Directory "%s" does not exist or is not readable', $directory));
            return Command::FAILURE;
        }

        $io->title('SFTP Postage Files Processor');
        $io->section(sprintf('Processing directory: %s', $directory));

        if ($reprocess) {
            $io->note('Reprocess mode: Files will be processed even if already processed');
        } else {
            $io->note('Skip mode: Already processed files will be skipped');
        }

        try {
            // Count files first
            $files = glob($directory.'/*.txt');
            if ($files === false) {
                $files = [];
            }
            $totalFiles = count($files);

            if ($totalFiles === 0) {
                $io->warning('No .txt files found in the directory');
                return Command::SUCCESS;
            }

            $io->progressStart($totalFiles);

            // Process directory
            $summary = $this->processor->processDirectory($directory);

            $io->progressFinish();
            $io->newLine(2);

            // Display results
            $io->section('Processing Results');

            $io->definitionList(
                ['Total Files' => $summary->totalFiles],
                ['Processed Files' => sprintf('<fg=green>%d</>', $summary->processedFiles)],
                ['Skipped Files' => sprintf('<fg=yellow>%d</>', $summary->skippedFiles)],
                ['Failed Files' => $summary->failedFiles > 0 ? sprintf('<fg=red>%d</>', $summary->failedFiles) : '0'],
                ['Total Records' => sprintf('<fg=blue>%d</>', $summary->totalRecords)],
                ['Processing Time' => sprintf('%.2f seconds', $summary->processingTimeSeconds)],
                ['Success Rate' => sprintf('%.1f%%', $summary->getSuccessRate() * 100)]
            );

            if ($summary->hasErrors()) {
                $io->section('Errors');
                foreach ($summary->errors as $error) {
                    $io->error($error);
                }
            }

            if ($summary->isSuccessful()) {
                $io->success('All files processed successfully!');
                return Command::SUCCESS;
            }

            $io->warning(sprintf(
                'Processing completed with %d failed files. Check the errors above.',
                $summary->failedFiles
            ));
            return Command::FAILURE;

        } catch (\Exception $e) {
            $io->error(sprintf('Processing failed: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }
}
