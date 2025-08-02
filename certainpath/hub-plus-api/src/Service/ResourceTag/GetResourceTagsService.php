<?php

declare(strict_types=1);

namespace App\Service\ResourceTag;

use App\DTO\Request\ResourceTag\GetResourceTagsRequestDTO;
use App\DTO\Response\ResourceTag\GetResourceTagsResponseDTO;
use App\Repository\ResourceTagRepository;

readonly class GetResourceTagsService
{
    public function __construct(
        private ResourceTagRepository $resourceTagRepository,
    ) {
    }

    /**
     * @return array{
     *     tags: GetResourceTagsResponseDTO[],
     *     totalCount: int
     * }
     */
    public function getTags(GetResourceTagsRequestDTO $dto): array
    {
        $tags = $this->resourceTagRepository->findTagsByQuery($dto);
        $totalCount = $this->resourceTagRepository->countTagsByQuery($dto);

        $tagDtos = array_map(
            fn ($tag) => GetResourceTagsResponseDTO::fromEntity($tag),
            $tags
        );

        return [
            'tags' => $tagDtos,
            'totalCount' => $totalCount,
        ];
    }
}
