<?php

namespace App\Exception;

class CampaignDetailsMetadataNotFoundException extends UnificationAPIException
{
    public function __construct(?\Exception $previous = null)
    {
        $message = 'Campaign details metadata not found.';
        parent::__construct($message, 0, $previous);
    }
}
