<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\IdentityCreationDTO;
use App\DTO\Request\Employee\CreateEmployeeDTO;
use App\Exception\UnableToCreateIdentityException;
use Auth0\SDK\Auth0;
use Auth0\SDK\Configuration\SdkConfiguration;
use Auth0\SDK\Exception\ArgumentException;
use Auth0\SDK\Exception\ConfigurationException;
use Auth0\SDK\Exception\NetworkException;

class IdentityCreationService
{
    private Auth0 $auth0;
    private string $clientId;

    public function __construct(
        string $auth0Domain,
        string $auth0MachineToMachineClientId,
        string $auth0MachineToMachineClientSecret,
        string $auth0Audience,
    ) {
        $this->clientId = $auth0MachineToMachineClientId;

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
            throw new UnableToCreateIdentityException(500, 'Unable to create the identity', $e);
        }
    }

    public function createIdentity(CreateEmployeeDTO $createUserDto): IdentityCreationDTO
    {
        try {
            $randomPassword = 'jk!L2@3k!L2@3k';

            $response = $this->auth0->management()->users()->create(
                'Username-Password-Authentication',
                [
                    'email' => $createUserDto->email,
                    'password' => $randomPassword,
                    'given_name' => $createUserDto->firstName,
                    'family_name' => $createUserDto->lastName,
                    'connection' => 'Username-Password-Authentication',
                    'email_verified' => true,
                ]
            );

            if (201 !== $response->getStatusCode()) {
                $contents = json_decode($response->getBody()->getContents(), true);
                $message = array_key_exists('message', $contents) ? $contents['message'] : '';
                throw new UnableToCreateIdentityException($contents['statusCode'], $message);
            }

            $this->auth0->authentication()->dbConnectionsChangePassword(
                $createUserDto->email,
                'Username-Password-Authentication',
                [
                    'client_id' => $this->clientId,
                ]
            );

            return IdentityCreationDTO::fromResponse($response);
        } catch (ArgumentException|NetworkException $e) {
            throw new UnableToCreateIdentityException(500, 'Unable to create the identity', $e);
        }
    }
}
