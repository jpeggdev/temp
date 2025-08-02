<?php

namespace App\DTO\Request\Prospect;

class UpdateProspectDoNotMailDTO
{
    public function __construct(
        public ?bool $doNotMail = null
    ) {}

    public static function fromBool(?bool $doNotMail): self
    {
        return new self($doNotMail);
    }
}
