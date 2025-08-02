<?php

declare(strict_types=1);

namespace App\Service\Authnet;

use App\DTO\AuthNet\AuthNetChargeRequestDTO;
use App\DTO\AuthNet\AuthNetChargeResponseDTO;
use net\authorize\api\constants\ANetEnvironment;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\contract\v1\CreateCustomerPaymentProfileRequest;
use net\authorize\api\contract\v1\CreateCustomerProfileRequest;
use net\authorize\api\contract\v1\CreateTransactionRequest;
use net\authorize\api\contract\v1\CustomerAddressType;
use net\authorize\api\contract\v1\CustomerProfilePaymentType;
use net\authorize\api\contract\v1\PaymentProfileType;
use net\authorize\api\contract\v1\TransactionRequestType;
use net\authorize\api\contract\v1\TransactionResponseType;
use net\authorize\api\controller\CreateCustomerPaymentProfileController;
use net\authorize\api\controller\CreateCustomerProfileController;
use net\authorize\api\controller\CreateTransactionController;
use net\authorize\api\controller\GetCustomerPaymentProfileController;
use net\authorize\api\controller\GetCustomerProfileController;

final readonly class AuthnetService
{
    private const bool TEST_DECLINE = false;

    public function __construct(
        private string $authorizeNetApiLoginId,
        private string $authorizeNetTransactionKey,
        private string $authorizeNetUrl,
        private AuthorizeNetErrorMapper $errorMapper,
    ) {
    }

    public function charge(AuthNetChargeRequestDTO $dto): AuthNetChargeResponseDTO
    {
        $transactionId = null;
        $customerProfileId = null;
        $paymentProfileId = null;
        $responseCode = null;
        $error = null;
        $accountType = null;
        $accountLast4 = null;
        $responseData = [
            'authCode' => null,
            'avsResultCode' => null,
            'cvvResultCode' => null,
            'transId' => null,
            'accountNumber' => null,
            'accountType' => null,
            'testRequest' => null,
            'refTransID' => null,
            'responseCode' => null,
            'error' => null,
        ];

        try {
            $existingProfileId = $this->getCustomerProfileId($dto->customerId);
            if (!$existingProfileId) {
                $customerProfileId = $this->createCustomerProfile($dto);
                if (!$customerProfileId) {
                    $error = 'Failed to create Customer Profile';
                    $responseData['error'] = $error;

                    return $this->buildResponseDTO(
                        $transactionId,
                        $customerProfileId,
                        $paymentProfileId,
                        $responseCode,
                        $error,
                        $accountLast4,
                        $accountType,
                        $responseData
                    );
                }
            } else {
                $customerProfileId = $existingProfileId;
            }

            $newPaymentProfileId = $this->createPaymentProfile($customerProfileId, $dto);
            if (!$newPaymentProfileId) {
                $error = 'Failed to create Customer Payment Profile';
                $responseData['error'] = $error;

                return $this->buildResponseDTO(
                    $transactionId,
                    $customerProfileId,
                    $paymentProfileId,
                    $responseCode,
                    $error,
                    $accountLast4,
                    $accountType,
                    $responseData
                );
            }
            $paymentProfileId = $newPaymentProfileId;

            $txnResponse = $this->chargeCustomerProfile(
                $customerProfileId,
                $paymentProfileId,
                $dto->amount,
                $dto->invoiceNumber
            );

            if (!$txnResponse) {
                $error = 'We received an incomplete response from the payment processor.';
                $responseData['error'] = $error;

                return $this->buildResponseDTO(
                    $transactionId,
                    $customerProfileId,
                    $paymentProfileId,
                    $responseCode,
                    $error,
                    $accountLast4,
                    $accountType,
                    $responseData
                );
            }

            $errorMessage = $this->handleTransactionErrorFromProfileTxn($txnResponse);
            if (null !== $errorMessage) {
                $error = $errorMessage;
                $responseData['error'] = $errorMessage;
            }

            $transactionId = $txnResponse->getTransId() ?: null;
            $accountType = $txnResponse->getAccountType() ?: null;
            $accountLast4 = $txnResponse->getAccountNumber() ?: null;
            $responseCode = $txnResponse->getResponseCode() ?: null;
            $responseData['transId'] = $transactionId;
            $responseData['accountType'] = $accountType;
            $responseData['accountNumber'] = $accountLast4;
            $responseData['responseCode'] = $responseCode;
            $responseData['error'] = $error;
            $responseData['authCode'] = $txnResponse->getAuthCode() ?: null;
            $responseData['avsResultCode'] = $txnResponse->getAvsResultCode() ?: null;
            $responseData['cvvResultCode'] = $txnResponse->getCvvResultCode() ?: null;
            $responseData['testRequest'] = $txnResponse->getTestRequest();
            $responseData['refTransID'] = $txnResponse->getRefTransID();
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $responseData['error'] = $error;
        }

        return $this->buildResponseDTO(
            $transactionId,
            $customerProfileId,
            $paymentProfileId,
            $responseCode,
            $error,
            $accountLast4,
            $accountType,
            $responseData
        );
    }

    private function createCustomerProfile(AuthNetChargeRequestDTO $dto): ?string
    {
        $merchantAuth = $this->createMerchantAuthentication();
        $profile = new AnetAPI\CustomerProfileType();
        if ($dto->customerId) {
            $profile->setMerchantCustomerId($dto->customerId);
        }
        if ($dto->customerEmail) {
            $profile->setEmail($dto->customerEmail);
        }

        $request = new CreateCustomerProfileRequest();
        $request->setMerchantAuthentication($merchantAuth);
        $request->setProfile($profile);
        $refId = 'ref'.time();
        $request->setRefId($refId);

        $controller = new CreateCustomerProfileController($request);
        $environment = str_contains($this->authorizeNetUrl, 'apitest')
            ? ANetEnvironment::SANDBOX
            : ANetEnvironment::PRODUCTION;

        try {
            /** @var AnetAPI\CreateCustomerProfileResponse $response */
            $response = $controller->executeWithApiResponse($environment);
            if ($response && $response->getMessages()) {
                if ('Ok' === $response->getMessages()->getResultCode()) {
                    $cpId = $response->getCustomerProfileId();

                    return $cpId ?: null;
                }
            }
        } catch (\Exception) {
        }

        return null;
    }

    private function createPaymentProfile(string $customerProfileId, AuthNetChargeRequestDTO $dto): ?string
    {
        $merchantAuth = $this->createMerchantAuthentication();
        $request = new CreateCustomerPaymentProfileRequest();
        $request->setMerchantAuthentication($merchantAuth);
        $request->setCustomerProfileId($customerProfileId);

        $opaqueData = new AnetAPI\OpaqueDataType();
        $opaqueData->setDataDescriptor($dto->dataDescriptor);
        $opaqueData->setDataValue($dto->dataValue);

        $paymentType = new AnetAPI\PaymentType();
        $paymentType->setOpaqueData($opaqueData);

        $paymentProfile = new AnetAPI\CustomerPaymentProfileType();
        $paymentProfile->setCustomerType('individual');
        $paymentProfile->setPayment($paymentType);

        /* @phpstan-ignore-next-line */
        if (self::TEST_DECLINE) {
            $billTo = new CustomerAddressType();
            $billTo->setZip('46282');
            $paymentProfile->setBillTo($billTo);
        }

        $request->setPaymentProfile($paymentProfile);

        $controller = new CreateCustomerPaymentProfileController($request);
        $environment = str_contains($this->authorizeNetUrl, 'apitest')
            ? ANetEnvironment::SANDBOX
            : ANetEnvironment::PRODUCTION;

        try {
            /** @var AnetAPI\CreateCustomerPaymentProfileResponse $response */
            $response = $controller->executeWithApiResponse($environment);
            if ($response && $response->getMessages()) {
                if ('Ok' === $response->getMessages()->getResultCode()) {
                    return $response->getCustomerPaymentProfileId() ?: null;
                } else {
                    $messages = $response->getMessages()->getMessage();
                    if (!empty($messages)) {
                        $errorCode = $messages[0]->getCode();
                        if ('E00039' === $errorCode && $response->getCustomerPaymentProfileId()) {
                            return $response->getCustomerPaymentProfileId();
                        }
                    }
                }
            }
        } catch (\Exception) {
        }

        return null;
    }

    private function chargeCustomerProfile(
        string $customerProfileId,
        string $paymentProfileId,
        float $amount,
        ?string $invoiceNumber,
    ): ?TransactionResponseType {
        $merchantAuth = $this->createMerchantAuthentication();
        $profileToCharge = new CustomerProfilePaymentType();
        $profileToCharge->setCustomerProfileId($customerProfileId);
        $payProfile = new PaymentProfileType();
        $payProfile->setPaymentProfileId($paymentProfileId);
        $profileToCharge->setPaymentProfile($payProfile);

        $transactionRequest = new TransactionRequestType();
        $transactionRequest->setTransactionType('authCaptureTransaction');
        $roundedAmount = (float) number_format($amount, 2, '.', '');
        $transactionRequest->setAmount($roundedAmount);
        $transactionRequest->setProfile($profileToCharge);

        if ($invoiceNumber) {
            $order = new AnetAPI\OrderType();
            $order->setInvoiceNumber($invoiceNumber);
            $transactionRequest->setOrder($order);
        }

        $request = new CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuth);
        $request->setTransactionRequest($transactionRequest);

        $controller = new CreateTransactionController($request);
        $environment = str_contains($this->authorizeNetUrl, 'apitest')
            ? ANetEnvironment::SANDBOX
            : ANetEnvironment::PRODUCTION;
        /** @var AnetAPI\CreateTransactionResponse $response */
        $response = $controller->executeWithApiResponse($environment);

        if ($response && $response->getTransactionResponse()) {
            return $response->getTransactionResponse();
        }

        return null;
    }

    private function handleTransactionErrorFromProfileTxn(TransactionResponseType $txnResponse): ?string
    {
        if ('1' === $txnResponse->getResponseCode()) {
            return null;
        }

        $errors = $txnResponse->getErrors();
        if ($errors && count($errors) > 0) {
            $errorCode = $errors[0]->getErrorCode();
            $errorText = $errors[0]->getErrorText();

            return $this->errorMapper->getErrorMessage($errorCode, $errorText);
        }

        switch ($txnResponse->getResponseCode()) {
            case '2':
                return 'Your card was declined by your bank.';
            case '3':
                return 'There was an error processing your payment. Please verify and try again.';
            case '4':
                return 'Your payment requires additional verification. Our team will contact you.';
        }

        return 'We received an unexpected response from the payment processor.';
    }

    private function getCustomerProfileId(?string $customerId): ?string
    {
        if (!$customerId) {
            return null;
        }

        $merchantAuth = $this->createMerchantAuthentication();
        $request = new AnetAPI\GetCustomerProfileRequest();
        $request->setMerchantAuthentication($merchantAuth);
        $request->setMerchantCustomerId($customerId);
        $request->setIncludeIssuerInfo(true);

        $controller = new GetCustomerProfileController($request);
        $environment = str_contains($this->authorizeNetUrl, 'apitest')
            ? ANetEnvironment::SANDBOX
            : ANetEnvironment::PRODUCTION;

        try {
            /** @var AnetAPI\GetCustomerProfileResponse $response */
            $response = $controller->executeWithApiResponse($environment);
            if (
                $response
                && 'Ok' === $response->getMessages()->getResultCode()
                && null !== $response->getProfile()
            ) {
                $profile = $response->getProfile();

                return $profile->getCustomerProfileId() ?: null;
            }
        } catch (\Exception) {
        }

        return null;
    }

    private function createMerchantAuthentication(): AnetAPI\MerchantAuthenticationType
    {
        $merchantAuth = new AnetAPI\MerchantAuthenticationType();
        $merchantAuth->setName($this->authorizeNetApiLoginId);
        $merchantAuth->setTransactionKey($this->authorizeNetTransactionKey);

        return $merchantAuth;
    }

    public function storePaymentProfileOnly(AuthNetChargeRequestDTO $dto): AuthNetChargeResponseDTO
    {
        $transactionId = null;
        $customerProfileId = null;
        $paymentProfileId = null;
        $responseCode = '1';
        $error = null;
        $accountType = null;
        $accountLast4 = null;
        $responseData = [
            'authCode' => null,
            'avsResultCode' => null,
            'cvvResultCode' => null,
            'transId' => null,
            'accountNumber' => null,
            'accountType' => null,
            'testRequest' => null,
            'refTransID' => null,
            'responseCode' => null,
            'error' => null,
        ];

        try {
            $existingProfileId = $this->getCustomerProfileId($dto->customerId);
            if (!$existingProfileId) {
                $customerProfileId = $this->createCustomerProfile($dto);
                if (!$customerProfileId) {
                    $error = 'Failed to create Customer Profile';
                    $responseCode = null;
                    $responseData['error'] = $error;

                    return $this->buildResponseDTO(
                        $transactionId,
                        $customerProfileId,
                        $paymentProfileId,
                        $responseCode,
                        $error,
                        $accountLast4,
                        $accountType,
                        $responseData
                    );
                }
            } else {
                $customerProfileId = $existingProfileId;
            }

            $newPaymentProfileId = $this->createPaymentProfile($customerProfileId, $dto);
            if (!$newPaymentProfileId) {
                $error = 'Failed to create Customer Payment Profile';
                $responseCode = null;
                $responseData['error'] = $error;

                return $this->buildResponseDTO(
                    $transactionId,
                    $customerProfileId,
                    $paymentProfileId,
                    $responseCode,
                    $error,
                    $accountLast4,
                    $accountType,
                    $responseData
                );
            }
            $paymentProfileId = $newPaymentProfileId;

            $profileDetails = $this->getCustomerPaymentProfileDetails($customerProfileId, $paymentProfileId); // ADDED
            $accountType = $profileDetails['accountType'];
            $accountLast4 = $profileDetails['accountLast4'];
            $responseData['accountType'] = $accountType;
            $responseData['accountNumber'] = $accountLast4;
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $responseCode = null;
            $responseData['error'] = $error;
        }

        return $this->buildResponseDTO(
            $transactionId,
            $customerProfileId,
            $paymentProfileId,
            $responseCode,
            $error,
            $accountLast4,
            $accountType,
            $responseData
        );
    }

    private function getCustomerPaymentProfileDetails(string $customerProfileId, string $paymentProfileId): array
    {
        $merchantAuth = $this->createMerchantAuthentication();
        $request = new AnetAPI\GetCustomerPaymentProfileRequest();
        $request->setMerchantAuthentication($merchantAuth);
        $request->setCustomerProfileId($customerProfileId);
        $request->setCustomerPaymentProfileId($paymentProfileId);

        $environment = str_contains($this->authorizeNetUrl, 'apitest')
            ? ANetEnvironment::SANDBOX
            : ANetEnvironment::PRODUCTION;

        $accountType = null;
        $accountLast4 = null;

        try {
            $controller = new GetCustomerPaymentProfileController($request);
            /** @var AnetAPI\GetCustomerPaymentProfileResponse $response */
            $response = $controller->executeWithApiResponse($environment);

            if (
                $response
                && 'Ok' === $response->getMessages()->getResultCode()
                && null !== $response->getPaymentProfile()
            ) {
                $paymentProfile = $response->getPaymentProfile();
                $creditCard = $paymentProfile->getPayment()->getCreditCard();
                if (null !== $creditCard) {
                    $accountType = $creditCard->getCardType();
                    $accountLast4 = $creditCard->getCardNumber();
                }
            }
        } catch (\Exception) {
        }

        return [
            'accountType' => $accountType,
            'accountLast4' => $accountLast4,
        ];
    }

    private function buildResponseDTO(
        ?string $transactionId,
        ?string $customerProfileId,
        ?string $paymentProfileId,
        ?string $responseCode,
        ?string $error,
        ?string $accountLast4,
        ?string $accountType,
        array $responseData,
    ): AuthNetChargeResponseDTO {
        return new AuthNetChargeResponseDTO(
            transactionId: $transactionId,
            customerProfileId: $customerProfileId,
            paymentProfileId: $paymentProfileId,
            responseCode: $responseCode,
            error: $error,
            accountLast4: $accountLast4,
            accountType: $accountType,
            responseData: $responseData
        );
    }
}
