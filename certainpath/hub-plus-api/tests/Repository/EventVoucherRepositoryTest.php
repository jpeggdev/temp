<?php

namespace App\Tests\Repository;

use App\Entity\Company;
use App\Entity\EventVoucher;
use App\Module\EventRegistration\Feature\EventVoucherManagement\DTO\Query\GetEventVouchersDTO;
use App\Module\EventRegistration\Feature\EventVoucherManagement\Exception\EventVoucherNotFoundException;
use App\Repository\EventVoucherRepository;
use App\Tests\AbstractKernelTestCase;

class EventVoucherRepositoryTest extends AbstractKernelTestCase
{
    private EventVoucherRepository $repository;

    public function setUp(): void
    {
        parent::setUp();
        /** @var EventVoucherRepository $voucherRepository */
        $voucherRepository = $this->entityManager->getRepository(EventVoucher::class);
        $this->repository = $voucherRepository;
    }

    private function createCompany(): Company
    {
        $company = new Company();
        $company->setCompanyName($this->faker->company());
        $company->setUuid($this->faker->uuid());
        $this->entityManager->persist($company);
        $this->entityManager->flush();

        return $company;
    }

    private function createEventVoucher(?string $name = null, ?Company $company = null): EventVoucher
    {
        if (null === $company) {
            $company = $this->createCompany();
        }

        $eventVoucher = new EventVoucher();
        $eventVoucher->setName($name ?? $this->faker->bothify('VOUCHER-####'));
        $eventVoucher->setDescription($this->faker->sentence());
        $eventVoucher->setCompany($company);
        $eventVoucher->setTotalSeats($this->faker->numberBetween(1, 100));
        $eventVoucher->setIsActive(true);
        $eventVoucher->setStartDate(new \DateTimeImmutable('+1 day'));
        $eventVoucher->setEndDate(new \DateTimeImmutable('+30 days'));

        $this->repository->save($eventVoucher, true);

        return $eventVoucher;
    }

    public function testSave(): void
    {
        $company = $this->createCompany();

        $eventVoucher = new EventVoucher();
        $eventVoucher->setName($this->faker->bothify('SAVE-####'));
        $eventVoucher->setDescription($this->faker->sentence());
        $eventVoucher->setCompany($company);
        $eventVoucher->setTotalSeats(10);
        $eventVoucher->setIsActive(true);

        self::assertNull($eventVoucher->getId());
        $this->repository->save($eventVoucher, true);
        self::assertNotNull($eventVoucher->getId());

        $found = $this->repository->findOneById($eventVoucher->getId());
        self::assertNotNull($found);
        self::assertSame($eventVoucher->getName(), $found->getName());
        self::assertSame($eventVoucher->getTotalSeats(), $found->getTotalSeats());
    }

    public function testSoftDelete(): void
    {
        $eventVoucher = $this->createEventVoucher();
        self::assertTrue($eventVoucher->isActive());
        self::assertNull($eventVoucher->getDeletedAt());

        $this->repository->softDelete($eventVoucher, true);

        self::assertFalse($eventVoucher->isActive());
        self::assertNotNull($eventVoucher->getDeletedAt());

        // The entity should still exist in the database
        $found = $this->repository->findOneById($eventVoucher->getId());
        self::assertNotNull($found);
        self::assertFalse($found->isActive());
    }

    public function testFindOneById(): void
    {
        $eventVoucher = $this->createEventVoucher();

        $found = $this->repository->findOneById($eventVoucher->getId());
        self::assertNotNull($found);
        self::assertSame($eventVoucher->getId(), $found->getId());

        $notFound = $this->repository->findOneById(99999);
        self::assertNull($notFound);
    }

    public function testFindOneByIdOrFail(): void
    {
        $eventVoucher = $this->createEventVoucher();

        $found = $this->repository->findOneByIdOrFail($eventVoucher->getId());
        self::assertSame($eventVoucher->getId(), $found->getId());

        $this->expectException(EventVoucherNotFoundException::class);
        $this->repository->findOneByIdOrFail(99999);
    }

    public function testFindOneByCode(): void
    {
        $specificCode = 'SUMMER2025';
        $eventVoucher = $this->createEventVoucher($specificCode);

        $this->createEventVoucher('WINTER2025');
        $this->createEventVoucher('SPRING2025');

        $found = $this->repository->findOneByCode($specificCode);
        self::assertNotNull($found);
        self::assertSame($eventVoucher->getId(), $found->getId());
        self::assertSame($specificCode, $found->getName());

        $notFound = $this->repository->findOneByCode('NONEXISTENT');
        self::assertNull($notFound);
    }

    public function testFindAllByDTO(): void
    {
        $voucher1 = $this->createEventVoucher('VOUCHER1');
        $voucher2 = $this->createEventVoucher('VOUCHER2');
        $voucher3 = $this->createEventVoucher('VOUCHER3');

        $deletedVoucher = $this->createEventVoucher('DELETED');
        $this->repository->softDelete($deletedVoucher, true);

        $dto = new GetEventVouchersDTO();
        $dto->page = 1;
        $dto->pageSize = 10;
        $dto->sortBy = 'id';
        $dto->sortOrder = 'ASC';
        $dto->isActive = true;

        $result = $this->repository->findAllByDTO($dto);

        self::assertCount(3, $result);
        self::assertTrue($result->contains($voucher1));
        self::assertTrue($result->contains($voucher2));
        self::assertTrue($result->contains($voucher3));
        self::assertFalse($result->contains($deletedVoucher));

        $dto->pageSize = 2;
        $paginatedResult = $this->repository->findAllByDTO($dto);
        self::assertCount(2, $paginatedResult);

        $searchDto = new GetEventVouchersDTO();
        $searchDto->searchTerm = 'VOUCHER1';
        $searchResult = $this->repository->findAllByDTO($searchDto);
        self::assertCount(1, $searchResult);
        self::assertTrue($searchResult->contains($voucher1));
    }

    public function testGetCountByDTO(): void
    {
        $this->createEventVoucher('COUNT1');
        $this->createEventVoucher('COUNT2');
        $this->createEventVoucher('COUNT3');

        $deletedVoucher = $this->createEventVoucher('COUNT-DELETED');
        $this->repository->softDelete($deletedVoucher, true);

        $dto = new GetEventVouchersDTO();
        $dto->isActive = true;

        $count = $this->repository->getCountByDTO($dto);
        self::assertSame(3, $count);

        $inactiveDto = new GetEventVouchersDTO();
        $inactiveDto->isActive = false;

        $inactiveCount = $this->repository->getCountByDTO($inactiveDto);
        self::assertSame(1, $inactiveCount);
    }

    public function testFindAllByCompany(): void
    {
        $company1 = $this->createCompany();
        $company2 = $this->createCompany();

        $voucher1 = $this->createEventVoucher('COM1-V1', $company1);
        $voucher2 = $this->createEventVoucher('COM1-V2', $company1);

        $voucher3 = $this->createEventVoucher('COM2-V1', $company2);

        $company1Vouchers = $this->repository->findAllByCompany($company1);
        self::assertCount(2, $company1Vouchers);
        self::assertContains($voucher1, $company1Vouchers);
        self::assertContains($voucher2, $company1Vouchers);
        self::assertNotContains($voucher3, $company1Vouchers);

        $company2Vouchers = $this->repository->findAllByCompany($company2);
        self::assertCount(1, $company2Vouchers);
        self::assertContains($voucher3, $company2Vouchers);
        self::assertNotContains($voucher1, $company2Vouchers);
        self::assertNotContains($voucher2, $company2Vouchers);
    }
}
