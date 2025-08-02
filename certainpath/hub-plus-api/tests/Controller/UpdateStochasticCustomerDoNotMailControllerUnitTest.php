<?php

namespace App\Tests\Controller;

use App\Controller\UpdateStochasticCustomerDoNotMailController;
use App\DTO\LoggedInUserDTO;
use App\DTO\Request\Customer\UpdateStochasticCustomerDoNotMailRequestDTO;
use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\User;
use App\Service\Unification\UpdateStochasticCustomerDoNotMailService;
use PHPUnit\Framework\TestCase;

class UpdateStochasticCustomerDoNotMailControllerUnitTest extends TestCase
{
    public function testIntacctIdExtractionLogic(): void
    {
        // Test that the correct intacct ID is extracted from different LoggedInUserDTO objects
        $loggedInUserDTO1 = $this->createLoggedInUserDTO('intacct-001');
        $loggedInUserDTO2 = $this->createLoggedInUserDTO('intacct-002');

        $this->assertEquals('intacct-001', $loggedInUserDTO1->getActiveCompany()->getIntacctId());
        $this->assertEquals('intacct-002', $loggedInUserDTO2->getActiveCompany()->getIntacctId());
    }

    public function testRequestDTOHandling(): void
    {
        $dtoTrue = new UpdateStochasticCustomerDoNotMailRequestDTO(true);
        $dtoFalse = new UpdateStochasticCustomerDoNotMailRequestDTO(false);

        $this->assertTrue($dtoTrue->doNotMail);
        $this->assertFalse($dtoFalse->doNotMail);
    }

    public function testControllerConstructorSignature(): void
    {
        // Test that the controller requires the correct service dependency
        $reflection = new \ReflectionClass(UpdateStochasticCustomerDoNotMailController::class);
        $constructor = $reflection->getConstructor();
        $parameters = $constructor->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('updateCustomerDoNotMailService', $parameters[0]->getName());
        $type = $parameters[0]->getType();
        $this->assertInstanceOf(\ReflectionNamedType::class, $type);
        $this->assertEquals(UpdateStochasticCustomerDoNotMailService::class, $type->getName());
    }

    public function testControllerMethodSignature(): void
    {
        // Test that the __invoke method has the correct signature
        $reflection = new \ReflectionClass(UpdateStochasticCustomerDoNotMailController::class);
        $method = $reflection->getMethod('__invoke');
        $parameters = $method->getParameters();

        $this->assertCount(3, $parameters);

        // Check parameter names and types
        $this->assertEquals('customerId', $parameters[0]->getName());
        /** @var \ReflectionParameter $parameterZeroType */
        $parameterZeroType = $parameters[0]->getType();
        $this->assertEquals('int', $parameterZeroType->getName());

        $this->assertEquals('requestDTO', $parameters[1]->getName());
        /** @var \ReflectionParameter $parameterOneType */
        $parameterOneType = $parameters[1]->getType();
        $this->assertEquals(UpdateStochasticCustomerDoNotMailRequestDTO::class, $parameterOneType->getName());

        $this->assertEquals('loggedInUserDTO', $parameters[2]->getName());
        /** @var \ReflectionParameter $parameterTwoType */
        $parameterTwoType = $parameters[2]->getType();
        $this->assertEquals(LoggedInUserDTO::class, $parameterTwoType->getName());
    }

    private function createLoggedInUserDTO(string $intacctId): LoggedInUserDTO
    {
        $user = $this->createMock(User::class);
        $employee = $this->createMock(Employee::class);
        $company = $this->createMock(Company::class);

        $company->method('getIntacctId')->willReturn($intacctId);

        return new LoggedInUserDTO($user, $employee, $company);
    }
}
