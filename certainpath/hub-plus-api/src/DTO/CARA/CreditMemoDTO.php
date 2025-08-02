<?php

namespace App\DTO\CARA;

use App\Entity\CreditMemo;

class CreditMemoDTO
{
    /**
     * @var CreditMemoLineDTO[]
     */
    public array $lines;
    public \DateTimeImmutable $cmDate;
    public string $status;
    public float $totalAmount;
    public string $reason;
    public string $type;
    public string $uuid;

    public function __construct(
        array $lines,
        string $status,
        string $type,
        float $totalAmount,
        string $reason,
        string|\DateTimeImmutable $cmDate,
        string $uuid,
    ) {
        $this->lines = $lines;
        $this->status = $status;
        $this->totalAmount = $totalAmount;
        $this->reason = $reason;
        $this->type = $type;
        $this->uuid = $uuid;
        if (is_string($cmDate)) {
            try {
                $cmDate = new \DateTimeImmutable($cmDate);
            } catch (\Exception) {
                $cmDate = new \DateTimeImmutable();
            }
        }
        $this->cmDate = $cmDate;
    }

    public static function fromEntity(CreditMemo $entity): self
    {
        $lines = [];
        foreach ($entity->getCreditMemoLineItems() as $lineItem) {
            $lines[] = CreditMemoLineDTO::fromEntity($lineItem);
        }

        return new self(
            $lines,
            $entity->getStatus()->value,
            $entity->getType()->value,
            (float) $entity->getTotalAmount(),
            $entity->getReason() ?? '',
            $entity->getCmDate(),
            $entity->getUuid()
        );
    }
}
