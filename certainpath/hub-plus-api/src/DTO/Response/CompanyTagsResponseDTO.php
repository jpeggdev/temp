<?php

namespace App\DTO\Response;

class CompanyTagsResponseDTO
{
    public function __construct(
        public array $tags = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data
        );
    }
}
