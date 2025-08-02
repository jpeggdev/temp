<?php

namespace App\Module\Stochastic\Feature\PostageUploads\ValueObject;

use App\ValueObject\AbstractRecordMap;

class BatchPostageRecordMap extends AbstractRecordMap
{
    public ?string $reference = null;
    public ?string $quantity_sent = null;
    public ?string $cost = null;
    public ?string $invoice_number = null;

    public function __construct()
    {
        $this->reference = 'Job ID,jobid,job_id';
        $this->quantity_sent = 'Number of Pieces,Pieces,pieces';
        $this->cost = 'Transaction Amount,Amount,amount';
        $this->invoice_number = 'Invoice Number';
    }
}
