<?php

declare(strict_types=1);

namespace App\Service\Resource;

use App\DTO\Request\Resource\CreateResourceContentBlockDTO;
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
use App\Module\CraftMigration\DTO\Request\Resource\UpdateRelatedResourcesDTO;
use App\Repository\EmployeeRoleRepository;
use App\Repository\FileRepository;
use App\Repository\ResourceCategoryRepository;
use App\Repository\ResourceRepository;
use App\Repository\ResourceTagRepository;
use App\Repository\ResourceTypeRepository;
use App\Repository\TradeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

readonly class UpdateResourceService
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
        private LoggerInterface $logger,
    ) {
    }

    public function updateResource(
        Resource $resource,
        CreateUpdateResourceDTO|UpdateRelatedResourcesDTO $updateResourceDTO,
    ): CreateUpdateResourceResponseDTO {
        $this->entityManager->beginTransaction();

        try {
            $this->validateSlug($resource, $updateResourceDTO->slug);

            if ($updateResourceDTO instanceof CreateUpdateResourceDTO) {
                $this->updateBaseResourceFields($resource, $updateResourceDTO);

                $this->updateResourceType($resource, $updateResourceDTO->type);

                $this->updateThumbnail($resource, $updateResourceDTO);

                $this->updateTagMappings($resource, $updateResourceDTO->tagIds);

                $this->updateCategoryMappings($resource, $updateResourceDTO->categoryIds);

                $this->updateTradeMappings($resource, $updateResourceDTO->tradeIds);

                $this->updateRoleMappings($resource, $updateResourceDTO->roleIds);

                $this->updateContentBlocks($resource, $updateResourceDTO->contentBlocks);
            }

            $this->updateRelatedResources($resource, $updateResourceDTO->relatedResourceIds);

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

    private function validateSlug(Resource $resource, string $newSlug): void
    {
        if ($resource->getSlug() === $newSlug) {
            return;
        }

        $existingWithSlug = $this->resourceRepository->findOneBy(['slug' => $newSlug]);
        if (null !== $existingWithSlug) {
            throw new ResourceCreateUpdateException(sprintf('A resource with slug "%s" already exists.', $newSlug));
        }
    }

    private function updateBaseResourceFields(Resource $resource, CreateUpdateResourceDTO $dto): void
    {
        $resource->setTitle($dto->title);
        $resource->setSlug($dto->slug);
        $resource->setDescription($dto->description);
        $resource->setIsPublished($dto->is_published);
        $resource->setTagline($dto->tagline);
        $resource->setContentUrl($dto->content_url);
        $resource->setLegacyUrl($dto->legacy_url);

        $publishStartDate = $dto->publish_start_date
            ? new \DateTime($dto->publish_start_date)
            : null;
        $resource->setPublishStartDate($publishStartDate);

        $publishEndDate = $dto->publish_end_date
            ? new \DateTime($dto->publish_end_date)
            : null;
        $resource->setPublishEndDate($publishEndDate);
    }

    private function updateResourceType(Resource $resource, ?int $typeId): void
    {
        $typeEntity = $this->resourceTypeRepository->find($typeId);
        if (!$typeEntity) {
            throw new ResourceCreateUpdateException(sprintf('ResourceType with ID %d not found.', $typeId));
        }
        $resource->setType($typeEntity);
    }

    private function updateThumbnail(Resource $resource, CreateUpdateResourceDTO $dto): void
    {
        // If UUID is null, clear the thumbnail
        if (null === $dto->thumbnailFileUuid) {
            $resource->setThumbnail(null);
            return;
        }

        $fileEntity = $this->fileRepository->findOneByUuid($dto->thumbnailFileUuid);
        if (!$fileEntity) {
            throw new ResourceCreateUpdateException(sprintf('File with UUID %s not found (thumbnail).', $dto->thumbnailFileUuid));
        }

        $resource->setThumbnail($fileEntity);
    }

    private function updateTagMappings(Resource $resource, array $tagIds): void
    {
        foreach ($resource->getResourceTagMappings() as $oldTagMapping) {
            $this->entityManager->remove($oldTagMapping);
        }
        $resource->getResourceTagMappings()->clear();

        foreach ($tagIds as $tagId) {
            $tagEntity = $this->resourceTagRepository->find($tagId);
            if ($tagEntity) {
                $tagMapping = new ResourceTagMapping();
                $tagMapping->setResource($resource);
                $tagMapping->setResourceTag($tagEntity);
                $this->entityManager->persist($tagMapping);
                $resource->addResourceTagMapping($tagMapping);
            }
        }
    }

    private function updateCategoryMappings(Resource $resource, array $categoryIds): void
    {
        foreach ($resource->getResourceCategoryMappings() as $oldCatMapping) {
            $this->entityManager->remove($oldCatMapping);
        }
        $resource->getResourceCategoryMappings()->clear();

        foreach ($categoryIds as $catId) {
            $catEntity = $this->resourceCategoryRepository->find($catId);
            if ($catEntity) {
                $catMapping = new ResourceCategoryMapping();
                $catMapping->setResource($resource);
                $catMapping->setResourceCategory($catEntity);
                $this->entityManager->persist($catMapping);
                $resource->addResourceCategoryMapping($catMapping);
            }
        }
    }

    private function updateTradeMappings(Resource $resource, array $tradeIds): void
    {
        // Remove old
        foreach ($resource->getResourceTradeMappings() as $oldTradeMapping) {
            $this->entityManager->remove($oldTradeMapping);
        }
        $resource->getResourceTradeMappings()->clear();

        // Add new
        foreach ($tradeIds as $tradeId) {
            $tradeEntity = $this->tradeRepository->find($tradeId);
            if ($tradeEntity) {
                $tradeMapping = new ResourceTradeMapping();
                $tradeMapping->setResource($resource);
                $tradeMapping->setTrade($tradeEntity);
                $this->entityManager->persist($tradeMapping);
                $resource->addResourceTradeMapping($tradeMapping);
            }
        }
    }

    private function updateRoleMappings(Resource $resource, array $roleIds): void
    {
        // Remove old
        foreach ($resource->getResourceEmployeeRoleMappings() as $oldRoleMapping) {
            $this->entityManager->remove($oldRoleMapping);
        }
        $resource->getResourceEmployeeRoleMappings()->clear();

        // Add new
        foreach ($roleIds as $roleId) {
            $roleEntity = $this->employeeRoleRepository->find($roleId);
            if ($roleEntity) {
                $roleMapping = new ResourceEmployeeRoleMapping();
                $roleMapping->setResource($resource);
                $roleMapping->setEmployeeRole($roleEntity);
                $this->entityManager->persist($roleMapping);
                $resource->addResourceEmployeeRoleMapping($roleMapping);
            }
        }
    }

    /**
     * @param Resource $resource
     * @param CreateResourceContentBlockDTO[] $contentBlocks
     * @return void
     */
    private function updateContentBlocks(Resource $resource, array $contentBlocks): void
    {
        $oldBlocks = $resource->getResourceContentBlocks()->toArray();
        foreach ($oldBlocks as $oldBlock) {
            $oldBlock->setFile(null);
            $resource->removeResourceContentBlock($oldBlock);
            $this->entityManager->remove($oldBlock);
        }
        $this->entityManager->flush();

        foreach ($contentBlocks as $blockDTO) {
            $contentBlock = new ResourceContentBlock();
            $contentBlock->setResource($resource);
            $contentBlock->setContent($blockDTO->content);
            $contentBlock->setType($blockDTO->type);
            $contentBlock->setSortOrder($blockDTO->order_number);
            $contentBlock->setTitle($blockDTO->title);
            $contentBlock->setShortDescription($blockDTO->short_description);

            if (!empty($blockDTO->fileUuid)) {
                $this->logger->debug(message: 'File UUID is: '.$blockDTO->fileUuid);
                $fileEntity = $this->fileRepository->findOneByUuid($blockDTO->fileUuid);
                if (!$fileEntity) {
                    throw new ResourceCreateUpdateException(sprintf('File with UUID %s not found (content block).', $blockDTO->fileUuid));
                }
                $contentBlock->setFile($fileEntity);
            }

            $this->entityManager->persist($contentBlock);
            $resource->addResourceContentBlock($contentBlock);
        }
    }

    private function updateRelatedResources(Resource $resource, array $relatedResourceIds): void
    {
        $oldRelations = $resource->getResourceRelations()->toArray();
        foreach ($oldRelations as $oldRelation) {
            $this->entityManager->remove($oldRelation);
        }
        $resource->getResourceRelations()->clear();

        foreach ($relatedResourceIds as $relatedId) {
            $relatedResource = $this->resourceRepository->find($relatedId);
            if (!$relatedResource) {
                throw new ResourceCreateUpdateException(sprintf('Resource with ID %d not found (related).', $relatedId));
            }

            $relation = new ResourceRelation();
            $relation->setResource($resource);
            $relation->setRelatedResource($relatedResource);
            $this->entityManager->persist($relation);

            $resource->addResourceRelation($relation);
        }
    }
}
