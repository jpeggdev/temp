<?php

namespace App\Tests\Services;

use App\Entity\Address;
use App\Exceptions\Smarty\AddressVerificationFailedException;
use App\Exceptions\Smarty\NoAddressCandidateFoundException;
use App\Exceptions\Smarty\RequestAddressCandidateFailedException;
use App\Services\AddressVerification\SmartyAddressVerificationService;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Psr\Log\LoggerInterface;
use SmartyStreets\PhpSdk\US_Street\Analysis;
use SmartyStreets\PhpSdk\US_Street\Candidate;
use SmartyStreets\PhpSdk\US_Street\Client;
use SmartyStreets\PhpSdk\US_Street\Components;
use SmartyStreets\PhpSdk\US_Street\Metadata;
use Symfony\Component\Serializer\SerializerInterface;

class SmartyAddressVerificationServiceTest extends MockeryTestCase
{
    private MockInterface $client;

    private MockInterface $entityManager;

    private MockInterface $serializer;

    private SmartyAddressVerificationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Mockery::mock(Client::class);
        $logger = Mockery::mock(LoggerInterface::class);
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);
        $this->serializer = Mockery::mock(SerializerInterface::class);

        $this->service = new SmartyAddressVerificationService(
            $this->client,
            $logger,
            $this->entityManager,
            $this->serializer
        );
    }

    /**
     * @throws NoAddressCandidateFoundException
     * @throws AddressVerificationFailedException
     * @throws RequestAddressCandidateFailedException
     */
    public function testVerifyAndNormalizeSuccess(): void
    {
        $address = $this->prepareAddressEntity();

        /** @var Candidate $candidate */
        $candidate = $this->initCandidateMock();
        $this->initClientMockWithNonEmptyLookupResult($candidate);
        $this->initSerializerMock();

        $this->assertEquals(0, $address->getVerificationAttempts());
        $this->assertNotEquals($candidate->getDeliveryLine1(), $address->getAddress1());
        $this->assertNotEquals($candidate->getDeliveryLine2(), $address->getAddress1());
        $this->assertNotEquals($candidate->getComponents()->getCityName(), $address->getCity());
        $this->assertNotEquals($candidate->getComponents()->getStateAbbreviation(), $address->getStateCode());

        $normalizedAddress = $this->service->verifyAndNormalize($address);

        $this->assertEquals(1, $normalizedAddress->getVerificationAttempts());
        $this->assertEquals($candidate->getDeliveryLine1(), $normalizedAddress->getAddress1());
        $this->assertEquals($candidate->getDeliveryLine2(), $normalizedAddress->getAddress2());
        $this->assertEquals($candidate->getComponents()->getCityName(), $normalizedAddress->getCity());
        $this->assertEquals($candidate->getComponents()->getStateAbbreviation(), $normalizedAddress->getStateCode());
    }

    /**
     * @throws RequestAddressCandidateFailedException
     * @throws AddressVerificationFailedException
     */
    public function testVerifyAndNormalizeNoAddressCandidateFoundException(): void
    {
        $address = $this->prepareAddressEntity();

        $this->initEntityManagerMock($address);
        $this->initClientMockWithEmptyLookupResult();

        $this->expectException(NoAddressCandidateFoundException::class);

        $this->service->verifyAndNormalize($address);
    }

    /**
     * @throws NoAddressCandidateFoundException
     * @throws RequestAddressCandidateFailedException
     */
    public function testVerifyAndNormalizeAddressVerificationFailedException(): void
    {
        $address = $this->prepareAddressEntity();

        $candidate = $this->initCandidateMock(false);
        $this->initClientMockWithNonEmptyLookupResult($candidate);
        $this->initEntityManagerMock($address);

        $this->expectException(AddressVerificationFailedException::class);

        $this->service->verifyAndNormalize($address);
    }

    private function prepareAddressEntity(): Address
    {
        return (new Address())
            ->setAddress1('123 main st')
            ->setAddress2('4B')
            ->setCity('anytown')
            ->setStateCode('ny')
            ->setPostalCode('12345');
    }

    public function initClientMockWithNonEmptyLookupResult($candidate): void
    {
        $this->client->shouldReceive('sendLookup')
            ->once()
            ->andReturnUsing(function ($lookup) use ($candidate) {
                $lookup->setResult($candidate);
            });
    }

    private function initClientMockWithEmptyLookupResult(): void
    {
        $this->client->shouldReceive('sendLookup')
            ->once()
            ->andReturn([]);
    }

    private function initEntityManagerMock(Address $address): void
    {
        $this->entityManager
            ->shouldReceive('persist')
            ->once()
            ->with($address);

        $this->entityManager
            ->shouldReceive('flush')
            ->once();
    }

    private function initSerializerMock(): void
    {
        $this->serializer
            ->shouldReceive('serialize')
            ->andReturn('{"mocked":"data"}');
    }

    private function initCandidateMock($isDeliverable = true): MockInterface
    {
        $componentsData = [
            'zipcode' => '12345',
            'city_name' => 'Anytown',
            'state_abbreviation' => 'NY'
        ];

        $analysisData = [
            'dpv_footnotes' => $isDeliverable ? 'AABB' : 'AAN1',
            'dpv_match_code' => $isDeliverable ? 'Y': 'N',
            'vacant' => 'N',
            'active' => 'N'
        ];

        $metadataData = [
            'rdi' => 'Residential'
        ];

        $components = new Components($componentsData);
        $analysis = new Analysis($analysisData);
        $metadata = new Metadata($metadataData);

        $candidate = Mockery::mock(Candidate::class)->makePartial();
        $candidate->shouldReceive('getDeliveryLine1')->andReturn('123 Main St Apt 4B');
        $candidate->shouldReceive('getComponents')->andReturn($components);
        $candidate->shouldReceive('getAnalysis')->andReturn($analysis);
        $candidate->shouldReceive('getMetadata')->andReturn($metadata);

        return $candidate;
    }
}
