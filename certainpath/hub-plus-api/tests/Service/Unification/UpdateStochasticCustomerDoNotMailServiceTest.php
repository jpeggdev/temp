<?php

declare(strict_types=1);

namespace App\Tests\Service\Unification;

use App\Client\UnificationClient;
use App\DTO\Request\Customer\UpdateStochasticCustomerDoNotMailRequestDTO;
use App\DTO\Request\Prospect\UpdateStochasticProspectDoNotMailRequestDTO;
use App\Exception\APICommunicationException;
use App\Service\Unification\UpdateStochasticCustomerDoNotMailService;
use App\Service\Unification\UpdateStochasticProspectDoNotMailServiceInterface;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class UpdateStochasticCustomerDoNotMailServiceTest extends TestCase
{
    private MockObject&UnificationClient $unificationClientMock;
    private UpdateStochasticCustomerDoNotMailService $service;
    private MockObject&UpdateStochasticProspectDoNotMailServiceInterface $updateProspectServiceMock;

    protected function setUp(): void
    {
        $this->unificationClientMock = $this->createMock(UnificationClient::class);
        $this->updateProspectServiceMock = $this->createMock(UpdateStochasticProspectDoNotMailServiceInterface::class);
        $this->service = new UpdateStochasticCustomerDoNotMailService(
            $this->unificationClientMock,
            $this->updateProspectServiceMock
        );
    }

    public function testUpdateCustomerDoNotMailSuccess(): void
    {
        $customerId = 123;
        $intacctId = 'test-intacct-id';
        $dto = new UpdateStochasticCustomerDoNotMailRequestDTO(true);

        // Mock prospect lookup response
        $prospectLookupResponse = $this->createMock(ResponseInterface::class);
        $prospectLookupResponse->method('getStatusCode')->willReturn(Response::HTTP_OK);
        $prospectLookupResponse->method('toArray')->willReturn([
            'data' => [
                'id' => 456,
                'customerId' => $customerId,
                'doNotMail' => false,
            ],
        ]);

        // Mock prospect update responses
        $prospectUpdateResponse = $this->createMock(ResponseInterface::class);
        $prospectUpdateResponse->method('getStatusCode')->willReturn(Response::HTTP_OK);
        $prospectUpdateResponse->method('toArray')->willReturn([
            'id' => 456,
            'doNotMail' => true,
        ]);

        $preferredAddressUpdateResponse = $this->createMock(ResponseInterface::class);
        $preferredAddressUpdateResponse->method('getStatusCode')->willReturn(Response::HTTP_OK);

        $this->unificationClientMock->method('getBaseUri')->willReturn('https://api.example.com');

        $this->unificationClientMock
            ->expects($this->once())
            ->method('sendPatchRequest')
            ->with(
                'https://api.example.com/api/customers/123/prospect',
                ['intacctId' => $intacctId]
            )
            ->willReturn($prospectLookupResponse);

        // Mock the prospect service response
        $expectedResult = ['id' => 456, 'doNotMail' => true];
        $this->updateProspectServiceMock
            ->expects($this->once())
            ->method('updateProspectDoNotMail')
            ->with(456, $this->callback(function (UpdateStochasticProspectDoNotMailRequestDTO $prospectDTO) {
                return true === $prospectDTO->doNotMail;
            }))
            ->willReturn($expectedResult);

        $result = $this->service->updateCustomerDoNotMail($customerId, $dto, $intacctId);

        self::assertSame($expectedResult, $result);
    }

    public function testUpdateCustomerDoNotMailSetToFalse(): void
    {
        $customerId = 123;
        $intacctId = 'test-intacct-id';
        $dto = new UpdateStochasticCustomerDoNotMailRequestDTO(false);

        // Mock prospect lookup response
        $prospectLookupResponse = $this->createMock(ResponseInterface::class);
        $prospectLookupResponse->method('getStatusCode')->willReturn(Response::HTTP_OK);
        $prospectLookupResponse->method('toArray')->willReturn([
            'data' => [
                'id' => 456,
                'customerId' => $customerId,
                'doNotMail' => true,
            ],
        ]);

        $this->unificationClientMock->method('getBaseUri')->willReturn('https://api.example.com');

        $this->unificationClientMock
            ->expects($this->once())
            ->method('sendPatchRequest')
            ->with(
                'https://api.example.com/api/customers/123/prospect',
                ['intacctId' => $intacctId]
            )
            ->willReturn($prospectLookupResponse);

        // Mock the prospect service response
        $expectedResult = ['doNotMail' => false];
        $this->updateProspectServiceMock
            ->expects($this->once())
            ->method('updateProspectDoNotMail')
            ->with(456, $this->callback(function (UpdateStochasticProspectDoNotMailRequestDTO $prospectDTO) {
                return false === $prospectDTO->doNotMail;
            }))
            ->willReturn($expectedResult);

        $result = $this->service->updateCustomerDoNotMail($customerId, $dto, $intacctId);

        self::assertSame($expectedResult, $result);
    }

    public function testUpdateCustomerDoNotMailProspectNotFound(): void
    {
        $customerId = 123;
        $intacctId = 'test-intacct-id';
        $dto = new UpdateStochasticCustomerDoNotMailRequestDTO(true);

        // Mock prospect lookup response - prospect not found
        $prospectLookupResponse = $this->createMock(ResponseInterface::class);
        $prospectLookupResponse->method('getStatusCode')->willReturn(Response::HTTP_NOT_FOUND);

        $this->unificationClientMock->method('getBaseUri')->willReturn('https://api.example.com');

        $this->unificationClientMock
            ->expects($this->once())
            ->method('sendPatchRequest')
            ->with(
                'https://api.example.com/api/customers/123/prospect',
                ['intacctId' => $intacctId]
            )
            ->willReturn($prospectLookupResponse);

        $this->updateProspectServiceMock
            ->expects($this->never())
            ->method('updateProspectDoNotMail');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No prospect found for customer 123');

        $this->service->updateCustomerDoNotMail($customerId, $dto, $intacctId);
    }

    public function testUpdateCustomerDoNotMailProspectLookupFails(): void
    {
        $customerId = 123;
        $intacctId = 'test-intacct-id';
        $dto = new UpdateStochasticCustomerDoNotMailRequestDTO(true);

        // Mock prospect lookup response - server error
        $prospectLookupResponse = $this->createMock(ResponseInterface::class);
        $prospectLookupResponse->method('getStatusCode')->willReturn(Response::HTTP_INTERNAL_SERVER_ERROR);

        $this->unificationClientMock->method('getBaseUri')->willReturn('https://api.example.com');

        $this->unificationClientMock
            ->expects($this->once())
            ->method('sendPatchRequest')
            ->with(
                'https://api.example.com/api/customers/123/prospect',
                ['intacctId' => $intacctId]
            )
            ->willReturn($prospectLookupResponse);

        $this->updateProspectServiceMock
            ->expects($this->never())
            ->method('updateProspectDoNotMail');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to update customer do-not-mail status');

        $this->service->updateCustomerDoNotMail($customerId, $dto, $intacctId);
    }

    public function testUpdateCustomerDoNotMailProspectUpdateFails(): void
    {
        $customerId = 123;
        $intacctId = 'test-intacct-id';
        $dto = new UpdateStochasticCustomerDoNotMailRequestDTO(true);

        // Mock prospect lookup response
        $prospectLookupResponse = $this->createMock(ResponseInterface::class);
        $prospectLookupResponse->method('getStatusCode')->willReturn(Response::HTTP_OK);
        $prospectLookupResponse->method('toArray')->willReturn([
            'data' => [
                'id' => 456,
                'customerId' => $customerId,
                'doNotMail' => false,
            ],
        ]);

        $this->unificationClientMock->method('getBaseUri')->willReturn('https://api.example.com');

        $this->unificationClientMock
            ->expects($this->once())
            ->method('sendPatchRequest')
            ->with(
                'https://api.example.com/api/customers/123/prospect',
                ['intacctId' => $intacctId]
            )
            ->willReturn($prospectLookupResponse);

        // Mock prospect service to throw exception
        $this->updateProspectServiceMock
            ->expects($this->once())
            ->method('updateProspectDoNotMail')
            ->with(456, $this->callback(function (UpdateStochasticProspectDoNotMailRequestDTO $prospectDTO) {
                return true === $prospectDTO->doNotMail;
            }))
            ->willThrowException(new \RuntimeException('Prospect update failed'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Prospect update failed');

        $this->service->updateCustomerDoNotMail($customerId, $dto, $intacctId);
    }

    public function testUpdateCustomerDoNotMailTransportException(): void
    {
        $customerId = 123;
        $intacctId = 'test-intacct-id';
        $dto = new UpdateStochasticCustomerDoNotMailRequestDTO(true);

        $transportException = new class () extends \Exception implements TransportExceptionInterface {
            public function __construct()
            {
                parent::__construct('Network error');
            }
        };

        $this->unificationClientMock->method('getBaseUri')->willReturn('https://api.example.com');

        $this->unificationClientMock
            ->expects($this->once())
            ->method('sendPatchRequest')
            ->with(
                'https://api.example.com/api/customers/123/prospect',
                ['intacctId' => $intacctId]
            )
            ->willThrowException($transportException);

        $this->expectException(APICommunicationException::class);
        $this->expectExceptionMessage('Error communicating with Unification API: Network error');

        $this->service->updateCustomerDoNotMail($customerId, $dto, $intacctId);
    }

    public function testUpdateCustomerDoNotMailWithResponseWithoutDataWrapper(): void
    {
        $customerId = 123;
        $intacctId = 'test-intacct-id';
        $dto = new UpdateStochasticCustomerDoNotMailRequestDTO(true);

        // Mock prospect lookup response without 'data' wrapper
        $prospectLookupResponse = $this->createMock(ResponseInterface::class);
        $prospectLookupResponse->method('getStatusCode')->willReturn(Response::HTTP_OK);
        $prospectLookupResponse->method('toArray')->willReturn([
            'id' => 456,
            'customerId' => $customerId,
            'doNotMail' => false,
        ]);

        $this->unificationClientMock->method('getBaseUri')->willReturn('https://api.example.com');

        $this->unificationClientMock
            ->expects($this->once())
            ->method('sendPatchRequest')
            ->with(
                'https://api.example.com/api/customers/123/prospect',
                ['intacctId' => $intacctId]
            )
            ->willReturn($prospectLookupResponse);

        // Mock the prospect service response
        $expectedResult = ['doNotMail' => true];
        $this->updateProspectServiceMock
            ->expects($this->once())
            ->method('updateProspectDoNotMail')
            ->with(456, $this->callback(function (UpdateStochasticProspectDoNotMailRequestDTO $prospectDTO) {
                return true === $prospectDTO->doNotMail;
            }))
            ->willReturn($expectedResult);

        $result = $this->service->updateCustomerDoNotMail($customerId, $dto, $intacctId);

        self::assertSame($expectedResult, $result);
    }
}
