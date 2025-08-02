<?php

namespace App\Commands;

use App\DTO\Request\Address\AddressEditDTO;
use App\DTO\Request\Address\RestrictedAddressEditDTO;
use App\Repository\AddressRepository;
use App\Repository\RestrictedAddressRepository;
use App\Services\Address\AddressService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'unification:verify-address',
    description: 'Verify an address.',
)]
class VerifyAddressCommand extends Command
{
    public function __construct(
        private readonly RestrictedAddressRepository $restrictedAddressRepository,
        private readonly AddressService $addressService,
        private readonly AddressRepository $addressRepository,
        private readonly ValidatorInterface $validator,
    ) {
        parent::__construct();
    }

    private static function mapArrayToObject(
        array $array,
        object $object
    ): AddressEditDTO|RestrictedAddressEditDTO {
        foreach ($array as $key => $value) {
            if (property_exists($object, $key)) {
                $object->$key = $value;
            }
        }
        return $object;
    }

    protected function configure(): void
    {
        $this->addOption(
            'json',
            null,
            InputOption::VALUE_OPTIONAL
        );
        $this->addOption(
            'addressId',
            null,
            InputOption::VALUE_OPTIONAL
        );
        $this->addOption(
            'restrictedAddressId',
            null,
            InputOption::VALUE_OPTIONAL
        );
        $this->addOption(
            'action',
            null,
            InputOption::VALUE_OPTIONAL
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $action = $input->getOption('action');
        $addressId = $input->getOption('addressId');
        $restrictedAddressId = $input->getOption('restrictedAddressId');

        if (
            $restrictedAddressId &&
            $restrictedAddress = $this->restrictedAddressRepository->find($restrictedAddressId)
        ) {
            $normalizedRestrictedAddress = $this->addressService->verifyAndNormalize($restrictedAddress);
            $this->addressService->resetIsGlobalDoNotMailForAddresses($restrictedAddress);

            $restrictedAddress = $this->restrictedAddressRepository->saveRestrictedAddress($normalizedRestrictedAddress);
            $this->addressService->propagateDoNotMailForRestrictedAddress($restrictedAddress);

            $output->writeln('<info>RestrictedAddress updated</info>');
        }

        if (
            $addressId &&
            $address = $this->addressRepository->find($addressId)
        ) {
            $normalizedAddress = $this->addressService->verifyAndNormalize($address);
            $this->addressRepository->saveAddress($normalizedAddress);
            $output->writeln('<info>Address updated</info>');
        }
        return Command::SUCCESS;
    }
}
