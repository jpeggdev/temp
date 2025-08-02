<?php

declare(strict_types=1);

namespace App\Service\Event;

use App\DTO\Response\Event\GetCreateUpdateEventMetadataResponseDTO;
use App\Repository\TradeRepository;

readonly class GetCreateUpdateEventMetadataService
{
    public function __construct(
        private TradeRepository $tradeRepository,
    ) {
    }

    public function getCreateUpdateEventMetadata(): GetCreateUpdateEventMetadataResponseDTO
    {
        $trades = $this->tradeRepository->findAll();
        $tradeData = array_map(static function ($trade) {
            return [
                'id' => $trade->getId(),
                'name' => $trade->getName(),
            ];
        }, $trades);

        return new GetCreateUpdateEventMetadataResponseDTO(
            trades: $tradeData
        );
    }
}
