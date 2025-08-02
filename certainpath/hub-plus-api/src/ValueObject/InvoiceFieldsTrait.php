<?php

namespace App\ValueObject;

trait InvoiceFieldsTrait
{
    public ?string $invoice_number = null;
    public ?string $job_number = null;
    public ?string $first_appointment = null;
    public ?string $summary = null;
    public ?string $total = null;
    public ?string $hub_plus_import_id = null;
    public ?string $job_type = null;
    public ?string $invoice_summary = null;
    public ?string $zone = null;
}
