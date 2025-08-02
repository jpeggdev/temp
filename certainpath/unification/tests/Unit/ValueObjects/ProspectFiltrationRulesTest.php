<?php

namespace App\Tests\Unit\ValueObjects;

use App\Tests\AppTestCase;
use App\ValueObjects\ProspectFilterRules;
use JsonException;

class ProspectFiltrationRulesTest extends AppTestCase
{
    /**
     * @throws JsonException
     */
    public function testRules(): void
    {
        $configFile1 = __DIR__ . '/../../Files/prospect-rules/prospect-rules-with-customers-1.json';
        self::assertFileExists($configFile1);
        $rules = ProspectFilterRules::fromConfigFile(
            $configFile1
        );
        self::assertTrue(
            $rules->includeProspectsAndCustomers()
        );
        self::assertFalse(
            $rules->includeCustomersOnly()
        );
        self::assertFalse(
            $rules->includeProspectsOnly()
        );
        self::assertSame(
            1000.0,
            $rules
                ->customerWithSingleInvoiceGreaterValue()
        );
        self::assertSame(
            2500.0,
            $rules
                ->customerWithLifeTimeGreaterValue()
        );
        self::assertTrue(
            $rules
                ->customerWithMembership()
        );
    }

    /**
     * @throws JsonException
     */
    public function testConfigSetups(): void
    {
        $configFile1 = __DIR__ . '/../../Files/prospect-rules/prospect-rules-with-customers-1.json';
        self::assertFileExists($configFile1);

        $configArray = json_decode(
            file_get_contents($configFile1),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $configOne = ProspectFilterRules::fromConfigFile(
            $configFile1
        );

        self::assertSame(
            $configArray,
            $configOne->getConfig()
        );

        $configTwo = ProspectFilterRules::fromArray($configArray);

        self::assertSame(
            $configOne->getConfig(),
            $configTwo->getConfig()
        );
    }
}
