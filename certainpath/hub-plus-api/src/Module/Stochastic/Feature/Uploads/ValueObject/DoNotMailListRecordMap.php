<?php

namespace App\Module\Stochastic\Feature\Uploads\ValueObject;

use App\ValueObject\AbstractRecordMap;

class DoNotMailListRecordMap extends AbstractRecordMap
{
    public ?string $address1 = null;
    public ?string $address2 = null;
    public ?string $city = null;
    public ?string $state = null;
    public ?string $zip = null;

    public function __construct()
    {
        $this->address1 = 'Address,Address1,address,address1';
        $this->address2 = 'Address2,address2';
        $this->city = 'City,city';
        $this->state = 'State,state';
        $this->zip = 'ZIP,Zip,zip';
    }
}
