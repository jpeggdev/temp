<?php

namespace App\ValueObject;

class ProspectRecord extends AbstractRecord
{
    use ProspectFieldsTrait;

    public function __construct()
    {
        $this->map = new ProspectRecordMap();
    }

    public static function getRecordInstance(): ProspectRecord
    {
        return new self();
    }

    public static function getOptionalFields(): array
    {
        return [
            'version' => true,
            'tag' => true,
            'country' => true,
            'phone' => true,
            'networthprem' => true,
            'estincome' => true,
            'areacode' => true,
            'phone_nospace' => true,
            'phoneflag' => true,
        ];
    }
}
