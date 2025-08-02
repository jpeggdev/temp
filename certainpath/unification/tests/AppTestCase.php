<?php

namespace App\Tests;

use App\Client\FileClient;
use Exception;
use Faker;
use JsonException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AppTestCase extends KernelTestCase
{
    public Faker\Generator $faker;

    public function setUp(): void
    {
        parent::setUp();

        if (!static::$booted) {
            static::bootKernel();
        }

        $this->faker = Faker\Factory::create();
    }
    public function getFaker(): Faker\Generator
    {
        return $this->faker;
    }
    protected function getFileClient(): FileClient
    {
        return $this->getService(
            FileClient::class
        );
    }
    protected function getService(string $serviceClass): ?object
    {
        try {
            return static::getContainer()->get($serviceClass);
        } catch (Exception $e) {
            echo "Could not instantiate: " . $serviceClass;
            echo $e->getMessage();
            return null;
        }
    }

    protected function debugString(string $string): void
    {
        echo $string . PHP_EOL;
    }

    /**
     * @throws JsonException
     */
    protected function debug(mixed $object): void
    {
        $encodedObject = json_encode($object, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        $this->debugString($encodedObject);
    }
}
