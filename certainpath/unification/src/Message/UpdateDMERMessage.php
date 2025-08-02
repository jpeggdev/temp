<?php

namespace App\Message;

readonly class UpdateDMERMessage
{
    public function __construct(
        public string $companyIdentifier,
    ) {
    }
}