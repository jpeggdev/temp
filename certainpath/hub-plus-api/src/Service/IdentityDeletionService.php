<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\UnableToDeleteIdentityException;
use Auth0\SDK\Auth0;
use Auth0\SDK\Configuration\SdkConfiguration;
use Auth0\SDK\Exception\ArgumentException;
use Auth0\SDK\Exception\ConfigurationException;
use Auth0\SDK\Exception\NetworkException;

class IdentityDeletionService
{
    private Auth0 $auth0;

    public function __construct(
        string $auth0Domain,
        string $auth0MachineToMachineClientId,
        string $auth0MachineToMachineClientSecret,
        string $auth0Audience,
    ) {
        try {
            $configuration = new SdkConfiguration(
                strategy: SdkConfiguration::STRATEGY_API,
                domain: $auth0Domain,
                clientId: $auth0MachineToMachineClientId,
                clientSecret: $auth0MachineToMachineClientSecret,
                audience: [$auth0Audience],
            );
            $this->auth0 = new Auth0($configuration);
        } catch (ConfigurationException $e) {
            throw new UnableToDeleteIdentityException(500, 'Unable to configure Auth0', $e);
        }
    }

    public function deleteIdentity(string $ssoId): void
    {
        try {
            $response = $this->auth0->management()->users()->delete($ssoId);

            if (204 !== $response->getStatusCode()) {
                throw new UnableToDeleteIdentityException(500, 'Unable to delete identity: unexpected response status');
            }
        } catch (ArgumentException|NetworkException $e) {
            throw new UnableToDeleteIdentityException(500, 'Unable to delete the identity', $e);
        }
    }
}
