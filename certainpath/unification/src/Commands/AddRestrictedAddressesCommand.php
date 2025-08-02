<?php

namespace App\Commands;

use App\Entity\Company;
use App\Entity\RestrictedAddress;
use App\Repository\CompanyRepository;
use App\Repository\RestrictedAddressRepository;
use App\ValueObjects\AddressObject;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use InvalidArgumentException;

#[AsCommand(
    name: 'unification:add-restricted-addresses',
    description: 'Add addresses to the restricted address table.',
)]
class AddRestrictedAddressesCommand extends Command
{
    public function __construct(
        private readonly CompanyRepository $companyRepository,
        private readonly RestrictedAddressRepository $restrictedAddressRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('filePath', InputArgument::REQUIRED);
        $this->addArgument('limit', InputArgument::OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filePath = $input->getArgument('filePath');
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException(sprintf('File path "%s" does not exist.', $filePath));
        }

        $limit = $input->getArgument('limit') ?? 0;

        $company = $this->companyRepository->findOneByIdentifier('UNI1');
        if (!$company instanceof Company) {
            throw new InvalidArgumentException('Company does not exist.');
        }

        $output->writeln(
            'Starting bulk migration...'
        );

        if (($handle = fopen($filePath, "rb")) !== false) {
            $i = 0;
            while (($data = fgetcsv($handle, null, ",")) !== false) {
                $restrictedAddressObject = new AddressObject([
                    'companyId' => $company->getId(),
                    'name' => $data[0],
                    'address1' => $data[1],
                    'city' => $data[2],
                    'stateCode' => $data[3],
                    'postalCode' => $data[4],
                    'countryISOCode' => 'US',
                ]);

                $restrictedAddressObject->externalId = $restrictedAddressObject->getKey();

                if (!$restrictedAddressObject->isValid()) {
                    $output->writeln(
                        sprintf(
                            'Invalid record: %s',
                            json_encode($restrictedAddressObject)
                        )
                    );
                    continue;
                }

                if (
                    $this->restrictedAddressRepository->findOneByExternalId(
                        $restrictedAddressObject->externalId
                    )
                ) {
                    $output->writeln(
                        sprintf(
                            'Record found for: %s',
                            $restrictedAddressObject->externalId
                        )
                    );
                    continue;
                }

                $this->restrictedAddressRepository->persist(
                    (new RestrictedAddress())->fromValueObject($restrictedAddressObject)
                );

                $output->writeln(
                    sprintf('Adding record %s', $i)
                );

                if (
                    !empty($limit) &&
                    $i >= ($limit - 1)
                ) {
                    $output->writeln(
                        sprintf('Limit of %s reached.', $limit)
                    );
                    $this->restrictedAddressRepository->flush();
                    break;
                }

                if ($i % 100 === 0) {
                    $output->writeln(
                        'Flushing.'
                    );
                    $this->restrictedAddressRepository->flush();
                }

                $i++;
            }
            fclose($handle);
        }

        return Command::SUCCESS;
    }
}
