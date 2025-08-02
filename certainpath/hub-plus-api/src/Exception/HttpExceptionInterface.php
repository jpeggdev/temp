<?php

namespace App\Exception;

interface HttpExceptionInterface
{
    public function getStatusCode(): int;
}
