<?php

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateMyUserProfileRequestDTO
{
    #[Assert\NotBlank(message: 'First name cannot be blank.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'First name cannot be longer than 255 characters.'
    )]
    public string $firstName;

    #[Assert\NotBlank(message: 'Last name cannot be blank.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Last name cannot be longer than 255 characters.'
    )]
    public string $lastName;

    #[Assert\NotBlank(message: 'Work email cannot be blank.')]
    #[Assert\Email(
        message: 'Please enter a valid email address for the work email.'
    )]
    public string $workEmail;

    public function __construct(string $firstName, string $lastName, string $workEmail)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->workEmail = $workEmail;
    }
}
