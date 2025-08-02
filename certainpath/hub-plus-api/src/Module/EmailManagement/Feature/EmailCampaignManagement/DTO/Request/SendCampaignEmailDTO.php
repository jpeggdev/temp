<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailCampaignManagement\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class SendCampaignEmailDTO
{
    public function __construct(
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
        #[Assert\Type('string')]
        public ?string $emailSubject = null,
        #[Assert\Type(type: 'array', message: 'The email recipients must be an array.')]
        #[Assert\All([
            new Assert\Type(type: 'string', message: 'Each email recipient must be a string.'),
        ])]
        public array $emailRecipients = [],
    ) {
    }
}
