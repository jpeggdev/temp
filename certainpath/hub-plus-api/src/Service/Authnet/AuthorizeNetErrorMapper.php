<?php

declare(strict_types=1);

namespace App\Service\Authnet;

class AuthorizeNetErrorMapper
{
    public function getErrorMessage(string $errorCode, ?string $errorText = null): string
    {
        $baseMessage = $this->getDefaultMessageForCode($errorCode) ?: $errorText ?: 'An unknown error occurred';

        return $this->addContextToMessage($errorCode, $baseMessage);
    }

    private function getDefaultMessageForCode(string $errorCode): ?string
    {
        $apiErrorMessages = $this->getApiErrorMessages();
        if (isset($apiErrorMessages[$errorCode])) {
            return $apiErrorMessages[$errorCode];
        }

        $transactionErrorMessages = $this->getTransactionErrorMessages();
        if (isset($transactionErrorMessages[$errorCode])) {
            return $transactionErrorMessages[$errorCode];
        }

        return null;
    }

    private function addContextToMessage(string $errorCode, string $baseMessage): string
    {
        $suggestions = $this->getSuggestionsForErrorCode($errorCode);

        if (!empty($suggestions)) {
            return $baseMessage.' '.$suggestions;
        }

        return $baseMessage;
    }

    private function getSuggestionsForErrorCode(string $errorCode): string
    {
        $errorSuggestions = [
            // Duplicate transaction
            '11' => 'Please wait a few minutes before trying again with a different transaction.',

            // AVS/Card verification failures
            '27' => 'Please verify your billing address matches what your bank has on file.',
            '127' => 'Please verify your billing address matches what your bank has on file.',
            '78' => 'Please check your card security code (CVV) and try again.',

            // Declined transactions
            '2' => 'Please try another payment method or contact your bank.',
            '3' => 'Please try another payment method or contact your bank.',
            '4' => 'Please try another payment method or contact your bank.',
            '5' => 'Please enter a valid payment amount.',

            // Invalid card info
            '6' => 'Please check your card number and try again.',
            '7' => 'Please check your card expiration date and try again.',
            '8' => 'Please use a different credit card that hasn\'t expired.',

            // E-coded errors with recommended actions
            'E00027' => 'Please check your payment information and try again.',
            'E00039' => 'You already have a payment profile with these details.',
        ];

        return $errorSuggestions[$errorCode] ?? '';
    }

    private function getApiErrorMessages(): array
    {
        return [
            'E00001' => 'The payment system is currently experiencing technical difficulties.',
            'E00003' => 'There was a problem with your payment data.',
            'E00004' => 'The payment method requested is invalid.',
            'E00005' => 'The payment credentials are invalid.',
            'E00006' => 'The payment username is invalid.',
            'E00007' => 'The payment authentication failed.',
            'E00008' => 'The payment account is inactive.',
            'E00009' => 'The payment system is in test mode and cannot process real transactions.',

            // Common payment processing errors
            'E00013' => 'One of your payment details is invalid.',
            'E00014' => 'Required payment information is missing.',
            'E00015' => 'One of your payment details exceeds the maximum allowed length.',
            'E00016' => 'One of your payment details has an invalid format.',
            'E00027' => 'Your transaction was unsuccessful.',
            'E00039' => 'A duplicate payment profile already exists.',
            'E00041' => 'Missing required payment information.',
            'E00042' => 'You\'ve reached the maximum number of payment methods allowed.',
        ];
    }

    private function getTransactionErrorMessages(): array
    {
        return [
            '1' => 'Your payment has been approved.',
            '2' => 'Your payment was declined by your bank.',
            '3' => 'Your payment was declined.',
            '4' => 'Your payment was declined.',
            '5' => 'The payment amount is invalid.',
            '6' => 'The credit card number is invalid.',
            '7' => 'The credit card expiration date is invalid.',
            '8' => 'The credit card has expired.',
            '9' => 'The bank routing number is invalid.',
            '10' => 'The bank account number is invalid.',
            '11' => 'A duplicate transaction has been detected.',
            '27' => 'The address verification failed.',
            '28' => 'This type of credit card is not accepted.',
            '44' => 'Your card security code (CVV) is invalid.',
            '45' => 'Your card has been reported lost or stolen.',
            '65' => 'This transaction was declined by your bank.',
            '78' => 'The card security code (CVV) is invalid.',
            '127' => 'The billing address doesn\'t match the address on file with your bank.',
            '200' => 'This transaction has been declined by the fraud detection system.',
            '201' => 'This transaction has been declined by your bank.',
        ];
    }
}
