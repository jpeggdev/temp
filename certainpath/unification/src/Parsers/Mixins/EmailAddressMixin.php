<?php

namespace App\Parsers\Mixins;

use function App\Functions\app_lower;
use function App\Functions\app_nullify;

trait EmailAddressMixin
{
    protected function formatEmailAddress(string $emailAddress = null): ?string
    {
        // Normalize Semi-Colons to Commas
        $emailAddress = str_replace(';', ',', $emailAddress);

        // Explode on Commas
        $emailAddressBits = explode(',', $emailAddress);

        foreach ($emailAddressBits as $emailAddressBit) {
            $emailAddresses[] = app_lower(trim($emailAddressBit));
        }

        // Convert Invalid Email Addresses to 'false'
        array_walk($emailAddresses, static function (&$emailAddress) {
            $emailAddress = filter_var($emailAddress, FILTER_VALIDATE_EMAIL);
        });

        // Filter Invalid Email Addresses
        $emailAddresses = array_unique(
            array_filter($emailAddresses)
        );

        $emailAddress = app_nullify(
            array_shift($emailAddresses)
        );

        return $emailAddress;
    }
}
