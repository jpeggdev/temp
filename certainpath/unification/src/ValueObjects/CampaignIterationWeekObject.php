<?php

namespace App\ValueObjects;

class CampaignIterationWeekObject extends AbstractObject
{
    public ?int $campaignIterationId = null;
    public ?int $weekNumber = null;
    public bool $isMailingDropWeek = false;
    public ?string $startDate = null;
    public ?string $endDate = null;

    public function fromArray(array $data): static
    {
        $this->campaignIterationId = $data['campaign_iteration_id'] ?? null;
        $this->weekNumber = $data['week_number'] ?? null;
        $this->startDate = $data['start_date'] ?? null;
        $this->isMailingDropWeek = $data['is_mailing_drop_week'] ?? false;
        $this->endDate = $data['end_date'] ?? null;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'campaign_iteration_id' => $this->campaignIterationId,
            'week_number' => $this->weekNumber,
            'is_mailing_drop_week' => $this->isMailingDropWeek,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'updated_at' => $this->formatDate($this->updatedAt),
            'created_at' => $this->formatDate($this->updatedAt),
        ];
    }

    public function getTableName(): string
    {
        return 'campaign_iteration_week';
    }

    public function getTableSequence(): string
    {
        return 'campaign_iteration_week_id_seq';
    }

    public function populate(): static
    {
        return $this;
    }
}
