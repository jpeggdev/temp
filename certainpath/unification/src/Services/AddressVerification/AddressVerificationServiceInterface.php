<?php

namespace App\Services\AddressVerification;

use App\Entity\AbstractAddress;

interface AddressVerificationServiceInterface
{
    public function verifyAndNormalize(AbstractAddress $address): AbstractAddress;

    public function getApiType(): string;
}
