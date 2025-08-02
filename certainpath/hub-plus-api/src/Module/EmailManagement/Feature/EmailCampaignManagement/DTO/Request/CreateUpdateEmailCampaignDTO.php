<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailCampaignManagement\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class CreateUpdateEmailCampaignDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'The campaignName field cannot be empty')]
        public string $campaignName,
        #[Assert\NotNull(message: 'The email template id is required.')]
        #[Assert\NotBlank(message: 'The email template id cannot be blank.')]
        #[Assert\Positive(message: 'The email template id must be a positive integer.')]
        #[Assert\Type(type: 'integer', message: 'The email template id must be a valid integer.')]
        public int $emailTemplateId,
        #[Assert\NotNull(message: 'The event id is required.')]
        #[Assert\NotBlank(message: 'The event id cannot be blank.')]
        #[Assert\Positive(message: 'The event id must be a positive integer.')]
        #[Assert\Type(type: 'integer', message: 'The event id must be a valid integer.')]
        public int $eventId,
        #[Assert\NotNull(message: 'The session id is required.')]
        #[Assert\NotBlank(message: 'The session id cannot be blank.')]
        #[Assert\Positive(message: 'The session id must be a positive integer.')]
        #[Assert\Type(type: 'integer', message: 'The session id must be a valid integer.')]
        public int $sessionId,
        #[Assert\NotBlank(message: 'The sendOption field cannot be empty')]
        public string $sendOption,
        #[Assert\Type('string')]
        public ?string $description = null,
        #[Assert\Type('string')]
        public ?string $emailSubject = null,
        #[Assert\Type(type: 'array', message: 'The registration status ids must be an array.')]
        #[Assert\All([
            new Assert\Type(type: 'integer', message: 'Each registration status id must be an integer.'),
            new Assert\Positive(message: 'Each registration status id must be a positive integer.'),
        ])]
        public array $registrationStatusIds = [],
    ) {
    }
}
