<?php

namespace App\Commands;

use App\DTO\Domain\ProspectFilterRulesDTO;
use App\DTO\Request\Campaign\CreateCampaignDTO;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyCompletedException;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyCreatedException;
use App\Exceptions\DomainException\Campaign\CampaignAlreadyProcessingException;
use App\Exceptions\DomainException\Campaign\CampaignCreationFailedException;
use App\Exceptions\DomainException\CampaignIteration\CampaignIterationCannotBeCreatedException;
use App\Exceptions\NotFoundException\BatchNotFoundException;
use App\Exceptions\NotFoundException\BatchStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignIterationNotFoundException;
use App\Exceptions\NotFoundException\CampaignIterationStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignIterationWeekNotFoundException;
use App\Exceptions\NotFoundException\CampaignStatusNotFoundException;
use App\Exceptions\NotFoundException\CompanyNotFoundException;
use App\Exceptions\NotFoundException\MailPackageNotFoundException;
use App\Exceptions\NotFoundException\ProspectFilterRuleNotFoundException;
use App\Services\Campaign\CreateCampaignService;
use App\Services\ProspectFilterRule\ProspectFilterRuleRegistry;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'unification:campaign:init',
    description: 'Initialize a new mailing campaign',
)]
class InitCampaignCommand extends Command
{
    public function __construct(
        private readonly CreateCampaignService $campaignService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Name of the campaign'
            )
            ->addArgument(
                'startDate',
                InputArgument::REQUIRED,
                'Start date of the campaign (Y-m-d)'
            )
            ->addArgument(
                'endDate',
                InputArgument::REQUIRED,
                'End date of the campaign (Y-m-d)'
            )
            ->addArgument(
                'mailingFrequencyWeeks',
                InputArgument::REQUIRED,
                'Weeks interval for the campaign iteration'
            )
            ->addArgument(
                'companyIdentifier',
                InputArgument::REQUIRED,
                'ID of the company'
            )
            ->addArgument(
                'mailPackageName',
                InputArgument::REQUIRED,
                'Name of the mail package to be created'
            )
            ->addArgument(
                'description',
                InputArgument::OPTIONAL,
                'Description of the campaign'
            )
            ->addArgument(
                'phoneNumber',
                InputArgument::OPTIONAL,
                'Phone number associated with the campaign'
            )
            ->addArgument(
                'mailingDropWeeks',
                InputArgument::OPTIONAL,
                'JSON-encoded array of mailing drops weeks'
            )
            ->addArgument(
                'prospectFilterRules',
                InputArgument::OPTIONAL,
                'JSON-encoded array of prospect filter rules'
            );
    }

    /**
     * @throws ORMException
     * @throws \JsonException
     * @throws BatchNotFoundException
     * @throws CompanyNotFoundException
     * @throws BatchStatusNotFoundException
     * @throws MailPackageNotFoundException
     * @throws CampaignAlreadyCreatedException
     * @throws CampaignCreationFailedException
     * @throws CampaignStatusNotFoundException
     * @throws CampaignAlreadyCompletedException
     * @throws CampaignAlreadyProcessingException
     * @throws CampaignIterationNotFoundException
     * @throws ProspectFilterRuleNotFoundException
     * @throws CampaignIterationWeekNotFoundException
     * @throws CampaignIterationStatusNotFoundException
     * @throws CampaignIterationCannotBeCreatedException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dto = new CreateCampaignDTO(
            $input->getArgument('name'),
            $input->getArgument('startDate'),
            $input->getArgument('endDate'),
            $input->getArgument('mailingFrequencyWeeks'),
            $input->getArgument('companyIdentifier'),
            $input->getArgument('mailPackageName'),
            $input->getArgument('description'),
            $input->getArgument('phoneNumber'),
            $this->prepareMailingDropWeeksData($input),
            $this->prepareProspectFilterRulesData($input)
        );

        $this->campaignService->createCampaignSync($dto);
        $output->writeln('<info>Campaign successfully initialized!</info>');

        return Command::SUCCESS;
    }

    /**
     * @throws \JsonException
     */
    private function prepareMailingDropWeeksData(InputInterface $input): array
    {
        $mailingDropWeeksJson = $input->getArgument('mailingDropWeeks');
        $mailingDropWeeks = $mailingDropWeeksJson
            ? json_decode($mailingDropWeeksJson, true, 512, JSON_THROW_ON_ERROR)
            : null;

        return $mailingDropWeeks ?? range(1, $input->getArgument('mailingFrequencyWeeks'));
    }

    /**
     * @throws \JsonException
     */
    private function prepareProspectFilterRulesData(InputInterface $input): ProspectFilterRulesDTO
    {
        $prospectOnlyRule = ProspectFilterRuleRegistry::INCLUDE_PROSPECTS_ONLY_VALUE;
        $prospectFilterRulesArgument = $input->getArgument('prospectFilterRules');
        $prospectFilterRulesData = $prospectFilterRulesArgument
            ? json_decode($prospectFilterRulesArgument, true, 512, JSON_THROW_ON_ERROR)
            : [];

        return new ProspectFilterRulesDTO(
            intacctId: $prospectFilterRulesData['intacctId'] ?? '',
            customerInclusionRule: $prospectFilterRulesData['customerInclusionRule'] ?? $prospectOnlyRule,
            lifetimeValueRule: $prospectFilterRulesData['lifetimeValueRule'] ?? '',
            clubMembersRule: $prospectFilterRulesData['clubMembersRule'] ?? '',
            installationsRule: $prospectFilterRulesData['installationsRule'] ?? '',
            prospectMinAge: $prospectFilterRulesData['prospectMinAge'] ?? null,
            prospectMaxAge: $prospectFilterRulesData['prospectMaxAge'] ?? null,
            minHomeAge: $prospectFilterRulesData['minHomeAge'] ?? null,
            minEstimatedIncome: $prospectFilterRulesData['minEstimatedIncome'] ?? '',
        );
    }
}
