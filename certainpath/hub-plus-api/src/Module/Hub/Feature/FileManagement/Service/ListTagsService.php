<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Service;

use App\Entity\FileSystemNodeTag;
use App\Module\Hub\Feature\FileManagement\DTO\Response\ListTagsResponseDTO;
use App\Module\Hub\Feature\FileManagement\DTO\Response\TagSummaryDTO;
use App\Repository\FileSystemNodeTagRepository;

readonly class ListTagsService
{
    public function __construct(
        private FileSystemNodeTagRepository $tagRepository,
    ) {
    }

    public function listTags(): ListTagsResponseDTO
    {
        $tags = $this->tagRepository->findAll();

        $tagDTOs = array_map(
            fn (FileSystemNodeTag $tag) => new TagSummaryDTO(
                id: $tag->getId(),
                name: $tag->getName(),
                color: $tag->getColor(),
                createdAt: $tag->getCreatedAt()->format(\DateTimeInterface::ATOM),
                updatedAt: $tag->getUpdatedAt()->format(\DateTimeInterface::ATOM),
            ),
            $tags
        );

        return new ListTagsResponseDTO(
            tags: $tagDTOs,
            totalCount: count($tagDTOs)
        );
    }
}
