<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\Query\Tag\TagQueryDTO;
use App\DTO\Response\TagListResponseDTO;
use App\Entity\Tag;
use App\Repository\TagRepository;

readonly class TagQueryService
{
    public function __construct(
        private TagRepository $tagRepository
    ) {
    }

    /**
     * @return array{
     *     tags: TagListResponseDTO[],
     *     totalCount: int
     * }
     */
    public function getTags(TagQueryDTO $queryDto): array
    {
        $tags = $this->tagRepository->findByQuery($queryDto);
        $totalCount = $this->tagRepository->getTotalCount($queryDto);

        $tagDTOs = array_map(
            static fn (Tag $tag) => TagListResponseDTO::fromEntity($tag),
            $tags
        );

        return [
            'tags' => $tagDTOs,
            'total' => $totalCount,
            'currentPage' => $queryDto->page,
            'perPage' => $queryDto->pageSize,
        ];
    }
}
