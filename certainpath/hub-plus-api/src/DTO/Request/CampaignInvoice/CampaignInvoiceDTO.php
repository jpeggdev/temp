<?php

declare(strict_types=1);

namespace App\DTO\Request\CampaignInvoice;

use Symfony\Component\Validator\Constraints as Assert;

class CampaignInvoiceDTO
{
    public const TYPE_LETTER = 'letter';
    public const TYPE_POSTCARD = 'postcard';

    public function __construct(
        #[Assert\NotBlank(message: 'accountIdentifier should not be blank.')]
        public string $accountIdentifier,
        #[Assert\Choice(choices: [
            self::TYPE_LETTER,
            self::TYPE_POSTCARD,
        ])]
        public string $type,
        public string|int $quantityMailed,
        public string|float $serviceUnitPrice,
        public string|float $postageUnitPrice,
        public string|int $batchReference,
        public ?string $invoiceReference,
    ) {
        $this->batchReference = (int) $batchReference;
        $this->quantityMailed = (int) $quantityMailed;
        $this->serviceUnitPrice = (float) $serviceUnitPrice;
        $this->postageUnitPrice = (float) $postageUnitPrice;
    }
}
