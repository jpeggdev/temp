<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailTemplateManagement\Voter;

use App\Entity\EmailTemplate;
use App\Security\Voter\RoleSuperAdminVoter;

class EmailTemplateVoter extends RoleSuperAdminVoter
{
    public const string CREATE = 'EMAIL_TEMPLATE_CREATE';
    public const string READ = 'EMAIL_TEMPLATE_READ';
    public const string UPDATE = 'EMAIL_TEMPLATE_UPDATE';
    public const string DELETE = 'EMAIL_TEMPLATE_DELETE';
    public const string DUPLICATE = 'EMAIL_TEMPLATE_DUPLICATE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        $allowedAttributes = [
            self::CREATE,
            self::READ,
            self::UPDATE,
            self::DUPLICATE,
            self::DELETE,
        ];

        $isValidSubject = $subject instanceof EmailTemplate || null === $subject;

        return in_array($attribute, $allowedAttributes, true) && $isValidSubject;
    }
}
