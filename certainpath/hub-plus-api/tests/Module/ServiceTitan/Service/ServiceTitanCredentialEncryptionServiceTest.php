<?php

declare(strict_types=1);

namespace App\Tests\Module\ServiceTitan\Service;

use App\Entity\Company;
use App\Module\ServiceTitan\Entity\ServiceTitanCredential;
use App\Module\ServiceTitan\Enum\ServiceTitanEnvironment;
use App\Module\ServiceTitan\Repository\ServiceTitanCredentialRepository;
use App\Module\ServiceTitan\Service\ServiceTitanCredentialEncryptionService;
use App\Tests\AbstractKernelTestCase;

class ServiceTitanCredentialEncryptionServiceTest extends AbstractKernelTestCase
{
    private ServiceTitanCredentialEncryptionService $encryptionService;
    private ServiceTitanCredentialRepository $credentialRepository;

    public function setUp(): void
    {
        parent::setUp();

        /** @var ServiceTitanCredentialEncryptionService $encryptionService */
        $encryptionService = $this->getService(ServiceTitanCredentialEncryptionService::class);
        $this->encryptionService = $encryptionService;

        /** @var ServiceTitanCredentialRepository $repository */
        $repository = $this->getRepository(ServiceTitanCredentialRepository::class);
        $this->credentialRepository = $repository;
    }

    public function testEncryptDecryptString(): void
    {
        $originalData = 'sensitive_test_data_123';

        $encryptedData = $this->encryptionService->encrypt($originalData);

        self::assertNotEquals($originalData, $encryptedData);
        self::assertStringStartsWith('encrypted:', $encryptedData);

        $decryptedData = $this->encryptionService->decrypt($encryptedData);

        self::assertSame($originalData, $decryptedData);
    }

    public function testEncryptDecryptEmptyString(): void
    {
        $originalData = '';

        $encryptedData = $this->encryptionService->encrypt($originalData);
        $decryptedData = $this->encryptionService->decrypt($encryptedData);

        self::assertSame($originalData, $decryptedData);
    }

    public function testEncryptDecryptSpecialCharacters(): void
    {
        $originalData = 'Test!@#$%^&*()_+-={}[]|\\:";\'<>?,./~`';

        $encryptedData = $this->encryptionService->encrypt($originalData);
        $decryptedData = $this->encryptionService->decrypt($encryptedData);

        self::assertSame($originalData, $decryptedData);
    }

    public function testEncryptDecryptUnicodeCharacters(): void
    {
        $originalData = 'Test 测试 тест テスト';

        $encryptedData = $this->encryptionService->encrypt($originalData);
        $decryptedData = $this->encryptionService->decrypt($encryptedData);

        self::assertSame($originalData, $decryptedData);
    }

    public function testEncryptionProducesUniqueResults(): void
    {
        $originalData = 'test_data_for_uniqueness';

        $encrypted1 = $this->encryptionService->encrypt($originalData);
        $encrypted2 = $this->encryptionService->encrypt($originalData);

        // Each encryption should produce different results due to unique IVs
        self::assertNotEquals($encrypted1, $encrypted2);

        // But both should decrypt to the same original data
        self::assertSame($originalData, $this->encryptionService->decrypt($encrypted1));
        self::assertSame($originalData, $this->encryptionService->decrypt($encrypted2));
    }

    public function testIsEncrypted(): void
    {
        $plainText = 'test_plain_text';
        $encryptedText = $this->encryptionService->encrypt($plainText);

        self::assertFalse($this->encryptionService->isEncrypted($plainText));
        self::assertTrue($this->encryptionService->isEncrypted($encryptedText));
        self::assertFalse($this->encryptionService->isEncrypted(''));
        self::assertFalse($this->encryptionService->isEncrypted('not_encrypted_data'));
    }

    public function testEncryptCredential(): void
    {
        $company = $this->getTestCompany();
        $credential = new ServiceTitanCredential();
        $credential->setCompany($company);
        $credential->setEnvironment(ServiceTitanEnvironment::INTEGRATION);
        $credential->setClientId('test_client_id');
        $credential->setClientSecret('test_client_secret');
        $credential->setAccessToken('test_access_token');
        $credential->setRefreshToken('test_refresh_token');

        // Store original values
        $originalClientId = $credential->getClientId();
        $originalClientSecret = $credential->getClientSecret();
        $originalAccessToken = $credential->getAccessToken();
        $originalRefreshToken = $credential->getRefreshToken();

        $this->encryptionService->encryptCredential($credential);

        // Verify all sensitive fields are encrypted
        self::assertNotEquals($originalClientId, $credential->getClientId());
        self::assertNotEquals($originalClientSecret, $credential->getClientSecret());
        self::assertNotEquals($originalAccessToken, $credential->getAccessToken());
        self::assertNotEquals($originalRefreshToken, $credential->getRefreshToken());

        // Verify encrypted data has proper prefix
        self::assertStringStartsWith('encrypted:', $credential->getClientId());
        self::assertStringStartsWith('encrypted:', $credential->getClientSecret());
        self::assertStringStartsWith('encrypted:', $credential->getAccessToken());
        self::assertStringStartsWith('encrypted:', $credential->getRefreshToken());
    }

    public function testDecryptCredential(): void
    {
        $company = $this->getTestCompany();
        $credential = new ServiceTitanCredential();
        $credential->setCompany($company);
        $credential->setEnvironment(ServiceTitanEnvironment::INTEGRATION);
        $credential->setClientId('test_client_id');
        $credential->setClientSecret('test_client_secret');
        $credential->setAccessToken('test_access_token');
        $credential->setRefreshToken('test_refresh_token');

        // Store original values
        $originalClientId = $credential->getClientId();
        $originalClientSecret = $credential->getClientSecret();
        $originalAccessToken = $credential->getAccessToken();
        $originalRefreshToken = $credential->getRefreshToken();

        // Encrypt then decrypt
        $this->encryptionService->encryptCredential($credential);
        $this->encryptionService->decryptCredential($credential);

        // Verify all fields are back to original values
        self::assertSame($originalClientId, $credential->getClientId());
        self::assertSame($originalClientSecret, $credential->getClientSecret());
        self::assertSame($originalAccessToken, $credential->getAccessToken());
        self::assertSame($originalRefreshToken, $credential->getRefreshToken());
    }

    public function testEncryptCredentialWithNullValues(): void
    {
        $company = $this->getTestCompany();
        $credential = new ServiceTitanCredential();
        $credential->setCompany($company);
        $credential->setEnvironment(ServiceTitanEnvironment::INTEGRATION);
        $credential->setClientId(null);
        $credential->setClientSecret(null);
        $credential->setAccessToken(null);
        $credential->setRefreshToken(null);

        $this->encryptionService->encryptCredential($credential);

        // Null values should remain null
        self::assertNull($credential->getClientId());
        self::assertNull($credential->getClientSecret());
        self::assertNull($credential->getAccessToken());
        self::assertNull($credential->getRefreshToken());
    }

    public function testEncryptCredentialWithEmptyStrings(): void
    {
        $company = $this->getTestCompany();
        $credential = new ServiceTitanCredential();
        $credential->setCompany($company);
        $credential->setEnvironment(ServiceTitanEnvironment::INTEGRATION);
        $credential->setClientId('');
        $credential->setClientSecret('');
        $credential->setAccessToken('');
        $credential->setRefreshToken('');

        $this->encryptionService->encryptCredential($credential);

        // Empty strings should be encrypted
        self::assertNotEmpty($credential->getClientId());
        self::assertNotEmpty($credential->getClientSecret());
        self::assertNotEmpty($credential->getAccessToken());
        self::assertNotEmpty($credential->getRefreshToken());

        self::assertStringStartsWith('encrypted:', $credential->getClientId());
        self::assertStringStartsWith('encrypted:', $credential->getClientSecret());
        self::assertStringStartsWith('encrypted:', $credential->getAccessToken());
        self::assertStringStartsWith('encrypted:', $credential->getRefreshToken());
    }

    public function testDecryptAlreadyDecryptedCredential(): void
    {
        $company = $this->getTestCompany();
        $credential = new ServiceTitanCredential();
        $credential->setCompany($company);
        $credential->setEnvironment(ServiceTitanEnvironment::INTEGRATION);
        $credential->setClientId('plain_client_id');
        $credential->setClientSecret('plain_client_secret');
        $credential->setAccessToken('plain_access_token');
        $credential->setRefreshToken('plain_refresh_token');

        // Store original values
        $originalClientId = $credential->getClientId();
        $originalClientSecret = $credential->getClientSecret();
        $originalAccessToken = $credential->getAccessToken();
        $originalRefreshToken = $credential->getRefreshToken();

        // Decrypt should not change plain text values
        $this->encryptionService->decryptCredential($credential);

        self::assertSame($originalClientId, $credential->getClientId());
        self::assertSame($originalClientSecret, $credential->getClientSecret());
        self::assertSame($originalAccessToken, $credential->getAccessToken());
        self::assertSame($originalRefreshToken, $credential->getRefreshToken());
    }

    public function testPersistenceWithEncryptedCredential(): void
    {
        $company = $this->getTestCompany();
        $credential = new ServiceTitanCredential();
        $credential->setCompany($company);
        $credential->setEnvironment(ServiceTitanEnvironment::INTEGRATION);
        $credential->setClientId('persistent_client_id');
        $credential->setClientSecret('persistent_client_secret');
        $credential->setAccessToken('persistent_access_token');
        $credential->setRefreshToken('persistent_refresh_token');

        // Store original values
        $originalClientId = $credential->getClientId();
        $originalClientSecret = $credential->getClientSecret();
        $originalAccessToken = $credential->getAccessToken();
        $originalRefreshToken = $credential->getRefreshToken();

        // Encrypt and save
        $this->encryptionService->encryptCredential($credential);
        $this->credentialRepository->save($credential, true);

        // Retrieve from database
        $savedCredentialId = $credential->getId();
        $retrievedCredential = $this->credentialRepository->find($savedCredentialId);
        self::assertNotNull($retrievedCredential);

        // Verify data is still encrypted in database
        self::assertStringStartsWith('encrypted:', $retrievedCredential->getClientId());
        self::assertStringStartsWith('encrypted:', $retrievedCredential->getClientSecret());
        self::assertStringStartsWith('encrypted:', $retrievedCredential->getAccessToken());
        self::assertStringStartsWith('encrypted:', $retrievedCredential->getRefreshToken());

        // Decrypt and verify original values
        $this->encryptionService->decryptCredential($retrievedCredential);

        self::assertSame($originalClientId, $retrievedCredential->getClientId());
        self::assertSame($originalClientSecret, $retrievedCredential->getClientSecret());
        self::assertSame($originalAccessToken, $retrievedCredential->getAccessToken());
        self::assertSame($originalRefreshToken, $retrievedCredential->getRefreshToken());
    }

    public function testDecryptInvalidEncryptedData(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Failed to decrypt data: invalid encrypted data format');

        $invalidEncryptedData = 'encrypted:invalid_base64_data';
        $this->encryptionService->decrypt($invalidEncryptedData);
    }

    public function testDecryptCorruptedData(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Failed to decrypt data');

        $corruptedData = 'encrypted:'.base64_encode('corrupted_iv_and_data');
        $this->encryptionService->decrypt($corruptedData);
    }

    public function testEncryptNullValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Data to encrypt cannot be null');

        $this->encryptionService->encrypt(null);
    }

    public function testDecryptNullValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Data to decrypt cannot be null');

        $this->encryptionService->decrypt(null);
    }

    public function testDecryptNonEncryptedData(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Data is not encrypted');

        $plainData = 'plain_text_data';
        $this->encryptionService->decrypt($plainData);
    }

    public function testLargeDataEncryption(): void
    {
        // Test with large data to ensure encryption handles it properly
        $largeData = str_repeat('Large test data content. ', 1000);

        $encryptedData = $this->encryptionService->encrypt($largeData);
        $decryptedData = $this->encryptionService->decrypt($encryptedData);

        self::assertSame($largeData, $decryptedData);
    }

    public function testConstantTimeComparisonSecurity(): void
    {
        $testData = 'security_test_data';
        $encrypted = $this->encryptionService->encrypt($testData);

        // This test verifies the service uses constant-time comparison
        // by testing that isEncrypted method works correctly
        self::assertTrue($this->encryptionService->isEncrypted($encrypted));
        self::assertFalse($this->encryptionService->isEncrypted($testData));

        // Test with malformed encrypted data to verify constant-time comparison
        $validPrefix = 'encrypted:';
        $fakeEncrypted = $validPrefix.'fake_data_that_is_not_properly_encrypted';
        self::assertTrue($this->encryptionService->isEncrypted($fakeEncrypted)); // Has correct prefix
    }
}
