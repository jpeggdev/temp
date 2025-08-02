<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\Service;

use App\Module\ServiceTitan\Entity\ServiceTitanCredential;
use Psr\Log\LoggerInterface;

/**
 * Enterprise-grade encryption service for ServiceTitan OAuth credentials.
 *
 * This service implements AES-256-CBC encryption with secure initialization vectors
 * for protecting sensitive credential data at rest. It provides transparent
 * encryption/decryption operations while maintaining security best practices.
 *
 * Security Features:
 * - AES-256-CBC encryption algorithm
 * - Unique IV for each encryption operation
 * - Constant-time comparison for encrypted data validation
 * - Secure error handling without credential exposure
 * - Integration with existing ServiceTitanCredential entity
 */
class ServiceTitanCredentialEncryptionService
{
    private const string ENCRYPTION_PREFIX = 'encrypted:';
    private const string ALGORITHM = 'AES-256-CBC';
    private const int IV_LENGTH = 16; // 128 bits for AES-256-CBC

    public function __construct(
        private readonly string $rawEncryptionKey,
        private readonly LoggerInterface $logger
    ) {
        $this->validateEncryptionKey();
    }

    /**
     * Encrypts a string using AES-256-CBC with a unique initialization vector.
     *
     * @param string|null $data The data to encrypt
     * @return string The encrypted data with the format "encrypted:{base64_encoded_iv_and_data}"
     * @throws \InvalidArgumentException If data is null
     */
    public function encrypt(?string $data): string
    {
        if ($data === null) {
            throw new \InvalidArgumentException('Data to encrypt cannot be null');
        }

        try {
            // Generate a secure random IV for each encryption operation
            $iv = random_bytes(self::IV_LENGTH);

            // Encrypt the data
            $encryptedData = openssl_encrypt(
                $data,
                self::ALGORITHM,
                $this->getProcessedEncryptionKey(),
                OPENSSL_RAW_DATA,
                $iv
            );

            if ($encryptedData === false) {
                $this->logger->error('Failed to encrypt data');
                throw new \RuntimeException('Encryption failed');
            }

            // Combine IV and encrypted data, then base64 encode
            $combined = $iv.$encryptedData;
            $encoded = base64_encode($combined);

            $this->logger->debug('Data successfully encrypted', [
                'data_length' => strlen($data),
                'encrypted_length' => strlen($encoded),
            ]);

            return self::ENCRYPTION_PREFIX.$encoded;
        } catch (\Exception $e) {
            $this->logger->error('Encryption operation failed', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Decrypts data that was encrypted with the encrypt() method.
     *
     * @param string|null $encryptedData The encrypted data to decrypt
     * @return string The decrypted data
     * @throws \InvalidArgumentException If data is null, not encrypted, or corrupted
     */
    public function decrypt(?string $encryptedData): string
    {
        if ($encryptedData === null) {
            throw new \InvalidArgumentException('Data to decrypt cannot be null');
        }

        if (!$this->isEncrypted($encryptedData)) {
            throw new \InvalidArgumentException('Data is not encrypted');
        }

        try {
            // Remove the encryption prefix
            $encodedData = substr($encryptedData, strlen(self::ENCRYPTION_PREFIX));

            // Decode the base64 data
            $combined = base64_decode($encodedData, true);
            if ($combined === false) {
                throw new \InvalidArgumentException('Failed to decrypt data: invalid encrypted data format');
            }

            // Extract IV and encrypted data
            if (strlen($combined) < self::IV_LENGTH) {
                throw new \InvalidArgumentException('Failed to decrypt data: invalid encrypted data format');
            }

            $iv = substr($combined, 0, self::IV_LENGTH);
            $encrypted = substr($combined, self::IV_LENGTH);

            // Decrypt the data
            $decryptedData = openssl_decrypt(
                $encrypted,
                self::ALGORITHM,
                $this->getProcessedEncryptionKey(),
                OPENSSL_RAW_DATA,
                $iv
            );

            if ($decryptedData === false) {
                throw new \InvalidArgumentException('Failed to decrypt data: decryption operation failed');
            }

            $this->logger->debug('Data successfully decrypted', [
                'encrypted_length' => strlen($encryptedData),
                'decrypted_length' => strlen($decryptedData),
            ]);

            return $decryptedData;
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning('Decryption failed with invalid argument', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('Decryption operation failed', [
                'error' => $e->getMessage(),
            ]);
            throw new \InvalidArgumentException('Failed to decrypt data: '.$e->getMessage(), 0, $e);
        }
    }

    /**
     * Encrypts all sensitive fields in a ServiceTitanCredential entity.
     *
     * This method encrypts the following fields if they are not null:
     * - clientId
     * - clientSecret
     * - accessToken
     * - refreshToken
     *
     * @param ServiceTitanCredential $credential The credential entity to encrypt
     */
    public function encryptCredential(ServiceTitanCredential $credential): void
    {
        try {
            $this->logger->info('Encrypting ServiceTitan credential', [
                'credential_id' => $credential->getId(),
                'company_id' => $credential->getCompany()?->getId(),
            ]);

            // Encrypt clientId if present
            if ($credential->getClientId() !== null) {
                $credential->setClientId($this->encrypt($credential->getClientId()));
            }

            // Encrypt clientSecret if present
            if ($credential->getClientSecret() !== null) {
                $credential->setClientSecret($this->encrypt($credential->getClientSecret()));
            }

            // Encrypt accessToken if present
            if ($credential->getAccessToken() !== null) {
                $credential->setAccessToken($this->encrypt($credential->getAccessToken()));
            }

            // Encrypt refreshToken if present
            if ($credential->getRefreshToken() !== null) {
                $credential->setRefreshToken($this->encrypt($credential->getRefreshToken()));
            }

            $this->logger->info('ServiceTitan credential encryption completed', [
                'credential_id' => $credential->getId(),
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to encrypt ServiceTitan credential', [
                'credential_id' => $credential->getId(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Decrypts all encrypted fields in a ServiceTitanCredential entity.
     *
     * This method decrypts the following fields if they are encrypted:
     * - clientId
     * - clientSecret
     * - accessToken
     * - refreshToken
     *
     * Fields that are not encrypted (plain text) are left unchanged.
     *
     * @param ServiceTitanCredential $credential The credential entity to decrypt
     */
    public function decryptCredential(ServiceTitanCredential $credential): void
    {
        try {
            $this->logger->info('Decrypting ServiceTitan credential', [
                'credential_id' => $credential->getId(),
                'company_id' => $credential->getCompany()?->getId(),
            ]);

            // Decrypt clientId if encrypted
            if ($credential->getClientId() !== null && $this->isEncrypted($credential->getClientId())) {
                $credential->setClientId($this->decrypt($credential->getClientId()));
            }

            // Decrypt clientSecret if encrypted
            if ($credential->getClientSecret() !== null && $this->isEncrypted($credential->getClientSecret())) {
                $credential->setClientSecret($this->decrypt($credential->getClientSecret()));
            }

            // Decrypt accessToken if encrypted
            if ($credential->getAccessToken() !== null && $this->isEncrypted($credential->getAccessToken())) {
                $credential->setAccessToken($this->decrypt($credential->getAccessToken()));
            }

            // Decrypt refreshToken if encrypted
            if ($credential->getRefreshToken() !== null && $this->isEncrypted($credential->getRefreshToken())) {
                $credential->setRefreshToken($this->decrypt($credential->getRefreshToken()));
            }

            $this->logger->info('ServiceTitan credential decryption completed', [
                'credential_id' => $credential->getId(),
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to decrypt ServiceTitan credential', [
                'credential_id' => $credential->getId(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Checks if a string is encrypted using constant-time comparison for security.
     *
     * @param string $data The data to check
     * @return bool True if the data is encrypted, false otherwise
     */
    public function isEncrypted(string $data): bool
    {
        if (strlen($data) < strlen(self::ENCRYPTION_PREFIX)) {
            return false;
        }

        // Use constant-time comparison to prevent timing attacks
        $prefix = substr($data, 0, strlen(self::ENCRYPTION_PREFIX));
        return hash_equals(self::ENCRYPTION_PREFIX, $prefix);
    }

    /**
     * Gets the processed encryption key for use in encryption/decryption operations.
     *
     * @return string The processed encryption key
     */
    private function getProcessedEncryptionKey(): string
    {
        $encryptionKey = $this->rawEncryptionKey;

        // Handle base64-encoded keys
        if (str_starts_with($encryptionKey, 'base64:')) {
            $decodedKey = base64_decode(substr($encryptionKey, 7), true);
            if ($decodedKey === false) {
                throw new \InvalidArgumentException('Invalid base64-encoded encryption key');
            }
            $encryptionKey = $decodedKey;
        }

        return $encryptionKey;
    }

    /**
     * Validates the encryption key configuration.
     *
     * @throws \InvalidArgumentException If the key is invalid
     */
    private function validateEncryptionKey(): void
    {
        if (empty($this->rawEncryptionKey)) {
            throw new \InvalidArgumentException('Encryption key cannot be empty');
        }

        $processedKey = $this->getProcessedEncryptionKey();

        // Validate key length for AES-256 (32 bytes / 256 bits)
        if (strlen($processedKey) !== 32) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Encryption key must be exactly 32 bytes for AES-256, got %d bytes',
                    strlen($processedKey)
                )
            );
        }

        $this->logger->info('ServiceTitan encryption service initialized', [
            'algorithm' => self::ALGORITHM,
            'key_length' => strlen($processedKey),
        ]);
    }
}
