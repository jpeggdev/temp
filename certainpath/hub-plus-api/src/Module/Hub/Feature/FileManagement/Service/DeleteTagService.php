<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Service;

use App\Module\Hub\Feature\FileManagement\DTO\Response\DeleteTagResponseDTO;
use App\Module\Hub\Feature\FileManagement\Exception\TagNotFoundException;
use App\Repository\FileSystemNodeTagRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class DeleteTagService
{
    public function __construct(
        private EntityManagerInterface $em,
        private FileSystemNodeTagRepository $tagRepository,
    ) {
    }

    public function deleteTag(int $id): DeleteTagResponseDTO
    {
        $tag = $this->tagRepository->find($id);
        if (!$tag) {
            throw new TagNotFoundException();
        }

        $tagName = $tag->getName();

        $this->em->remove($tag);
        $this->em->flush();

        return new DeleteTagResponseDTO(
            message: "Tag '$tagName' deleted successfully",
            deletedTagId: $id
        );
    }
}
