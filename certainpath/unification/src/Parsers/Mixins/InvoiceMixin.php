<?php

namespace App\Parsers\Mixins;

trait InvoiceMixin
{
    public static function isInvoiceNumberField(?string $str): bool
    {
        $str = trim($str ?? '');
        $str = str_replace('#', 'num', $str);
        $str = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $str));
        $str = str_replace(['id', 'number', 'invoice'], ['', 'num', 'inv'], $str);

        return str_contains($str, 'invnum');
    }

    public static function getInvoiceNumberFromRecord(array $record): string
    {
        foreach ($record as $key => $value) {
            if (
                is_string($value) &&
                self::isInvoiceNumberField($key)
            ) {
                return $value;
            }
        }

        return '';
    }
}
