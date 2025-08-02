<?php

namespace App\Exceptions;

use App\Importers\AbstractImporter;

class PartialProcessingException extends AppException
{
    private AbstractImporter $importResult;

    public function setExceptions(array $exceptions): void
    {
        $this->message = $this->getDefaultMessage() . PHP_EOL;
        foreach ($exceptions as $exception) {
            $this->message .= $exception->getMessage() . PHP_EOL;
        }
    }

    protected function getDefaultMessage(): string
    {
        return 'Partial Processing Exceptions: ';
    }

    public function setImportResult(AbstractImporter $importer): void
    {
        $this->importResult = $importer;
    }

    public function getImportResult(): AbstractImporter
    {
        return $this->importResult;
    }
}
