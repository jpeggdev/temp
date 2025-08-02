<?php

namespace App\ValueObject;

use App\Exception\FieldsAreMissing;

class InvoiceRecord extends CustomerRecord
{
    use InvoiceFieldsTrait;

    public function __construct()
    {
        $this->map = new InvoiceRecordMap();
    }

    public static function getRecordInstance(): InvoiceRecord
    {
        return new self();
    }

    public static function getOptionalFields(): array
    {
        return [
            'customer_phone_number_primary' => true,
            'customer_first_name' => true,
            'customer_last_name' => true,
            'customer_name' => true,
            'customer_phone_numbers' => true,
            'summary' => true,
            'country' => true,
            'unit' => true,
            'job_number' => true,
            'customer_id' => true,
            'job_type' => true,
            'invoice_summary' => true,
            'zone' => true,
        ];
    }

    /**
     * @throws FieldsAreMissing
     */
    public function validateFieldValues(): void
    {
        parent::validateFieldValues();

        $invalid = [];
        $isTotalAmountInvalid =
            $this->isEmpty($this->total)
        ;
        if ($isTotalAmountInvalid) {
            $invalid[] = 'Invoice Amount is Invalid';
        }

        if (!empty($invalid)) {
            throw new FieldsAreMissing(implode(', ', $invalid));
        }
    }
}
