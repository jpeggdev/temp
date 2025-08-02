<?php

namespace App\ValueObject;

use App\Exception\FieldsAreMissing;

abstract class AbstractRecord
{
    protected ?AbstractRecordMap $map = null;

    private static bool $fieldsAreValidated = false;

    abstract public static function getRecordInstance(): AbstractRecord;

    abstract public static function getOptionalFields(): array;

    /**
     * @throws FieldsAreMissing
     */
    private static function validateRecordInstance(AbstractRecord $record): void
    {
        if (!self::$fieldsAreValidated) {
            $optionalFields = static::getOptionalFields();
            $reflectionClass = new \ReflectionClass($record);
            $properties = $reflectionClass->getProperties(\ReflectionProperty::IS_PUBLIC);
            $missing = [];
            foreach ($properties as $property) {
                $name = $property->getName();
                $name = NormalizedField::fromString($name)->getValue();
                if (
                    !isset($record->$name)
                    && !isset($optionalFields[$name])
                ) {
                    $missing[] = $name;
                }
            }
            if ($missing) {
                throw new FieldsAreMissing(implode(', ', $missing));
            }
            self::$fieldsAreValidated = true;
        }
    }

    /**
     * @throws FieldsAreMissing
     */
    public static function fromTabularRecord(array $tabularRecord): self
    {
        $record = static::getRecordInstance();
        foreach ($tabularRecord as $tabularKey => $tabularValue) {
            $property = $record->map->getProperty($tabularKey);
            if ($property) {
                $record->$property = trim($tabularValue);
            }
        }
        self::validateRecordInstance($record);

        return $record;
    }

    public static function fromDatabaseRecord(array $databaseRecord): self
    {
        $record = static::getRecordInstance();
        $reflectionClass = new \ReflectionClass($record);

        foreach ($databaseRecord as $key => $value) {
            if ($reflectionClass->hasProperty($key)) {
                $property = $reflectionClass->getProperty($key);
                if ($property->isPublic()) {
                    $record->$key = $value;
                }
            }
        }

        return $record;
    }

    public function toArray(): array
    {
        $reflectionClass = new \ReflectionClass($this);
        $properties = $reflectionClass->getProperties(\ReflectionProperty::IS_PUBLIC);
        $array = [];

        foreach ($properties as $property) {
            $name = $property->getName();
            $array[$name] = $this->$name;
        }

        return $array;
    }

    protected function isEmpty(?string $field): bool
    {
        return
            null === $field
            || '' === trim($field)
        ;
    }
}
