<?php

namespace App\MessageHandler;

use App\Message\SampleMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SampleMessageHandler
{
    public function __invoke(SampleMessage $message)
    {
        // ... do some work
    }
}
