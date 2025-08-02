<?php

namespace App\Module\Stochastic\Feature\PostageUploads\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UploadPostageExpenseDTO
{
    public const string VENDOR_USPS = 'usps';
    public const string TYPE_EXPENSE = 'expense';

    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 255)]
        public ?string $vendor = null,
        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 255)]
        public ?string $type = null,
    ) {
    }
}
