<?php

namespace App\ValueObjects;

class CampaignIterationObject extends AbstractObject
{
    public ?int $campaignId = null;
    public ?int $iterationNumber = null;
    public ?int $campaignIterationStatusId = null;
    public ?string $startDate = null;
    public ?string $endDate = null;

    public function fromArray(array $data): static
    {
        $this->campaignId = $data['campaign_id'] ?? null;
        $this->iterationNumber = $data['iteration_number'] ?? null;
        $this->campaignIterationStatusId = $data['campaign_iteration_status_id'] ?? null;
        $this->startDate = $data['start_date'] ?? null;
        $this->endDate = $data['end_date'] ?? null;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'campaign_id' => $this->campaignId,
            'iteration_number' => $this->iterationNumber,
            'campaign_iteration_status_id' => $this->campaignIterationStatusId,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'updated_at' => $this->formatDate($this->updatedAt),
            'created_at' => $this->formatDate($this->createdAt),
        ];
    }

    public function getTableName(): string
    {
        return 'campaign_iteration';
    }

    public function getTableSequence(): string
    {
        return 'campaign_iteration_id_seq';
    }

    public function populate(): static
    {
        return $this;
    }
}
