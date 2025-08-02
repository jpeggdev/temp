<?php

namespace App\Tests\Repository;

use App\Entity\DiscountType;
use App\Entity\EventDiscount;
use App\Module\EventRegistration\Feature\EventDiscountManagement\DTO\Query\GetEventDiscountsDTO;
use App\Module\EventRegistration\Feature\EventDiscountManagement\Exception\EventDiscountNotFoundException;
use App\Repository\EventDiscountRepository;
use App\Tests\AbstractKernelTestCase;

class EventDiscountRepositoryTest extends AbstractKernelTestCase
{
    private EventDiscountRepository $repository;
    private DiscountType $percentageDiscountType;
    private DiscountType $fixedAmountDiscountType;

    public function setUp(): void
    {
        parent::setUp();
        /** @var EventDiscountRepository $discountRepository */
        $discountRepository = $this->entityManager->getRepository(EventDiscount::class);
        $this->repository = $discountRepository;
        $this->percentageDiscountType = new DiscountType();
        $this->percentageDiscountType->setName(DiscountType::EVENT_TYPE_PERCENTAGE);
        $this->percentageDiscountType->setDisplayName('Percentage');
        $this->entityManager->persist($this->percentageDiscountType);

        $this->fixedAmountDiscountType = new DiscountType();
        $this->fixedAmountDiscountType->setName(DiscountType::EVENT_TYPE_FIXED_AMOUNT);
        $this->fixedAmountDiscountType->setDisplayName('Fixed Amount');
        $this->entityManager->persist($this->fixedAmountDiscountType);

        $this->entityManager->flush();
    }

    private function createTestEventDiscount(?string $code = null): EventDiscount
    {
        $eventDiscount = new EventDiscount();
        $eventDiscount->setCode($code ?? $this->faker->bothify('DISC-####'));
        $eventDiscount->setDescription($this->faker->sentence());
        $eventDiscount->setDiscountValue('10.00');
        $eventDiscount->setDiscountType($this->percentageDiscountType);
        $eventDiscount->setIsActive(true);

        $this->repository->save($eventDiscount, true);

        return $eventDiscount;
    }

    public function testFindOneByCode(): void
    {
        $specificCode = 'SUMMER2025';
        $eventDiscount = $this->createTestEventDiscount($specificCode);

        $this->createTestEventDiscount('WINTER2025');
        $this->createTestEventDiscount('SPRING2025');

        $found = $this->repository->findOneByCode($specificCode);
        self::assertNotNull($found);
        self::assertSame($eventDiscount->getId(), $found->getId());
        self::assertSame($specificCode, $found->getCode());

        $notFound = $this->repository->findOneByCode('NONEXISTENT');
        self::assertNull($notFound);

        $caseVariation = $this->repository->findOneByCode(strtolower($specificCode));
        if (strtolower($specificCode) !== $specificCode) {
            self::assertNull($caseVariation, 'Code search should be case-sensitive');
        }
    }

    public function testSave(): void
    {
        $eventDiscount = new EventDiscount();
        $eventDiscount->setCode($this->faker->bothify('SAVE-####'));
        $eventDiscount->setDescription($this->faker->sentence());
        $eventDiscount->setDiscountValue('15.00');
        $eventDiscount->setDiscountType($this->fixedAmountDiscountType);
        $eventDiscount->setIsActive(true);

        self::assertNull($eventDiscount->getId());
        $this->repository->save($eventDiscount, true);
        self::assertNotNull($eventDiscount->getId());

        $found = $this->repository->findOneById($eventDiscount->getId());
        self::assertNotNull($found);
        self::assertSame($eventDiscount->getCode(), $found->getCode());
    }

    public function testSoftDelete(): void
    {
        $eventDiscount = $this->createTestEventDiscount();
        self::assertTrue($eventDiscount->isActive());
        self::assertNull($eventDiscount->getDeletedAt());

        $this->repository->softDelete($eventDiscount, true);

        self::assertFalse($eventDiscount->isActive());
        self::assertNotNull($eventDiscount->getDeletedAt());

        $found = $this->repository->findOneById($eventDiscount->getId());
        self::assertNotNull($found);
        self::assertFalse($found->isActive());
    }

    public function testFindOneById(): void
    {
        $eventDiscount = $this->createTestEventDiscount();

        $found = $this->repository->findOneById($eventDiscount->getId());
        self::assertNotNull($found);
        self::assertSame($eventDiscount->getId(), $found->getId());

        $notFound = $this->repository->findOneById(99999);
        self::assertNull($notFound);
    }

    public function testFindOneByIdOrFail(): void
    {
        $eventDiscount = $this->createTestEventDiscount();

        $found = $this->repository->findOneByIdOrFail($eventDiscount->getId());
        self::assertSame($eventDiscount->getId(), $found->getId());

        $this->expectException(EventDiscountNotFoundException::class);
        $this->repository->findOneByIdOrFail(99999);
    }

    public function testFindAllByDTO(): void
    {
        $discount1 = $this->createTestEventDiscount('CODE1');
        $discount2 = $this->createTestEventDiscount('CODE2');
        $discount3 = $this->createTestEventDiscount('CODE3');

        $deletedDiscount = $this->createTestEventDiscount('DELETED');
        $this->repository->softDelete($deletedDiscount, true);

        $dto = new GetEventDiscountsDTO();
        $dto->page = 1;
        $dto->pageSize = 10;
        $dto->sortBy = 'id';
        $dto->sortOrder = 'ASC';
        $dto->isActive = true;

        $result = $this->repository->findAllByDTO($dto);

        self::assertCount(3, $result);
        self::assertTrue($result->contains($discount1));
        self::assertTrue($result->contains($discount2));
        self::assertTrue($result->contains($discount3));
        self::assertFalse($result->contains($deletedDiscount));

        $dto->pageSize = 2;
        $paginatedResult = $this->repository->findAllByDTO($dto);
        self::assertCount(2, $paginatedResult);
    }

    public function testGetCountByDTO(): void
    {
        $this->createTestEventDiscount('COUNT1');
        $this->createTestEventDiscount('COUNT2');
        $this->createTestEventDiscount('COUNT3');

        $deletedDiscount = $this->createTestEventDiscount('COUNT-DELETED');
        $this->repository->softDelete($deletedDiscount, true);

        $dto = new GetEventDiscountsDTO();
        $dto->isActive = true;

        $count = $this->repository->getCountByDTO($dto);
        self::assertSame(3, $count);

        $inactiveDto = new GetEventDiscountsDTO();
        $inactiveDto->isActive = false;

        $inactiveCount = $this->repository->getCountByDTO($inactiveDto);
        self::assertSame(1, $inactiveCount);
    }
}
