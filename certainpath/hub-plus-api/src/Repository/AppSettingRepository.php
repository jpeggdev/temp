<?php

namespace App\Repository;

use App\Entity\AppSetting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AppSetting>
 */
class AppSettingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AppSetting::class);
    }

    public function findOneByName(string $name): ?AppSetting
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function getValue(string $name): ?string
    {
        $setting = $this->findOneByName($name);

        return $setting?->getValue();
    }
}
