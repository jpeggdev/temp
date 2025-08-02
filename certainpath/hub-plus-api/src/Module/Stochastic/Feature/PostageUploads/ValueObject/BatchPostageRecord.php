<?php

namespace App\Module\Stochastic\Feature\PostageUploads\ValueObject;

use App\ValueObject\AbstractRecord;

class BatchPostageRecord extends AbstractRecord
{
    public ?string $reference = null;
    public ?string $quantity_sent = null;
    public ?string $cost = null;
    public ?string $invoice_number = null;

    public function __construct()
    {
        $this->map = new BatchPostageRecordMap();
    }

    public static function getRecordInstance(): BatchPostageRecord
    {
        return new self();
    }

    public static function getOptionalFields(): array
    {
        return [
            'invoice_number' => true,
        ];
    }
}
