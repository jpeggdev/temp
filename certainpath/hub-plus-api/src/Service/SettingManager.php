<?php

namespace App\Service;

use App\Entity\AppSetting;
use App\Repository\AppSettingRepository;
use Doctrine\ORM\EntityManagerInterface;

class SettingManager
{
    private AppSettingRepository $settingRepository;
    private EntityManagerInterface $em;

    public function __construct(AppSettingRepository $settingRepository, EntityManagerInterface $em)
    {
        $this->settingRepository = $settingRepository;
        $this->em = $em;
    }

    public function getValue(string $name): ?string
    {
        return $this->settingRepository->getValue($name);
    }

    public function getBoolValue(string $name): bool
    {
        $value = $this->getValue($name);

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public function setValue(string $name, ?string $value): void
    {
        $setting = $this->settingRepository->findOneByName($name);

        if (!$setting) {
            $setting = new AppSetting();
            $setting->setName($name);
        }

        $setting->setValue($value);
        $this->em->persist($setting);
        $this->em->flush();
    }
}
