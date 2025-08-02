<?php

namespace App\Services;
use App\Repository\InvoiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception;

readonly class InvoiceService
{
    public function __construct(
        private InvoiceRepository $invoiceRepository,
    ) {
    }

    /**
     * Retrieves invoice years from the `invoiced_at` field and fills in any missing years in the range.
     *
     * @throws Exception
     */
    public function getInvoiceYearsSequence(
        string $intacctId,
        ?int $yearsLimit = null
    ): ArrayCollection {
        $years = $this->invoiceRepository->fetchInvoicedAtYears($intacctId, $yearsLimit)->toArray();
        if (empty($years)) {
            return new ArrayCollection();
        }

        $firstYear = min($years);
        $lastYear = max($years);

        $range = range($firstYear, $lastYear);

        return new ArrayCollection($range);
    }
}
