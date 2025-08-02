<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailTemplateManagement\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class CreateUpdateEmailTemplateDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $templateName,
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $emailSubject,
        #[Assert\NotBlank]
        public string $emailContent,
        #[Assert\Type('array')]
        #[Assert\Count(min: 1, minMessage: 'At least one category must be selected.')]
        public array $categoryIds,
    ) {
    }
}
