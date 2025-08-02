<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailCampaignManagement\Exception;

use App\Exception\UnificationAPIException;

class FailedToDeleteEmailCampaignException extends UnificationAPIException
{
    public function __construct()
    {
        $message = 'Failed to delete email campaign.';
        parent::__construct($message);
    }
}
