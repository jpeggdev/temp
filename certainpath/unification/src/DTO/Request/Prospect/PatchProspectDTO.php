<?php

namespace App\DTO\Request\Prospect;

class PatchProspectDTO
{
    public function __construct(
        public ?bool $doNotMail = null,
        public ?int $prospectId = null, // Kept for backward compatibility, not used
    ) {}
}
