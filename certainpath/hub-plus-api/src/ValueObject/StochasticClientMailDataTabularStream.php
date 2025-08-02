<?php

namespace App\ValueObject;

use App\DTO\Response\StochasticClientMailDataRowDTO;

readonly class StochasticClientMailDataTabularStream
{
    /**
     * @param StochasticClientMailDataRowDTO[] $mailDataRows
     */
    private function __construct(
        private array $mailDataRows,
    ) {
    }

    /**
     * @param StochasticClientMailDataRowDTO[] $mailDataRows
     */
    public static function fromDtoArray(array $mailDataRows): self
    {
        return new self($mailDataRows);
    }

    public function asGenerator(): \Generator
    {
        $mailDataRows = $this->mailDataRows;
        return (static function () use ($mailDataRows) {
            yield [
                'ID',
                'Intacct ID',
                'Client Name',
                'Campaign ID',
                'Campaign Name',
                'Job Number',
                'Status',
                'Projected Qty',
                'Actual Qty',
                'Material Cost',
                'Postage Cost',
                'Total Cost',

                'Week',
                'Year',
                'Start Date',
                'End Date',

                'Product ID',
                'Product Name',
                'Product Type',
                'Product Description',
                'Product Code',
                'Product Distribution Method',
                'Product Mailer Description',
                'Product Format',
                'Product Prospect Price',
                'Product Customer Price',
            ];
            foreach ($mailDataRows as $row) {
                yield [
                    $row->id,
                    $row->intacctId,
                    $row->clientName,
                    $row->campaignId,
                    $row->campaignName,
                    $row->batchNumber,
                    $row->batchStatus,
                    $row->prospectCount,
                    $row->batchPricing?->actualQuantity,
                    $row->batchPricing?->materialExpense,
                    $row->batchPricing?->postageExpense,
                    $row->batchPricing?->totalExpense,

                    $row->week,
                    $row->year,
                    $row->startDate,
                    $row->endDate,

                    $row->productId,
                    $row->campaignProduct?->name,
                    $row->campaignProduct?->type,
                    $row->campaignProduct?->description,
                    $row->campaignProduct?->code,
                    $row->campaignProduct?->distributionMethod,
                    $row->campaignProduct?->mailerDescription,
                    $row->campaignProduct?->format,
                    $row->campaignProduct?->prospectPrice,
                    $row->campaignProduct?->customerPrice,
                ];
            }
        })();
    }
}
