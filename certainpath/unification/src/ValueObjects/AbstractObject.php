<?php

namespace App\ValueObjects;

use DateTimeInterface;
use JsonException;

use function App\Functions\app_camelToSnake;
use function App\Functions\app_lower;
use function App\Functions\app_nullify;
use function App\Functions\app_sanitizeIterable;
use function App\Functions\app_snakeToCamel;

abstract class AbstractObject
{
    public int $_id = 0;
    public string $key = '';
    public int $companyId = 0;
    public ?int $tradeId = null;
    public ?string $tradeName = null;
    public ?string $company = null;
    public bool $isActive = true;
    public bool $isDeleted = false;
    public array $_extra = [ ];
    public ?DateTimeInterface $createdAt = null;
    public ?DateTimeInterface $updatedAt = null;
    public ?DateTimeInterface $imported = null;
    private string $json;
    protected array $_modified = [ ];

    public const ENABLED = 1;
    public const DISABLED = 0;
    public const IS_DELETED = 0;

    protected const KEY_FIELDS = [ ];

    public function __construct(array $record = [ ])
    {
        $record = app_sanitizeIterable($record);
        $this->isActive = (bool) static::ENABLED;
        $this->isDeleted = (bool) static::IS_DELETED;
        $this->imported = date_create();
        $this->createdAt = date_create();
        $this->updatedAt = date_create();
        $this->json = $this->toJson();
        $this->hydrate($record);
        $this->populate();
    }

    /**
     * Returns the name of the table this record
     * should be inserted into.
     */
    abstract public function getTableName(): string;

    /**
     * Returns the name of the sequence this record
     * should use to generate the primary key of the
     * inserted record.
     */
    abstract public function getTableSequence(): string;

    abstract public function populate(): static;

    /**
     * Hydrates the record using a key/value array. Keys in the
     * array that are not properties of the class are ignored.
     */
    public function hydrate(array $record = [ ]): static
    {
        $modified = $this->_modified ?? [ ];
        foreach ($record as $key => $value) {
            $value = app_nullify($value);
            if (is_string($value)) {
                $value = @mb_convert_encoding($value, 'UTF-8', 'auto');
                if ($value === false) {
                    $value = null;
                }
            }
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
            $modified[$key] = $value;

            $key = app_snakeToCamel($key);
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
            $modified[$key] = $value;

            $key = app_camelToSnake($key);
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
            $modified[$key] = $value;
        }

        $this->_modified = array_intersect_key($modified, $this->toArray());
        return $this;
    }

    public function setId($_id): static
    {
        $this->_id = (int)$_id;

        return $this;
    }

    public function getId(): int
    {
        return $this->_id;
    }

    public function hasId(): bool
    {
        return ($this->getId() > 0);
    }

    public function getCreatedFmt(): string
    {
        return $this->formatDate(
            $this->createdAt
        );
    }

    public function getUpdatedFmt(): string
    {
        return $this->formatDate(
            $this->updatedAt
        );
    }

    public function getImportedFmt(): string
    {
        return $this->formatDate(
            $this->imported
        );
    }

    public function isValid(): bool
    {
        return false;
    }

    public function setIsActive(bool $bool): static
    {
        $this->isActive = $bool;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsDeleted(bool $bool): static
    {
        $this->isDeleted = $bool;

        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }

    public function toJson(): string
    {
        try {
            $this->json = json_encode($this, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $this->json = '[]';
        }

        return $this->json;
    }
    public function toArray(): array
    {
        return [ ];
    }

    /**
     * Formats a DateTimeInterface object
     * to a YYYY-MM-DD HH:MM:SS string.
     */
    public function formatDate(
        DateTimeInterface $dateTime = null
    ): ?string {
        if ($dateTime instanceof DateTimeInterface) {
            return $dateTime->format('Y-m-d H:i:s');
        }

        return null;
    }

    /**
     * Formats the components of an address into
     * a single string that can be easily stored.
     */
    protected function formatAddress(
        string $addressLine1 = null,
        string $addressLine2 = null,
        string $cityName = null,
        string $postalCode = null,
        string $stateCode = null
    ): ?string {
        $streetBits = array_filter([
            app_nullify($addressLine1),
            app_nullify($addressLine2)
        ]);

        $locationBits = array_filter([
            app_nullify($cityName),
            app_nullify($stateCode),
            app_nullify($postalCode)
        ]);

        $streetFormatted = app_nullify(
            implode(' ', $streetBits)
        );

        $locationFormatted = app_nullify(
            implode(' ', $locationBits)
        );

        $addressFormatted = implode(', ', array_filter([
            $streetFormatted, $locationFormatted
        ]));

        return app_nullify($addressFormatted);
    }

    protected function getHashedString(?string $str): string
    {
        if (empty($str)) {
            return '';
        }
        return md5($str);
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public static function createKey(array $array): ?string
    {
        $values = array_intersect_key(
            $array,
            array_flip(static::KEY_FIELDS),
        );

        return app_lower(preg_replace(
            "/\W+/",
            '',
            implode('', $values)
        ));
    }
}
