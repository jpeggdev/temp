<?php

namespace App\Functions;

use Ramsey\Uuid\Uuid;
use DateTimeInterface;
use JsonException;
use Finfo;

use function function_exists;

if (!function_exists(__NAMESPACE__ . '\app_determineMimeType')) {
    function app_determineMimeType(string $filePath): ?string
    {
        if (is_readable($filePath)) {
            return (new Finfo(FILEINFO_MIME_TYPE))->file($filePath);
        }

        return null;
    }
}

if (!function_exists(__NAMESPACE__ . '\app_getExtensionFromMimeType')) {
    function app_getExtensionFromMimeType(string $mimeType = null): ?string
    {
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
        ];

        if (array_key_exists($mimeType, $extensions)) {
            return $extensions[$mimeType];
        }

        return null;
    }
}

if (!function_exists(__NAMESPACE__ . '\app_appendFileExtension')) {
    function app_appendFileExtension(string $str, string $extension = null): string
    {
        if (
            $extension &&
            strpos($str, '.' . $extension) === false
        ) {
            return implode('.', [$str, $extension]);
        }

        return $str;
    }
}

if (!function_exists(__NAMESPACE__ . '\app_generateHashFromFile')) {
    function app_generateHashFromFile(string $inputPath): false|string
    {
        return sha1_file($inputPath);
    }
}

if (!function_exists(__NAMESPACE__ . '\app_formatFilename')) {
    function app_formatFilename(string $fileName): ?string
    {
        if (0 === strlen(trim($fileName))) {
            return null;
        }

        // Sanitize File Name
        $fileName = trim(preg_replace(
            '/[^a-z0-9\_\-\.]/i',
            '',
            $fileName
        ));

        // Generate File Name If Necessary
        if (0 === stripos($fileName, '.')) {
            $fileNameBits = explode('.', $fileName);
            $fileExtension = array_pop($fileNameBits);

            $fileName = implode('.', [
                app_uuid4(), $fileExtension
            ]);
        }

        return $fileName;
    }
}

if (!function_exists(__NAMESPACE__ . '\app_generateToken')) {
    function app_generateToken(int $length): string
    {
        $sqlGenerateToken = trim("
            SELECT generate_token(:length)
        ");

        $token = app_db()->executeQuery($sqlGenerateToken, [
            'length' => $length
        ])->fetchOne();

        return $token;
    }
}

if (!function_exists(__NAMESPACE__ . '\app_getSanitizedHeaderValue')) {
    function app_getSanitizedHeaderValue(?string $column, ?int $idx = null): string
    {
        $column = str_replace(
            ['#', ' '],
            ['Number', '_'],
            $column
        );

        $column = app_nullify(preg_replace(
            '/[^A-Za-z0-9]/u',
            '',
            $column
        ));

        if (empty($column)) {
            $column = sprintf('Col%d', $idx);
        }

        return app_lower($column);
    }
}

if (!function_exists(__NAMESPACE__ . '\app_sanitizeIterable')) {
    function app_sanitizeIterable(iterable $record): array
    {
        return array_map(function ($item) {
            if (is_string($item)) {
                // Convert to UTF-8, replacing invalid characters
                return mb_convert_encoding($item, 'UTF-8', 'UTF-8');
            }
            if (is_array($item)) {
                return app_sanitizeIterable($item);
            }
            return $item; // Return non-string items as-is
        }, (array)$record);
    }
}

if (!function_exists(__NAMESPACE__ . '\app_nullify')) {
    function app_nullify($value): mixed
    {
        if (is_scalar($value)) {
            if (is_bool($value)) {
                return $value;
            }

            $value = trim($value);

            if (0 === strlen($value)) {
                return null;
            }

            $nullValues = [
                'null',
                'NULL',
            ];

            if (in_array($value, $nullValues)) {
                return null;
            }
        }

        return $value;
    }
}

if (!function_exists(__NAMESPACE__ . '\app_getBoolean')) {
    function app_getBoolean($value): bool
    {
        if (
            !is_bool($value) &&
            is_scalar($value)
        ) {
            $booleanValues = [
                'YES',
                'OK',
                'TRUE'
            ];

            $value = in_array(
                app_upper($value),
                $booleanValues
            );
        }

        return (bool)(int)$value;
    }
}

if (!function_exists(__NAMESPACE__ . '\app_getDecimal')) {
    function app_getDecimal($value, int $scale = 2): string
    {
        $value = (float)str_replace([',', '$'], '', $value);
        $value = bcdiv(round($value, $scale), 1, $scale);

        return $value;
    }
}

if (!function_exists(__NAMESPACE__ . '\app_coalesceDates')) {
    function app_coalesceDates(array $dates = [ ]): ?DateTimeInterface
    {
        $dateString = app_arrayToValue($dates);

        if (empty($dateString)) {
            return null;
        }

        $dateTime = date_create($dateString);

        if (!$dateTime instanceof DateTimeInterface) {
            return null;
        }

        return $dateTime;
    }
}

if (!function_exists(__NAMESPACE__ . '\app_coalesceValues')) {
    function app_coalesceValues(array $values = [ ])
    {
        $values = array_filter(
            $values,
            function ($value) {
                return app_nullify($value);
            }
        );

        return array_shift(
            $values
        );
    }
}

if (!function_exists(__NAMESPACE__ . '\app_getEarliestDate')) {
    function app_getEarliestDate(?string ...$dateStrings): DateTimeInterface
    {
        array_unshift($dateStrings, 'now');
        return date_create('@' . array_reduce($dateStrings, function ($c, $i) {
                $timestamp = strtotime($i);
                $c = ($timestamp && (!$c || $timestamp < $c)) ? $timestamp : $c;
                return $c;
        }));
    }
}

if (!function_exists(__NAMESPACE__ . '\app_arrayToValue')) {
    function app_arrayToValue(array $array): ?string
    {
        foreach ($array as $key => $value) {
            $value = app_nullify($value);

            if ($value) {
                return $value;
            }
        }

        return null;
    }
}

if (!function_exists(__NAMESPACE__ . '\app_stringList')) {
    function app_stringList(array $values = [ ], string $separator = ', '): ?string
    {
        return app_nullify(implode($separator, array_filter(
            $values,
            function ($value) {
                return !empty(trim($value));
            }
        )));
    }
}

if (!function_exists(__NAMESPACE__ . '\app_stringArray')) {
    function app_stringArray(array $array = [ ], string $separator = ', '): ?string
    {
        $values = [ ];
        $array = array_filter(
            $array,
            function ($value) {
                return !empty(trim($value));
            }
        );

        foreach ($array as $key => $value) {
            $values[] = sprintf("%s: %s", $key, $value);
        }

        return app_stringList($values, $separator);
    }
}

if (!function_exists(__NAMESPACE__ . '\app_findFirstName')) {
    function app_findFirstName($string): string
    {
        $bits = explode(' ', $string);

        return (string) ($bits[0] ?? '');
    }
}

if (!function_exists(__NAMESPACE__ . '\app_findLastName')) {
    function app_findLastName($string): string
    {
        $firstName = app_findFirstName($string);

        return trim(str_replace($firstName, '', $string));
    }
}

if (!function_exists(__NAMESPACE__ . '\app_formatName')) {
    function app_formatName(?string $firstName = '', ?string $middleName = '', ?string $lastName = ''): string
    {
        return trim(implode(' ', array_filter([
            $firstName,
            $middleName,
            $lastName,
        ])));
    }
}

if (!function_exists(__NAMESPACE__ . '\app_jsonEncodeSafe')) {
    function app_jsonEncodeSafe($payload = null): ?string
    {
        if (empty($payload)) {
            return null;
        }

        $encodeOptions = (
            JSON_PRETTY_PRINT |
            JSON_UNESCAPED_SLASHES
        );

        return app_nullify(json_encode($payload, $encodeOptions));
    }
}

if (!function_exists(__NAMESPACE__ . '\app_jsonDecodeSafe')) {
    function app_jsonDecodeSafe(string $payload = null): ?object
    {
        if (empty($payload)) {
            return null;
        }

        try {
            return json_decode(
                $payload,
                false,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $e) {
        }

        return null;
    }
}

if (!function_exists(__NAMESPACE__ . '\app_uuid4')) {
    function app_uuid4(): string
    {
        return (Uuid::uuid4()->toString());
    }
}

if (!function_exists(__NAMESPACE__ . '\app_getExternalId')) {
    function app_getExternalId(string $sourceId, string $externalId = null): ?string
    {
        if (!empty($externalId)) {
            $externalId = implode('.', [
                $sourceId,
                $externalId
            ]);
        }

        return $externalId;
    }
}

if (!function_exists(__NAMESPACE__ . '\app_snakeToCamel')) {
    function app_snakeToCamel(string $str): string
    {
        return lcfirst(
            str_replace(' ', '', ucwords(
                str_replace('_', ' ', $str)
            ))
        );
    }
}

if (!function_exists(__NAMESPACE__ . '\app_lower')) {
    function app_lower(?string $str): string
    {
        if ($str) {
            return strtolower($str);
        }
        return '';
    }
}

if (!function_exists(__NAMESPACE__ . '\app_upper')) {
    function app_upper(?string $str): string
    {
        if ($str) {
            return strtoupper($str);
        }
        return '';
    }
}

if (!function_exists(__NAMESPACE__ . '\app_camelToSnake')) {
    function app_camelToSnake(?string $string)
    {
        if (!$string) {
            return '';
        }
        return app_lower(
            preg_replace('/(?<!^)[A-Z]/', '_$0', $string)
        );
    }
}

if (!function_exists(__NAMESPACE__ . '\app_endTime')) {
    function app_endTime(float $startTime): string
    {
        return app_getDecimal(microtime(true) - $startTime, 2);
    }
}

if (!function_exists(__NAMESPACE__ . '\app_getTimestampInMilliseconds')) {
    function app_getTimestampInMilliseconds(): int
    {
        return (int) round(microtime(true) * 1000);
    }
}

if (!function_exists(__NAMESPACE__ . '\app_getPostalCodeShort')) {
    function app_getPostalCodeShort(string $string): string
    {
        return app_upper(substr(preg_replace(
            "/\W+/",
            '',
            $string
        ), 0, 5));
    }
}
