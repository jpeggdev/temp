<?php

declare(strict_types=1);

namespace App\Service\Event;

use App\DTO\Response\Event\ValidateEventNameResponseDTO;
use App\Repository\EventRepository\EventRepository;

readonly class ValidateEventCodeService
{
    public function __construct(
        private EventRepository $eventRepository,
    ) {
    }

    public function codeExists(string $eventCode, ?string $eventUuid = null): ValidateEventNameResponseDTO
    {
        $existingEvent = $this->eventRepository->findOneBy(['eventCode' => $eventCode]);

        $exists = false;
        $message = sprintf('The event code "%s" is available', $eventCode);

        if (null !== $existingEvent) {
            if ($eventUuid && $existingEvent->getUuid() === $eventUuid) {
                $message = sprintf(
                    'The event code "%s" is already used by THIS event (that is fine).',
                    $eventCode
                );
            } else {
                $exists = true;
                $message = sprintf('The event code "%s" already exists', $eventCode);
            }
        }

        return new ValidateEventNameResponseDTO(
            codeExists: $exists,
            message: $message,
        );
    }
}
