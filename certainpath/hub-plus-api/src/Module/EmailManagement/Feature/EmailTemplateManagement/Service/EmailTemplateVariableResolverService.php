<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailTemplateManagement\Service;

use App\Entity\EmailTemplate;
use App\Entity\EmailTemplateVariable;
use App\Entity\EventSession;

readonly class EmailTemplateVariableResolverService
{
    public function resolveEmailTemplateContent(
        EmailTemplate $emailTemplate,
        EventSession $eventSession,
    ): string {
        $emailTemplateContent = $emailTemplate->getEmailContent();
        $placeholdersMap = $this->generatePlaceholderToValueMap($eventSession);

        return $this->applyPlaceholdersToContent($emailTemplateContent, $placeholdersMap);
    }

    private function generatePlaceholderToValueMap(EventSession $eventSession): array
    {
        return [
            EmailTemplateVariable::SESSION_NAME => $eventSession->getEvent()?->getEventName() ?? '',
            EmailTemplateVariable::SESSION_START_DATE => $eventSession->getStartDate()->format('Y-m-d'),
            EmailTemplateVariable::SESSION_END_DATE => $eventSession->getEndDate()->format('Y-m-d'),
            EmailTemplateVariable::SESSION_START_TIME => $eventSession->getStartDate()->format('H:i'),
            EmailTemplateVariable::SESSION_END_TIME => $eventSession->getEndDate()->format('H:i'),
            EmailTemplateVariable::SESSION_TIME_ZONE => $eventSession->getStartDate()->getTimezone()->getName(),
            EmailTemplateVariable::EVENT_DESCRIPTION => $eventSession->getEvent()?->getEventDescription() ?? '',
            // EmailTemplateVariable::EVENT_IMAGE_URL => $eventSession->getEvent()?->getImageUrl() ?? '',
            EmailTemplateVariable::EVENT_TYPE => $eventSession->getEvent()?->getEventType()?->getName() ?? '',
            EmailTemplateVariable::EVENT_CATEGORY => $eventSession->getEvent()?->getEventCategory()?->getName() ?? '',
            EmailTemplateVariable::EVENT_VIRTUAL_LINK => $eventSession->getVirtualLink() ?? '',
        ];
    }

    private function applyPlaceholdersToContent(string $content, array $placeholdersMap): string
    {
        $placeholderPattern = '/\*\|[^*|]+\|\*/';

        return preg_replace_callback($placeholderPattern, static function ($matches) use ($placeholdersMap) {
            $placeholder = $matches[0];

            return $placeholdersMap[$placeholder] ?? $placeholder;
        }, $content);
    }
}
