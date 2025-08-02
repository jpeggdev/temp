<?php

declare(strict_types=1);

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Matches each item in "zipCodes":
 * {
 *   "code": "37748",
 *   "avgSale": 0,
 *   "availableProspects": 2213,
 *   "selectedProspects": "2213",
 *   "filteredProspects": 2213
 * }
 */
class ZipCodeDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Postal code cannot be empty')]
        public string $code,
        #[Assert\Type(type: 'numeric', message: 'avgSale must be numeric')]
        public float $avgSale,
        #[Assert\Type(type: 'int', message: 'availableProspects must be an integer')]
        public int $availableProspects,
        #[Assert\Type(type: 'string', message: 'selectedProspects must be a string')]
        public ?string $selectedProspects,
        #[Assert\Type(type: 'int', message: 'filteredProspects must be an integer')]
        public int $filteredProspects,
    ) {
    }
}
