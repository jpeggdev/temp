<?php

namespace App\DTO\Request\Campaign;

use Symfony\Component\Validator\Constraints as Assert;

class PatchCampaignDTO
{
    private array $providedFields = [];

    #[Assert\NotBlank(message: 'The name field cannot be empty')]
    public string $name = '';

    public ?string $description = null;

    #[Assert\Regex(pattern: '/^\d{3}-\d{3}-\d{4}$/', message: 'Invalid phone number format')]
    public ?string $phoneNumber = null;

    #[Assert\NotBlank(message: 'The status field cannot be empty')]
    public ?int $status = null;

    // Track fields set through setter methods
    public function setName(string $name): static
    {
        $this->name = $name;
        $this->trackProvidedField('name');

        return $this;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        $this->trackProvidedField('description');

        return $this;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;
        $this->trackProvidedField('phoneNumber');

        return $this;
    }

    public function setStatus(?int $status): static
    {
        $this->status = $status;
        $this->trackProvidedField('status');

        return $this;
    }

    public function getProvidedFields(): array
    {
        return $this->providedFields;
    }

    private function trackProvidedField(string $field): void
    {
        if (!in_array($field, $this->providedFields, true)) {
            $this->providedFields[] = $field;
        }
    }
}
