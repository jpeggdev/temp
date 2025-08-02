<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\ValueObject;

use App\Module\ServiceTitan\Enum\ServiceTitanEnvironment;

readonly class OAuthCredentials
{
    public function __construct(
        public string $clientId,
        public string $clientSecret,
        public ServiceTitanEnvironment $environment,
    ) {
    }

    public function isValid(): bool
    {
        return trim($this->clientId) !== '' && trim($this->clientSecret) !== '';
    }
}
