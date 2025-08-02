# User Story: ST-015 - Credential Management Controllers

## Story Information
- **Story ID**: ST-015
- **Epic**: User Experience & Management Interface
- **Phase**: Phase 3 - UI Components & Synchronization Services
- **Story Points**: 6
- **Priority**: Must Have
- **Component**: Controller Layer

## User Story
**As a** client administrator  
**I want** API endpoints for managing ServiceTitan credentials  
**So that** I can securely configure and maintain my ServiceTitan integration

## Detailed Description
This story creates REST API endpoints for complete ServiceTitan credential lifecycle management. The controllers handle creation, reading, updating, deletion, and testing of credentials with proper security, validation, and error handling.

## Acceptance Criteria
- [ ] Create CRUD endpoints for ServiceTitan credentials
- [ ] Implement secure credential input with masked fields
- [ ] Add connection testing endpoint
- [ ] Include credential validation before saving
- [ ] Support environment switching (Integration/Production)
- [ ] Add proper access control with security voters

## Technical Implementation Notes
- **Controller Location**: `src/Module/ServiceTitan/Feature/CredentialManagement/Controller/`
- **Pattern**: Follow single-action controller pattern
- **Architecture Reference**: Section 7.1

### Controller Endpoints

#### CreateServiceTitanCredentialController
```php
// POST /api/servicetitan/credentials
class CreateServiceTitanCredentialController extends AbstractController
{
    public function __invoke(
        CreateServiceTitanCredentialRequest $request,
        ServiceTitanCredentialService $credentialService
    ): JsonResponse {
        $this->denyAccessUnlessGranted('CREATE_SERVICETITAN_CREDENTIAL', $request->getCompany());
        
        try {
            $credential = $credentialService->createCredential($request);
            return $this->json(
                new ServiceTitanCredentialResponse($credential),
                Response::HTTP_CREATED
            );
        } catch (ValidationException $e) {
            return $this->json(['errors' => $e->getErrors()], Response::HTTP_BAD_REQUEST);
        }
    }
}
```

#### GetServiceTitanCredentialController
```php
// GET /api/servicetitan/credentials/{id}
class GetServiceTitanCredentialController extends AbstractController
{
    public function __invoke(
        string $id,
        ServiceTitanCredentialRepository $repository
    ): JsonResponse {
        $credential = $repository->find($id);
        
        if (!$credential) {
            throw new NotFoundHttpException('ServiceTitan credential not found');
        }
        
        $this->denyAccessUnlessGranted('VIEW_SERVICETITAN_CREDENTIAL', $credential);
        
        return $this->json(new ServiceTitanCredentialResponse($credential));
    }
}
```

#### UpdateServiceTitanCredentialController
```php
// PUT /api/servicetitan/credentials/{id}
class UpdateServiceTitanCredentialController extends AbstractController
{
    public function __invoke(
        string $id,
        UpdateServiceTitanCredentialRequest $request,
        ServiceTitanCredentialService $credentialService
    ): JsonResponse {
        $credential = $credentialService->findCredential($id);
        $this->denyAccessUnlessGranted('EDIT_SERVICETITAN_CREDENTIAL', $credential);
        
        try {
            $updatedCredential = $credentialService->updateCredential($credential, $request);
            return $this->json(new ServiceTitanCredentialResponse($updatedCredential));
        } catch (ValidationException $e) {
            return $this->json(['errors' => $e->getErrors()], Response::HTTP_BAD_REQUEST);
        }
    }
}
```

#### DeleteServiceTitanCredentialController
```php
// DELETE /api/servicetitan/credentials/{id}
class DeleteServiceTitanCredentialController extends AbstractController
{
    public function __invoke(
        string $id,
        ServiceTitanCredentialService $credentialService
    ): JsonResponse {
        $credential = $credentialService->findCredential($id);
        $this->denyAccessUnlessGranted('DELETE_SERVICETITAN_CREDENTIAL', $credential);
        
        $credentialService->deleteCredential($credential);
        
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
```

#### TestServiceTitanCredentialController
```php
// POST /api/servicetitan/credentials/{id}/test
class TestServiceTitanCredentialController extends AbstractController
{
    public function __invoke(
        string $id,
        ServiceTitanIntegrationService $integrationService
    ): JsonResponse {
        $credential = $integrationService->findCredential($id);
        $this->denyAccessUnlessGranted('TEST_SERVICETITAN_CREDENTIAL', $credential);
        
        $testResult = $integrationService->testCredentials($credential);
        
        return $this->json(new ConnectionTestResponse($testResult));
    }
}
```

#### ListServiceTitanCredentialsController
```php
// GET /api/servicetitan/credentials
class ListServiceTitanCredentialsController extends AbstractController
{
    public function __invoke(
        Request $request,
        ServiceTitanCredentialRepository $repository
    ): JsonResponse {
        $company = $this->getUser()->getCurrentCompany();
        $this->denyAccessUnlessGranted('LIST_SERVICETITAN_CREDENTIALS', $company);
        
        $credentials = $repository->findByCompany($company);
        
        $response = array_map(
            fn($credential) => new ServiceTitanCredentialResponse($credential),
            $credentials
        );
        
        return $this->json($response);
    }
}
```

## Definition of Done
- [ ] All CRUD endpoints implemented and tested
- [ ] Security voters working correctly
- [ ] Credential masking for responses implemented
- [ ] Connection testing functional
- [ ] Environment switching supported
- [ ] API tests for all endpoints passing
- [ ] Request/response DTOs properly validated
- [ ] Error handling comprehensive
- [ ] PHPStan analysis passing
- [ ] Code style compliance verified

## Dependencies
- **ST-001**: ServiceTitan Credential Entity Design
- **ST-014**: ServiceTitan Integration Service
- **ST-017**: Request/Response DTOs
- **ST-018**: Security Voters Implementation

## Testing Requirements
- API tests for all CRUD operations
- Security tests for access control
- Test credential masking in responses
- Test connection testing functionality
- Test error scenarios and validation

### API Test Examples
```php
class ServiceTitanCredentialManagementTest extends AbstractWebTestCase
{
    public function testCreateCredential(): void
    {
        $client = static::createClient();
        $this->loginAsCompanyAdmin($client);
        
        $credentialData = [
            'clientId' => 'test-client-id',
            'clientSecret' => 'test-client-secret',
            'environment' => 'integration'
        ];
        
        $client->request('POST', '/api/servicetitan/credentials', [], [], [], json_encode($credentialData));
        
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        
        $response = json_decode($client->getResponse()->getContent(), true);
        self::assertArrayHasKey('id', $response);
        self::assertSame('integration', $response['environment']);
        self::assertStringContains('***', $response['clientSecret']); // Masked
    }
    
    public function testUnauthorizedAccess(): void
    {
        $client = static::createClient();
        // Don't log in - should be denied
        
        $client->request('GET', '/api/servicetitan/credentials');
        
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
```

### Security Test Examples
```php
public function testCrossCompanyAccessDenied(): void
{
    $client = static::createClient();
    $this->loginAsCompanyUser($client, $this->companyA);
    
    // Try to access credential from different company
    $credentialFromCompanyB = $this->createCredentialForCompany($this->companyB);
    
    $client->request('GET', '/api/servicetitan/credentials/' . $credentialFromCompanyB->getId());
    
    self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
}
```

## Request/Response DTOs Integration
```php
class CreateServiceTitanCredentialRequest
{
    #[Assert\NotBlank]
    public string $clientId;
    
    #[Assert\NotBlank]
    public string $clientSecret;
    
    #[Assert\Choice(['integration', 'production'])]
    public string $environment;
    
    public function getCompany(): Company
    {
        return $this->getUser()->getCurrentCompany();
    }
}

class ServiceTitanCredentialResponse
{
    public function __construct(ServiceTitanCredential $credential)
    {
        $this->id = $credential->getId()->toString();
        $this->environment = $credential->getEnvironment();
        $this->clientId = $this->maskCredential($credential->getClientId());
        $this->clientSecret = $this->maskCredential($credential->getClientSecret());
        $this->connectionStatus = $credential->getConnectionStatus();
        $this->lastConnectionAttempt = $credential->getLastConnectionAttempt();
        $this->createdAt = $credential->getCreatedAt();
    }
    
    private function maskCredential(string $credential): string
    {
        return substr($credential, 0, 4) . str_repeat('*', strlen($credential) - 8) . substr($credential, -4);
    }
}
```

## Security Considerations
- All sensitive credentials masked in API responses
- Proper access control enforcement
- Input validation for all credential data
- Audit logging for credential operations
- Rate limiting for connection testing

## Error Handling
```php
// In controllers
try {
    $result = $service->operation();
    return $this->json($result);
} catch (ValidationException $e) {
    return $this->json(['errors' => $e->getErrors()], 400);
} catch (ServiceTitanApiException $e) {
    return $this->json(['error' => $e->getActionableMessage()], 502);
} catch (\Exception $e) {
    $this->logger->error('Unexpected error in credential management', [
        'exception' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    return $this->json(['error' => 'Internal server error'], 500);
}
```

## Risks and Mitigation
- **Risk**: Credential exposure in API responses
- **Mitigation**: Comprehensive masking and response filtering
- **Risk**: Unauthorized access to credentials
- **Mitigation**: Robust security voters and access control

## Additional Notes
These controllers form the API foundation for the ServiceTitan integration UI. They must handle all security requirements properly while providing a clean, intuitive API for credential management operations.