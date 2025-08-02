<?php

namespace App\ValueObject;

class CampaignProductRecord extends AbstractRecord
{
    use CampaignProductFieldsTrait;

    public function __construct()
    {
        $this->map = new CampaignProductRecordMap();
    }

    public function populateFields(): void
    {
        $this->normalizeNameAndDescriptions();
        $this->type = strtolower($this->type);
        $this->category =
            str_contains($this->name, 'Letter')
                ?
                CampaignProductTaxonomy::LETTERS
                :
                CampaignProductTaxonomy::POSTCARDS;
        $this->subCategory = $this->getSubCategory();
        $this->format = $this->getFormat();
        $this->code = strtoupper($this->code);
        $this->prospectPrice = NumericValue::fromMixedInput($this->prospectPrice)->toSanitizedString();
        $this->customerPrice = NumericValue::fromMixedInput($this->customerPrice)->toSanitizedString();
        $this->hasColoredStock = $this->getHasColoredStock();
        $this->brand =
            $this->getBrand();
        $this->distributionMethod = CampaignProductTaxonomy::DIRECT_MAIL;
        $this->targetAudience = $this->getTargetAudience();
        $this->size = $this->getSize();
    }

    public static function getRecordInstance(): CampaignProductRecord
    {
        return new self();
    }

    public static function getOptionalFields(): array
    {
        return [
            'category' => true,
            'subCategory' => true,
            'format' => true,
            'hasColoredStock' => true,
            'brand' => true,
            'size' => true,
            'distributionMethod' => true,
            'targetAudience' => true,
            'stock' => true,
            'subcategory' => true,
            'prospectprice' => true,
            'customerprice' => true,
            'mailerdescription' => true,
            'hascoloredstock' => true,
            'distributionmethod' => true,
            'targetaudience' => true,
        ];
    }

    /**
     * @return string|null
     */
    private function getBrand(): ?string
    {
        if (str_contains($this->meta, 'Cornerstone')) {
            return 'Cornerstone';
        }
        if (str_contains($this->name, 'Cornerstone')) {
            return 'Cornerstone';
        }
        if (str_contains($this->name, 'Morevent')) {
            return 'Morevent';
        }
        if (str_contains($this->name, 'Fix-it')) {
            return 'Fix-it 24/7';
        }
        if (str_contains($this->name, 'Mister Quik')) {
            return 'Mister Quik';
        }
        if (str_contains($this->name, 'Monarch')) {
            return 'Monarch';
        }
        if (str_contains($this->name, 'Dring')) {
            return 'Dring';
        }
        if (str_contains($this->name, 'Melfi')) {
            return 'Melfi';
        }
        if (str_contains($this->name, 'Same Day')) {
            return 'Same Day';
        }
        return null;
    }

    /**
     * @return string
     */
    private function getTargetAudience(): string
    {
        $description = strtolower($this->description);
        if (str_contains($description, 'targeting prospects ')) {
            return CampaignProductTaxonomy::PROSPECTS;
        }
        if (str_contains($description, 'targeting customers ')) {
            return CampaignProductTaxonomy::CUSTOMERS;
        }
        return CampaignProductTaxonomy::PROSPECTS_AND_CUSTOMERS;
    }

    private function getHasColoredStock(): string
    {
        if (str_contains(strtolower($this->meta), 'colored')) {
            return '1';
        }
        if (str_contains(strtolower($this->name), 'yellow stock')) {
            return '1';
        }
        if (str_contains(strtolower($this->meta), 'green stock')) {
            return '1';
        }
        return '';
    }

    private function getSubCategory(): string
    {
        $name = strtolower($this->name);
        $description = strtolower($this->description);
        if (str_contains($name, 'buy back')) {
            return 'buy_back';
        }
        if (str_contains($name, 'sell us')) {
            return 'sell_us';
        }
        if (str_contains($name, 'close out')) {
            return 'close_out';
        }
        if (str_contains($name, 'inventory')) {
            return 'inventory';
        }
        if (str_contains($name, 'furnace')) {
            return 'furnace';
        }
        if (str_contains($name, 'trade in')) {
            return 'trade_in';
        }
        if (str_contains($name, 'contractor')) {
            return 'contractor';
        }
        if (str_contains($name, 'rebate')) {
            return 'rebate';
        }
        if (str_contains($name, 'tune up')) {
            return 'tune_up';
        }
        if (str_contains($name, 'drain cleaning')) {
            return 'drain_cleaning';
        }
        if (str_contains($name, 'drain')) {
            return 'drain_cleaning';
        }
        if (str_contains($name, 'water heater')) {
            return 'water_heater';
        }
        if (str_contains($name, 'electric')) {
            return 'electric';
        }
        if (str_contains($name, 'plumbing')) {
            return 'plumbing';
        }
        if (str_contains($name, 'roofing')) {
            return 'roofing';
        }
        if (str_contains($description, 'oversized')) {
            return 'oversized';
        }
        if (str_contains($name, 'replacement')) {
            return 'replacement';
        }
        if (str_contains($name, 'eddm')) {
            return 'eddm';
        }
        return 'standard';
    }

    private function getFormat(): string
    {
        $name = strtolower($this->name);
        $description = strtolower($this->description);
        if (
            str_contains($name, '#10')
            || str_contains($description, '#10')
        ) {
            return CampaignProductTaxonomy::NUMBER_10_ENVELOPE;
        }
        if (
            str_contains($name, '6x9')
            || str_contains($name, '6 x 9')
            || str_contains($description, '6x9')
            || str_contains($description, '6 x 9')
        ) {
            return CampaignProductTaxonomy::ENVELOPE_SIZE_6X9;
        }
        if (
            str_contains($name, '8.5x5.5')
            || str_contains($name, '8.5 x 5.5')
            || str_contains($description, '8.5x5.5')
            || str_contains($description, '8.5 x 5.5')
        ) {
            return CampaignProductTaxonomy::MEDIUM_SIZE;
        }
        if (
            str_contains($name, 'oversized')
            || str_contains($description, 'oversized')
        ) {
            return CampaignProductTaxonomy::OVERSIZED;
        }
        return CampaignProductTaxonomy::STANDARD_SIZE;
    }

    private function getSize(): string
    {
        $map = [
            CampaignProductTaxonomy::STANDARD_SIZE => '4.25x6',
            CampaignProductTaxonomy::NUMBER_10_ENVELOPE => '8.5x11',
            CampaignProductTaxonomy::ENVELOPE_SIZE_6X9 => '6x9',
            CampaignProductTaxonomy::OVERSIZED => '11x5.5',
            CampaignProductTaxonomy::MEDIUM_SIZE => '8.5x5.5',
        ];

        $name = strtolower($this->name);
        if (str_contains($name, '8 1/2x11')) {
            return '8.5x11';
        }

        return $map[$this->format];
    }

    private function normalizeNameAndDescriptions(): void
    {
        $this->name = $this->normalizeSubject(
            $this->name
        );
        $this->description = $this->normalizeSubject(
            $this->description
        );
        $this->mailerDescription = $this->normalizeSubject(
            $this->mailerDescription
        );
    }

    private function normalizeSubject(string $subject): string
    {
        //11.5 x 5.5
        //11 x 5.5
        return
            str_replace(
                [
                    '6 x 9',
                    '6 X 9',
                    '6X9',
                    '4.25 x 6',
                    '11 x 5.5',
                    '8.5 x 5.5',
                    '6 x 4.25',
                    '6 X 4.25',
                    '11.5 x 5.5',
                    '8 1/2 x 11'
                ],
                [
                    '6x9',
                    '6x9',
                    '6x9',
                    '4.25x6',
                    '11x5.5',
                    '8.5x5.5',
                    '6x4.25',
                    '6x4.25',
                    '11.5x5.5',
                    '8 1/2x11',
                ],
                $subject
            );
    }
}
