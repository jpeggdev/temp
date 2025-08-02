<?php

namespace App\DTO\Request\Batch;

use Symfony\Component\Validator\Constraints as Assert;

class PatchBatchDTO
{
    private array $providedFields = [];

    #[Assert\NotBlank(message: 'The name field cannot be empty', allowNull: true)]
    public ?string $name = null;

    public ?string $description = null;

    #[Assert\NotBlank(message: 'The batch_status_id field cannot be empty', allowNull: true)]
    #[Assert\Positive(message: 'The batch_status_id must be a positive integer')]
    public ?int $batchStatusId = null;

    #[Assert\NotBlank(message: 'The batch_status field cannot be empty', allowNull: true)]
    public ?string $batchStatusName = null;

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

    public function setBatchStatusId(int $batchStatusId): static
    {
        $this->batchStatusId = $batchStatusId;
        $this->trackProvidedField('batchStatusId');

        return $this;
    }

    public function setBatchStatusName(string $batchStatusName): static
    {
        $this->batchStatusName = $batchStatusName;
        $this->trackProvidedField('batchStatus');

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
