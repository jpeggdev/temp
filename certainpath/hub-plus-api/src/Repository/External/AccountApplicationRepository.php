<?php

namespace App\Repository\External;

use App\DTO\CompanyDTO;
use App\SQL\Report;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

readonly class AccountApplicationRepository
{
    public function __construct(
        private Connection $accountApplicationConnection,
    ) {
    }

    /**
     * @return CompanyDTO[]
     *
     * @throws Exception
     */
    public function getAllActiveCompanies(): array
    {
        $accountsData = $this
            ->accountApplicationConnection
            ->fetchAllAssociative(
                Report::ACTIVE_MEMBERSHIP_QUERY
            );

        return array_map(static fn ($data) => new CompanyDTO($data), $accountsData);
    }
}
