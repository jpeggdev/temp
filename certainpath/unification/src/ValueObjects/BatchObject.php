<?php

namespace App\ValueObjects;

class BatchObject extends AbstractObject
{
    public ?string $name = null;
    public ?int $campaignId = null;
    public ?int $campaignIterationId = null;
    public ?int $campaignIterationWeekId = null;
    public ?int $batchStatusId = null;

    public function fromArray(array $data): static
    {
        $this->name = $data['name'] ?? null;
        $this->campaignId = $data['campaign_id'] ?? null;
        $this->campaignIterationId = $data['campaign_iteration_id'] ?? null;
        $this->campaignIterationWeekId = $data['campaign_iteration_week_id'] ?? null;
        $this->batchStatusId = $data['batch_status_id'] ?? null;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'campaign_id' => $this->campaignId,
            'campaign_iteration_id' => $this->campaignIterationId,
            'batch_status_id' => $this->batchStatusId,
            'updated_at' => $this->formatDate($this->updatedAt),
            'created_at' => $this->formatDate($this->updatedAt),
        ];
    }

    public function getTableName(): string
    {
        return 'batch';
    }

    public function getTableSequence(): string
    {
        return 'batch_id_seq';
    }

    public function populate(): static
    {
        return $this;
    }
}
