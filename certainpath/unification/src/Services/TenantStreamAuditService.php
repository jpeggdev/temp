<?php

namespace App\Services;

use App\Repository\Unmanaged\GenericIngestRepository;

readonly class TenantStreamAuditService
{
    public function __construct(
        private GenericIngestRepository $genericIngestRepository,
    ) {
    }

    public function countOutstandingRecordsForTenant(
        string $tenantIdentifier
    ): int {
        $db = $this->genericIngestRepository->getDatabase();
        if (!$db) {
            return 0;
        }

        $prospectTableStreamCount =
            $this->getProspectsCount($tenantIdentifier);

        $memberTableStreamCount =
            $this->getMembersCount($tenantIdentifier);

        $invoiceTableStreamCount =
            $this->getInvoiceCount($tenantIdentifier);

        return
            $prospectTableStreamCount
            + $memberTableStreamCount
            + $invoiceTableStreamCount;
    }

    /**
     * @param string $tenantIdentifier
     * @return int
     */
    public function getProspectsCount(string $tenantIdentifier): int
    {
        return $this->genericIngestRepository->count(
            'prospects_stream',
            ['tenant' => $tenantIdentifier]
        );
    }

    /**
     * @param string $tenantIdentifier
     * @return int
     */
    public function getMembersCount(string $tenantIdentifier): int
    {
        return $this->genericIngestRepository->count(
            'members_stream',
            ['tenant' => $tenantIdentifier]
        );
    }

    /**
     * @param string $tenantIdentifier
     * @return int
     */
    public function getInvoiceCount(string $tenantIdentifier): int
    {
        return $this->genericIngestRepository->count(
            'invoices_stream',
            ['tenant' => $tenantIdentifier]
        );
    }
}
