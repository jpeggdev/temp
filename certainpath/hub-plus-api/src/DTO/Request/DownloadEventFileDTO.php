<?php

declare(strict_types=1);

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class DownloadEventFileDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'File ID is required')]
        #[Assert\Type('integer', message: 'File ID must be an integer')]
        public int $fileId,
    ) {
    }
}
