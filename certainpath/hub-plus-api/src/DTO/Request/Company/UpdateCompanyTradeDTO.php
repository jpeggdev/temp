<?php

declare(strict_types=1);

namespace App\DTO\Request\Company;

use Symfony\Component\Validator\Constraints as Assert;

readonly class UpdateCompanyTradeDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Positive]
        public int $tradeId,
    ) {
    }
}
