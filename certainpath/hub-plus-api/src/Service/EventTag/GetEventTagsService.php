<?php

declare(strict_types=1);

namespace App\Service\EventTag;

use App\DTO\Request\EventTag\GetEventTagsRequestDTO;
use App\DTO\Response\EventTag\GetEventTagsResponseDTO;
use App\Repository\EventTagRepository;

readonly class GetEventTagsService
{
    public function __construct(
        private EventTagRepository $eventTagRepository,
    ) {
    }

    /**
     * @return array{
     *     tags: GetEventTagsResponseDTO[],
     *     totalCount: int
     * }
     */
    public function getTags(GetEventTagsRequestDTO $dto): array
    {
        $tags = $this->eventTagRepository->findTagsByQuery($dto);
        $totalCount = $this->eventTagRepository->countTagsByQuery($dto);

        $tagDtos = array_map(
            fn ($tag) => GetEventTagsResponseDTO::fromEntity($tag),
            $tags
        );

        return [
            'tags' => $tagDtos,
            'totalCount' => $totalCount,
        ];
    }
}
