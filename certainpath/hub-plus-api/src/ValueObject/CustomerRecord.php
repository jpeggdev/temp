<?php

namespace App\ValueObject;

use App\Exception\FieldsAreMissing;

abstract class CustomerRecord extends AbstractRecord
{
    use CoreFieldsTrait;

    /**
     * @throws FieldsAreMissing
     */
    public function processCustomerNames(): void
    {
        if ($this->allCustomerFieldsAreValid()) {
            return;
        }
        if (
            !$this->isEmpty($this->customer_name)
            && (
                $this->isEmpty($this->customer_first_name)
                || $this->isEmpty($this->customer_last_name)
            )
        ) {
            $names = explode(' ', $this->customer_name);
            if ($this->isEmpty($this->customer_first_name)) {
                $this->customer_first_name = $names[0];
            }
            if ($this->isEmpty($this->customer_last_name)) {
                $this->customer_last_name = $names[1] ?? null;
            }
        } elseif (
            $this->isEmpty($this->customer_name)
            && (
                !$this->isEmpty($this->customer_first_name)
                || !$this->isEmpty($this->customer_last_name)
            )
        ) {
            $this->customer_name =
                implode(
                    ' ',
                    [$this->customer_first_name, $this->customer_last_name]
                );
        } else {
            $message = 'customer_first_name, customer_last_name, or customer_name';
            $message .= ' for Customer: ';
            $message .= $this->customer_id;
            throw new FieldsAreMissing($message);
        }
    }

    /**
     * @throws FieldsAreMissing
     */
    public function validateFieldValues(): void
    {
        $invalid = [];
        $isZipCodeInvalid =
            $this->isEmpty($this->zip);
        $isStreetInvalid =
            $this->isEmpty($this->street);
        $isCityInvalid =
            $this->isEmpty($this->city);
        $isStateInvalid =
            $this->isEmpty($this->state);

        if ($isZipCodeInvalid) {
            $invalid[] = 'Zip Code is Empty';
        }
        if ($isStreetInvalid) {
            $invalid[] = 'Street is Empty';
        }
        if ($isCityInvalid) {
            $invalid[] = 'City is Empty';
        }
        if ($isStateInvalid) {
            $invalid[] = 'State is Empty';
        }

        if (!empty($invalid)) {
            throw new FieldsAreMissing(implode(', ', $invalid));
        }
    }

    private function allCustomerFieldsAreValid(): bool
    {
        return
            !$this->isEmpty($this->customer_name)
            && !$this->isEmpty($this->customer_first_name)
            && !$this->isEmpty($this->customer_last_name)
        ;
    }
}
