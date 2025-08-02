<?php

namespace App\Parsers\Mixins;

use function App\Functions\app_nullify;

trait PhoneNumberMixin
{

    protected function formatPhoneNumber(
        string $phoneNumber = null,
        string $defaultAreaCode = null,
        string $defaultLabel = null
    ) : array
    {
        $record = [
            'index' => null,
            'human' => null,
            'label' => null
        ];

        // Extract Labels
        $labels = [ ];

        preg_match(
            '/[a-zA-Z]+/i',
            $phoneNumber,
            $labels
        );

        // Remove Non-Numeric Characters
        $phoneNumber = preg_replace(
            '/[^0-9]/', '', $phoneNumber
        );

        if (strlen($phoneNumber) >= 7) {
            if (7 === strlen($phoneNumber)) {
                $phoneNumber = $defaultAreaCode . $phoneNumber;
            }

            if (11 === strlen($phoneNumber)) {
                $phoneNumber = substr($phoneNumber, 1, 10);
            }

            if (10 === strlen($phoneNumber)) {
                $label = array_shift($labels);

                $record['human'] = sprintf('(%s) %s-%s',
                    substr($phoneNumber, 0, 3),
                    substr($phoneNumber, 3, 3),
                    substr($phoneNumber, 6, 4)
                );

                $record['index'] = sprintf('+1%s',
                    $phoneNumber
                );

                $record['label'] = (
                    app_nullify($label) ??
                    app_nullify($defaultLabel)
                );
            }
        }

        return $record;
    }

}
