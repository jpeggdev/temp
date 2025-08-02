<?php

namespace App\Service;

use App\Client\SalesforceClient;
use App\ValueObject\Roster\RosterCompany;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class SalesforceRosterService
{
    private int $total = 0;
    private int $counter = 0;

    public function __construct(
        private readonly SalesforceClient $salesforceClient,
        private readonly ApplicationSignalingService $signal,
    ) {
    }

    /**
     * @return RosterCompany[]
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getCompanies(?int $limit = null): array
    {
        $companies = $this->salesforceClient->getCompanies($limit);
        $salesforceCompanies = $companies['records'];
        $this->signal->console('Retrieved: '.count($salesforceCompanies).' companies');
        $users = $this->salesforceClient->getSalesforceUsers();
        $salesforceUsers = $users['records'];
        $this->signal->console('Retrieved: '.count($salesforceUsers).' users');
        $rosterCompanies = [];
        $this->total = count($salesforceCompanies);
        $this->counter = 0;
        foreach ($salesforceCompanies as $company) {
            $rosterCompany = $this->processAndGetSalesforceCompany(
                $company,
                $salesforceUsers
            );
            $rosterCompanies[] = $rosterCompany;
            ++$this->counter;
        }

        $this->signal->console('Returning: '.count($rosterCompanies).' companies');

        return $rosterCompanies;
    }

    public function setSignaling(OutputInterface $output): void
    {
        $this->signal->setOutput(
            $output
        );
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function getCompanyByIntacctId(string $intacctId): RosterCompany
    {
        $users = $this->salesforceClient->getSalesforceUsers();
        $salesforceUsers = $users['records'];
        $company = $this->salesforceClient->getCompanyByIntacctId(
            $intacctId
        )['records'][0];

        return $this->processAndGetSalesforceCompany(
            $company,
            $salesforceUsers
        );
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    private function processAndGetSalesforceCompany(
        array $company,
        array $salesforceUsers,
    ): RosterCompany {
        $intacctId = $company['IntacctID__c'];
        $employees = $this->salesforceClient->getEmployeesForCompanyIntacctId(
            $intacctId
        );
        $salesforceEmployees = $employees['records'];
        $this->signal->console(
            $intacctId.': '.$this->counter.' / '.$this->total.' Retrieved: '.count($salesforceEmployees).' employees'
        );

        return RosterCompany::fromSalesforceCompanyCoachEmployees(
            $company,
            $salesforceUsers,
            $salesforceEmployees,
        );
    }
}
