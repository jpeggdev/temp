<?php

namespace App\Module\Stochastic\Feature\Uploads\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class UploadCompanyProspectSourceDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 255)]
        public ?string $software = null,
        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 255)]
        public ?string $importType = null,
        #[Assert\Length(min: 1, max: 255)]
        public ?string $version = null,
        #[Assert\Length(min: 1, max: 255)]
        public ?string $tags = null,
    ) {
    }
}
