<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\EmailTemplateCategory;

class EmailTemplateCategorySecurityVoter extends RoleSuperAdminVoter
{
    public const string EMAIL_TEMPLATE_CATEGORY_MANAGE = 'EMAIL_TEMPLATE_CATEGORY_MANAGE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::EMAIL_TEMPLATE_CATEGORY_MANAGE === $attribute
            && ($subject instanceof EmailTemplateCategory || null === $subject);
    }
}
