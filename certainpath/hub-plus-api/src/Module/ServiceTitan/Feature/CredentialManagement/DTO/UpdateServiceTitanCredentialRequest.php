<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\Feature\CredentialManagement\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateServiceTitanCredentialRequest
{
    #[Assert\NotBlank(message: 'Client ID is required.')]
    #[Assert\Length(min: 1, max: 255, minMessage: 'Client ID must be at least {{ limit }} characters long.', maxMessage: 'Client ID cannot be longer than {{ limit }} characters.')]
    public string $clientId;

    #[Assert\NotBlank(message: 'Client Secret is required.')]
    #[Assert\Length(min: 1, max: 255, minMessage: 'Client Secret must be at least {{ limit }} characters long.', maxMessage: 'Client Secret cannot be longer than {{ limit }} characters.')]
    public string $clientSecret;

    #[Assert\NotBlank(message: 'Environment is required.')]
    #[Assert\Choice(choices: ['integration', 'production'], message: 'Choose a valid environment (integration or production).')]
    public string $environment;
}
