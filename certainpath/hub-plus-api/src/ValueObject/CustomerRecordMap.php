<?php

namespace App\ValueObject;

abstract class CustomerRecordMap extends AbstractRecordMap
{
    use CoreFieldsTrait;

    public function __construct()
    {
        $this->tenant = 'Tenant,tenant';
        $this->trade = 'Trade,trade';
        $this->software = 'Software,software';
        $this->customer_id = 'Customer ID,customer_id,ID,id';
        $fullCustomerName = 'Customer Name,customer_name,Name,name';
        $firstName = 'Customer First Name,customer_first_name,First Name,first name,first_name';
        $lastName = 'Customer Last Name,customer_last_name,Last Name,first name,last_name';
        $this->customer_first_name =
            $firstName
        ;
        $this->customer_last_name =
            $lastName
        ;
        $this->customer_name =
            $fullCustomerName
        ;
        $this->customer_phone_numbers =
            'Customer Phone Number(s),customer_phone_numbers,'
            .
            'Phone Numbers,phone_numbers,Landline,landline,'
        ;
        $this->customer_phone_number_primary =
            'Phone,phone,Mobile,mobile,'
            .
            'Customer Phone,customer phone,Phone Number';
        $this->street =
            'Street,street,Customer Street,customer street,'
            .
            'Location Street,location street,Street Address,street address';
        $this->unit = 'Unit,unit,Customer Unit,customer unit';
        $this->city = 'City,city,Customer City,customer city,Location City,location city';
        $this->state = 'State,state,Customer State,customer state,Location State,location state';
        $this->zip =
            'Zip,zip,Zip Code,zip code,Postal Code,postal code,Post Code,post code,'
            .
            'Customer Zip,customer zip,Location Zip,location zip';
        $this->country = 'Country,country,Customer Country,customer country,Location Country,location country';
    }
}
