<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Exception;

class BulkOperationException extends \Exception
{
    private array $failedItems = [];

    public function setFailedItems(array $failedItems): self
    {
        $this->failedItems = $failedItems;

        return $this;
    }

    public function getFailedItems(): array
    {
        return $this->failedItems;
    }
}
