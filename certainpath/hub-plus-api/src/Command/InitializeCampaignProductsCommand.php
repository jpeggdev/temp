<?php

namespace App\Command;

use App\Exception\CouldNotReadSheet;
use App\Exception\ExcelFileIsCorrupted;
use App\Exception\FieldsAreMissing;
use App\Exception\NoFilePathWasProvided;
use App\Exception\UnsupportedFileTypeException;
use App\Repository\CampaignProductRepository;
use League\Csv\Exception;
use League\Csv\SyntaxError;
use League\Csv\UnavailableStream;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'hub:campaign-products:initialize')]
class InitializeCampaignProductsCommand extends Command
{
    public function __construct(
        private readonly CampaignProductRepository $campaignProductRepository,
    ) {
        parent::__construct();
    }

    /**
     * @throws IOException
     * @throws FieldsAreMissing
     * @throws UnsupportedFileTypeException
     * @throws ExcelFileIsCorrupted
     * @throws UnavailableStream
     * @throws ReaderNotOpenedException
     * @throws CouldNotReadSheet
     * @throws SyntaxError
     * @throws Exception
     * @throws NoFilePathWasProvided
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $this
            ->campaignProductRepository
            ->initializeCampaignProducts(
                $input->getOption('csv'),
            );

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addOption(
            'csv',
            'csv',
            InputOption::VALUE_OPTIONAL,
            'Path to Taxonomy CSV File'
        );
    }
}
