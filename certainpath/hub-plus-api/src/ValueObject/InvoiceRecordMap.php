<?php

namespace App\ValueObject;

class InvoiceRecordMap extends CustomerRecordMap
{
    use InvoiceFieldsTrait;

    public function __construct()
    {
        parent::__construct();
        $this->invoice_number = 'Invoice Number,invoice_number,Invoice #,invoice #';
        $this->job_number = 'Job Number,job_number,Job #,job #';
        $this->job_type = 'Job Type,job_type';
        $this->invoice_summary = 'Invoice Summary,invoice_summary';
        $this->zone = 'Zone,zone';
        $this->total = 'Total,total';
        $this->first_appointment =
            'First Appointment,first_appointment,'
            .
            'Invoice Date,invoice date';
        $this->summary = 'Summary,summary,description,Invoice Summary';
        $this->hub_plus_import_id = 'hub_plus_import_id';
    }
}
