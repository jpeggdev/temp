<?php

namespace App\Parsers;

use App\ValueObjects\{AbstractObject, CompanyObject};
use InvalidArgumentException;

use function App\Functions\app_getExternalId;
use function App\Functions\app_stringList;

abstract class AbstractParser
{
    protected CompanyObject $company;

    protected array $records = [ ];

    protected int $recordCount = 1;

    public function __construct(CompanyObject $company)
    {
        $this->company = $company;
        $this->records = [ ];
    }

    public function parse(
        array $headers = [ ],
        array $records = [ ]
    ): static {
        $missingHeaders = array_diff(
            $this::getRequiredHeaders(),
            $headers
        );

        if (count($missingHeaders) > 0) {
            throw new InvalidArgumentException(sprintf(
                "The records could not be parsed because they are missing the following headers: %s",
                app_stringList($missingHeaders)
            ));
        }

        $this->parseRecords($records);

        return $this;
    }

    /**
     * @return AbstractObject[]
     */
    public function getRecords(): array
    {
        return $this->records;
    }

    abstract public function getSourceId(): string;

    abstract public static function getRequiredHeaders(): array;

    abstract public static function getSourceName(): string;

    abstract public static function hasMatchingFileName(string $fileName): bool;

    public static function hasMatchingHeaders(array $headers): bool
    {
        if (
            method_exists(
                static::class,
                'getRequiredHeaders'
            )
        ) {
            if (empty(array_diff(static::getRequiredHeaders(), $headers))) {
                return true;
            }
        }

        return false;
    }

    protected function addRecord(AbstractObject $record): static
    {
        $this->records[] = $record;
        ++$this->recordCount;
        return $this;
    }

    protected function getCompanyIdentifier(): string
    {
        return $this->company->identifier;
    }

    protected function getCompanyId(): string
    {
        return $this->company->_id;
    }

    protected function getExternalId(string $externalId = null): ?string
    {
        return app_getExternalId('id', $externalId);
    }

    public function parseRecords(array $records = [ ]): bool
    {
        foreach ($records as $record) {
            $this->addRecord(
                $this->parseRecord($record)
            );
        }

        return true;
    }

    abstract public function parseRecord(array $record = [ ]): AbstractObject;
}
