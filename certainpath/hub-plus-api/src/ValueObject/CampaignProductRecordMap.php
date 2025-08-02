<?php

namespace App\ValueObject;

class CampaignProductRecordMap extends AbstractRecordMap
{
    use CampaignProductFieldsTrait;

    public function __construct()
    {
        $this->name = 'Product/Service';
        $this->type = 'Type';
        $this->description = 'Description';
        $this->mailerDescription = 'Mailer Description,Customer Mailing Description';
        $this->code = 'Code';
        $this->meta = 'Meta';
        $this->prospectPrice = 'ACH Prospect';
        $this->customerPrice = 'ACH Customer';
    }
}
