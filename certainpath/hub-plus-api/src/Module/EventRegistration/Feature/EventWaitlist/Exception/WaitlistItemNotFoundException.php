<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventWaitlist\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WaitlistItemNotFoundException extends NotFoundHttpException
{
}
