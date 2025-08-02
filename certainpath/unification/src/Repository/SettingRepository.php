<?php

namespace App\Repository;

use App\Entity\Setting;
use App\QueryBuilder\SettingQueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class SettingRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly SettingQueryBuilder $settingQueryBuilder,
    ) {
        parent::__construct($registry, Setting::class);
    }

    public function saveSetting(Setting $setting): Setting
    {
        /** @var Setting $saved */
        $saved = $this->save($setting);
        return $saved;
    }

    public function findSettingByName(
        string $name,
        string $sortOrder = 'ASC',
        int $limit = 10
    ): ?Setting {
        return $this->settingQueryBuilder
            ->createFindSettingByNameQueryBuilder($name, $sortOrder, $limit)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
