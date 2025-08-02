<?php

namespace App\Tests\Repository;

use App\Entity\Setting;
use App\Repository\SettingRepository;
use App\Tests\FunctionalTestCase;

class SettingRepositoryTest extends FunctionalTestCase
{
    public function testDoctrineManagedEntity(): void
    {
        $repository = $this->getSettingRepository();

        $name = 'test_setting';
        $setting = (new Setting())
            ->setName($name)
            ->setValue('value1')
            ->setType('string');

        $repository->persist($setting);

        $setting->setValue('value2');

        $repository->flush();
        $result = $repository->findSettingByName($name);

        $this->assertSame('value2', $result->getValue());
    }

    public function testDuplicateDoctrineManagedEntities(): void
    {
        $repository = $this->getSettingRepository();

        $name = 'test_setting';
        $setting = (new Setting())
            ->setName($name)
            ->setValue('value1')
            ->setType('string');

        $managedEntities[] = $setting;

        $setting->setValue('value2');
        $managedEntities[] = $setting;

        foreach ($managedEntities as $managedEntity) {
            $repository->persist($managedEntity);
        }

        $repository->flush();
        $result = $repository->findSettingByName($name);

        $this->assertSame('value2', $result->getValue());
    }

    public function testFindSettingByName(): void
    {
        $name = 'test_setting';
        $setting = (new Setting())
            ->setName($name)
            ->setValue('value')
            ->setType('string');

        $repository = $this->getSettingRepository();

        $result = $repository->findSettingByName($name);
        $this->assertNull($result);

        $result = $this->getSettingRepository()->save($setting);
        $this->assertInstanceOf(Setting::class, $result);

        $result = $repository->findSettingByName($name);
        $this->assertInstanceOf(Setting::class, $result);
    }

    private function getSettingRepository(): SettingRepository
    {
        return $this->getService(
            SettingRepository::class
        );
    }
}
