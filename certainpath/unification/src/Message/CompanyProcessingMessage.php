<?php

namespace App\Message;

class CompanyProcessingMessage
{
    public bool $doCustomerProcessing = true;
    public string $companyIdentifier;
    public ?string $jobIdentifier = null;

    public function __construct(string $companyIdentifier)
    {
        $this->companyIdentifier = $companyIdentifier;
    }
}
