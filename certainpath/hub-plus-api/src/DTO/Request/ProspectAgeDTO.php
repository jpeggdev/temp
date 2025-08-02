<?php

declare(strict_types=1);

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Matches the structure:
 * "prospectAge": { "min": "40", "max": "90" }
 */
class ProspectAgeDTO
{
    public function __construct(
        #[Assert\Type('string', message: 'min must be a string')]
        public string $min,
        #[Assert\Type('string', message: 'max must be a string')]
        public string $max,
    ) {
    }
}
