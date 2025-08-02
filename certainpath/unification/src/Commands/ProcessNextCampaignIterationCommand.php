<?php

namespace App\Commands;

use App\Services\CampaignIteration\ProcessNextCampaignIterationService;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'unification:campaigns:process-next-iteration',
    description: 'Process next campaign iterations',
)]
class ProcessNextCampaignIterationCommand extends Command
{
    public function __construct(
        private readonly ProcessNextCampaignIterationService $service,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'iterationStartDate',
            InputArgument::OPTIONAL,
            'Campaign iteration start date'
        );

        $this->addArgument(
            'campaignId',
            InputArgument::OPTIONAL,
            'ID of the campaign'
        );
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->service->setOutput($output);
        $campaignId = $input->getArgument('campaignId');
        $iterationStartDate = $input->getArgument('iterationStartDate');
        $this->service->processCampaignsIterations(
            $campaignId,
            $iterationStartDate,
        );
        return Command::SUCCESS;
    }
}
