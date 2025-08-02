<?php

namespace App\ValueObject;

use App\Module\Stochastic\Feature\Uploads\ValueObject\DoNotMailListRecordMap;

class DoNotMailListRecord extends AbstractRecord
{
    public ?string $address1 = null;
    public ?string $address2 = null;
    public ?string $city = null;
    public ?string $state = null;
    public ?string $zip = null;

    public function __construct()
    {
        $this->map = new DoNotMailListRecordMap();
    }

    public static function getRecordInstance(): DoNotMailListRecord
    {
        return new self();
    }

    public static function getOptionalFields(): array
    {
        return [];
    }
}
