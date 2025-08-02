<?php

namespace App\ValueObjects;

use App\DTO\Domain\ProspectFilterRulesDTO;
use JsonException;

class ProspectFilterRules
{
    public const CUSTOMERS_ONLY_RULE = 'customers_only';
    public const PROSPECTS_ONLY_RULE = 'prospects_only';
    public const PROSPECTS_AND_CUSTOMERS_RULE = 'prospects_and_customers';

    private array $config;

    public function __construct(
        array $config,
    ) {
        $this->config = $config;
    }
    /**
     * @throws JsonException
     */
    public static function fromConfigFile(string $configFile): self
    {
        return new self(
            json_decode(
                file_get_contents($configFile),
                true,
                512,
                JSON_THROW_ON_ERROR
            )
        );
    }

    public static function fromArray(mixed $configArray): self
    {
        return new self($configArray);
    }

    public static function fromDto(ProspectFilterRulesDTO $filterRulesDTO): self
    {
        return new self(
            [
                'customer_inclusion' => [
                    'selected' => $filterRulesDTO->customerInclusionRule
                ]
            ]
        );
    }

    public function includeProspectsAndCustomers(): bool
    {
        return
            $this->config['customer_inclusion']['selected'] === self::PROSPECTS_AND_CUSTOMERS_RULE;
    }
    public function includeCustomersOnly(): bool
    {
        return
            $this->config['customer_inclusion']['selected'] === self::CUSTOMERS_ONLY_RULE;
    }
    public function includeProspectsOnly(): bool
    {
        return
            $this->config['customer_inclusion']['selected'] === self::PROSPECTS_ONLY_RULE;
    }
    public function customerWithSingleInvoiceGreaterValue(): ?float
    {
        $inclusion =
            isset(
                $this
                    ->config
                ['customer_criteria']
                ['conditions']
                ['single_invoice']
                ['include']
            )
            &&
            $this
                ->config
                ['customer_criteria']
                ['conditions']
                ['single_invoice']
                ['include'] === true
            &&
            $this
                ->config
                ['customer_criteria']
                ['conditions']
                ['single_invoice']
                ['operator'] === 'greater_than';

        if ($inclusion) {
            return
                $this
                ->config
                ['customer_criteria']
                ['conditions']
                ['single_invoice']
                ['value'];
        }
        return null;
    }

    public function customerWithLifeTimeGreaterValue(): ?float
    {
        $inclusion =
            isset(
                $this
                    ->config
                ['customer_criteria']
                ['conditions']
                ['lifetime_value']
                ['include']
            )
            &&
            $this
                ->config
            ['customer_criteria']
            ['conditions']
            ['lifetime_value']
            ['include'] === true
            &&
            $this
                ->config
            ['customer_criteria']
            ['conditions']
            ['lifetime_value']
            ['operator'] === 'greater_than';

        if ($inclusion) {
            return
                $this
                    ->config
                ['customer_criteria']
                ['conditions']
                ['lifetime_value']
                ['value'];
        }
        return null;
    }

    public function customerWithMembership(): bool
    {
        return
            isset(
                $this
                    ->config
                ['customer_criteria']
                ['conditions']
                ['club_members']
                ['include']
            )
            &&
            $this
                ->config
            ['customer_criteria']
            ['conditions']
            ['club_members']
            ['include'] === true;
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}
