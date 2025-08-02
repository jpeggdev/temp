<?php

namespace App\Exceptions;

class PostProcessingServiceException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'Post processing error.';
    }
}