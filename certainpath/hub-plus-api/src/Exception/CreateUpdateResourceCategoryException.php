<?php

declare(strict_types=1);

namespace App\Exception;

class CreateUpdateResourceCategoryException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return '';
    }
}
