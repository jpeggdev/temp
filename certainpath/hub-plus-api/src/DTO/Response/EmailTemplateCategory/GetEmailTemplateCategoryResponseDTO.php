<?php

declare(strict_types=1);

namespace App\DTO\Response\EmailTemplateCategory;

use App\Entity\EmailTemplateCategory;

class GetEmailTemplateCategoryResponseDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $displayedName,
        public array $color,
    ) {
    }

    public static function fromEntity(EmailTemplateCategory $category): self
    {
        return new self(
            $category->getId(),
            $category->getName(),
            $category->getDisplayedName(),
            self::prepareColorData($category),
        );
    }

    public static function prepareColorData(EmailTemplateCategory $emailTemplateCategory): array
    {
        $color = $emailTemplateCategory->getColor();

        return $color
            ? ['id' => $color->getId(), 'value' => $color->getValue()]
            : [];
    }
}
