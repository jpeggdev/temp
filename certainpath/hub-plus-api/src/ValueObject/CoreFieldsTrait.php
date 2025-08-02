<?php

namespace App\ValueObject;

trait CoreFieldsTrait
{
    public ?string $tenant = null;
    public ?string $trade = null;
    public ?string $software = null;
    public ?string $customer_id = null;
    public ?string $customer_name = null;
    public ?string $customer_first_name = null;
    public ?string $customer_last_name = null;
    public ?string $customer_phone_numbers = null;
    public ?string $customer_phone_number_primary = null;
    public ?string $street = null;
    public ?string $unit = null;
    public ?string $city = null;
    public ?string $state = null;
    public ?string $zip = null;
    public ?string $country = null;
}
