<?php

declare(strict_types=1);

namespace App\Service\Resource;

use App\DTO\Request\Resource\CreateUpdateResourceDTO;
use App\DTO\Response\Resource\CreateUpdateResourceResponseDTO;
use App\Entity\Resource;
use App\Entity\ResourceCategoryMapping;
use App\Entity\ResourceContentBlock;
use App\Entity\ResourceEmployeeRoleMapping;
use App\Entity\ResourceRelation;
use App\Entity\ResourceTagMapping;
use App\Entity\ResourceTradeMapping;
use App\Exception\ResourceCreateUpdateException;
use App\Repository\EmployeeRoleRepository;
use App\Repository\FileRepository;
use App\Repository\ResourceCategoryRepository;
use App\Repository\ResourceRepository;
use App\Repository\ResourceTagRepository;
use App\Repository\ResourceTypeRepository;
use App\Repository\TradeRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class CreateResourceService
{
    public function __construct(
        private ResourceRepository $resourceRepository,
        private ResourceTypeRepository $resourceTypeRepository,
        private ResourceTagRepository $resourceTagRepository,
        private ResourceCategoryRepository $resourceCategoryRepository,
        private EmployeeRoleRepository $employeeRoleRepository,
        private TradeRepository $tradeRepository,
        private FileRepository $fileRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function createResource(CreateUpdateResourceDTO $createResourceDTO): CreateUpdateResourceResponseDTO
    {
        $this->entityManager->beginTransaction();

        try {
            $this->validateSlug($createResourceDTO->slug);

            $resource = $this->createBaseResource($createResourceDTO);

            $this->attachThumbnailIfPresent($createResourceDTO, $resource);

            $this->attachTagMappings($createResourceDTO->tagIds, $resource);
            $this->attachCategoryMappings($createResourceDTO->categoryIds, $resource);
            $this->attachTradeMappings($createResourceDTO->tradeIds, $resource);
            $this->attachRoleMappings($createResourceDTO->roleIds, $resource);

            $this->attachContentBlocks($createResourceDTO->contentBlocks, $resource);

            $this->attachRelatedResources($createResourceDTO->relatedResourceIds, $resource);

            $this->resourceRepository->save($resource, true);
            $this->entityManager->commit();

            return new CreateUpdateResourceResponseDTO(
                id: $resource->getId(),
                uuid: $resource->getUuid(),
                title: $resource->getTitle(),
                contentUrl: $resource->getContentUrl(),
                thumbnailUrl: $resource->getThumbnailUrl(),
                isPublished: $resource->isPublished(),
            );
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        } finally {
            $this->entityManager->clear();
        }
    }

    private function validateSlug(string $slug): void
    {
        $existingWithSlug = $this->resourceRepository->findOneBy(['slug' => $slug]);
        if (null !== $existingWithSlug) {
            throw new ResourceCreateUpdateException(sprintf('A resource with slug "%s" already exists.', $slug));
        }
    }

    private function createBaseResource(CreateUpdateResourceDTO $dto): Resource
    {
        $resource = new Resource();
        $resource->setTitle($dto->title);
        $resource->setSlug($dto->slug);
        $resource->setTagline($dto->tagline);
        $resource->setDescription($dto->description);
        $resource->setIsPublished($dto->is_published);

        if (!empty($dto->content_url)) {
            $resource->setContentUrl($dto->content_url);
        }

        if (!empty($dto->publish_start_date)) {
            $publishStart = new \DateTime($dto->publish_start_date);
            $resource->setPublishStartDate($publishStart);
        }
        if (!empty($dto->publish_end_date)) {
            $publishEnd = new \DateTime($dto->publish_end_date);
            $resource->setPublishEndDate($publishEnd);
        }

        $typeEntity = $this->resourceTypeRepository->find($dto->type);
        if (null === $typeEntity) {
            throw new ResourceCreateUpdateException(sprintf('ResourceType with ID %d not found.', $dto->type));
        }
        $resource->setType($typeEntity);
        $resource->setLegacyUrl($dto->legacy_url);

        return $resource;
    }

    private function attachThumbnailIfPresent(CreateUpdateResourceDTO $dto, Resource $resource): void
    {
        if (null === $dto->thumbnailFileUuid) {
            return;
        }

        $fileEntity = $this->fileRepository->findOneByUuid($dto->thumbnailFileUuid);
        if (!$fileEntity) {
            throw new ResourceCreateUpdateException(sprintf('File with UUID %s not found (thumbnail).', $dto->thumbnailFileUuid));
        }

        $resource->setThumbnail($fileEntity);
    }

    private function attachTagMappings(array $tagIds, Resource $resource): void
    {
        foreach ($tagIds as $tagId) {
            $tagEntity = $this->resourceTagRepository->find($tagId);
            if ($tagEntity) {
                $tagMapping = new ResourceTagMapping();
                $tagMapping->setResource($resource);
                $tagMapping->setResourceTag($tagEntity);
                $resource->addResourceTagMapping($tagMapping);

                $this->entityManager->persist($tagMapping);
            }
        }
    }

    private function attachCategoryMappings(array $categoryIds, Resource $resource): void
    {
        foreach ($categoryIds as $catId) {
            $catEntity = $this->resourceCategoryRepository->find($catId);
            if ($catEntity) {
                $catMapping = new ResourceCategoryMapping();
                $catMapping->setResource($resource);
                $catMapping->setResourceCategory($catEntity);
                $resource->addResourceCategoryMapping($catMapping);

                $this->entityManager->persist($catMapping);
            }
        }
    }

    private function attachTradeMappings(array $tradeIds, Resource $resource): void
    {
        foreach ($tradeIds as $tradeId) {
            $tradeEntity = $this->tradeRepository->find($tradeId);
            if ($tradeEntity) {
                $tradeMapping = new ResourceTradeMapping();
                $tradeMapping->setResource($resource);
                $tradeMapping->setTrade($tradeEntity);
                $resource->addResourceTradeMapping($tradeMapping);

                $this->entityManager->persist($tradeMapping);
            }
        }
    }

    private function attachRoleMappings(array $roleIds, Resource $resource): void
    {
        foreach ($roleIds as $roleId) {
            $roleEntity = $this->employeeRoleRepository->find($roleId);
            if ($roleEntity) {
                $roleMapping = new ResourceEmployeeRoleMapping();
                $roleMapping->setResource($resource);
                $roleMapping->setEmployeeRole($roleEntity);
                $resource->addResourceEmployeeRoleMapping($roleMapping);

                $this->entityManager->persist($roleMapping);
            }
        }
    }

    private function attachContentBlocks(array $contentBlocks, Resource $resource): void
    {
        foreach ($contentBlocks as $blockDTO) {
            $contentBlock = new ResourceContentBlock();
            $contentBlock->setResource($resource);
            $contentBlock->setContent($blockDTO->content);
            $contentBlock->setType($blockDTO->type);
            $contentBlock->setSortOrder($blockDTO->order_number);
            $contentBlock->setTitle($blockDTO->title);
            $contentBlock->setShortDescription($blockDTO->short_description);

            if (!empty($blockDTO->fileUuid)) {
                $blockFileEntity = $this->fileRepository->findOneByUuid($blockDTO->fileUuid);
                if (!$blockFileEntity) {
                    throw new ResourceCreateUpdateException(sprintf('File with UUID %s not found (content block).', $blockDTO->fileUuid));
                }
                $contentBlock->setFile($blockFileEntity);
            }

            $this->entityManager->persist($contentBlock);
            $resource->addResourceContentBlock($contentBlock);
        }
    }

    private function attachRelatedResources(array $relatedResourceIds, Resource $resource): void
    {
        foreach ($relatedResourceIds as $relatedId) {
            $relatedResource = $this->resourceRepository->find($relatedId);
            if (!$relatedResource) {
                throw new ResourceCreateUpdateException(sprintf('Resource with ID %d not found (related).', $relatedId));
            }

            $relation = new ResourceRelation();
            $relation->setResource($resource);
            $relation->setRelatedResource($relatedResource);
            $this->entityManager->persist($relation);
        }
    }
}
