<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventVoucherManagement\Voter;

use App\Entity\EventVoucher;
use App\Security\Voter\RoleSuperAdminVoter;

class EventVoucherVoter extends RoleSuperAdminVoter
{
    public const string CREATE = 'EVENT_VOUCHER_CREATE';
    public const string READ = 'EVENT_VOUCHER_READ';
    public const string UPDATE = 'EVENT_VOUCHER_UPDATE';
    public const string DELETE = 'EVENT_VOUCHER_DELETE';
    public const string DUPLICATE = 'EVENT_VOUCHER_DUPLICATE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        $allowedAttributes = [
            self::CREATE,
            self::READ,
            self::UPDATE,
            self::DUPLICATE,
            self::DELETE,
        ];

        $isValidSubject = $subject instanceof EventVoucher || null === $subject;

        return in_array($attribute, $allowedAttributes, true) && $isValidSubject;
    }
}
