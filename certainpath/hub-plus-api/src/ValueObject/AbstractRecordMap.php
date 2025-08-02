<?php

namespace App\ValueObject;

abstract class AbstractRecordMap
{
    private static array $keyMapCache = [];

    public function getProperty(string $tabularKey): ?string
    {
        $tabularKey =
            NormalizedField::fromString($tabularKey)->getValue();
        if (isset(self::$keyMapCache[$tabularKey])) {
            return self::$keyMapCache[$tabularKey];
        }
        $reflectionClass = new \ReflectionClass($this);
        $properties = $reflectionClass->getProperties();
        foreach ($properties as $property) {
            if (!$property->isPublic()) {
                continue;
            }
            $propertyValue = $property->getValue($this);
            if (is_string($propertyValue)) {
                $propertyNames = explode(',', $propertyValue);
                foreach ($propertyNames as &$propertyName) {
                    $propertyName =
                        NormalizedField::fromString($propertyName)->getValue();
                }
                unset($propertyName);
                if (in_array($tabularKey, $propertyNames, true)) {
                    self::$keyMapCache[$tabularKey] = $property->getName();

                    return $property->getName();
                }
            }
        }
        self::$keyMapCache[$tabularKey] = null;

        return null;
    }
}
