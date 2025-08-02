<?php

namespace App\DTO\Query\Prospect;

use Symfony\Component\Validator\Constraints as Assert;

readonly class ProspectExportMetadataDTO
{
    public function __construct(
        #[Assert\Type('string')]
        public string $jobNumber = '',

        #[Assert\Type('string')]
        public string $ringTo = '',

        #[Assert\Type('string')]
        public string $versionCode = '',

        #[Assert\Type('string')]
        public string $csr = ''
    ) {
    }

    public function toArray(): array
    {
        return [
            'Job Number' => $this->jobNumber,
            'Ring To' => $this->ringTo,
            'Version Code' => $this->versionCode,
            'CSR Full Name' => $this->csr,
        ];
    }
}
