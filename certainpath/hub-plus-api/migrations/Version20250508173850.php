<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Timezone;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250508173850 extends AbstractMigration
{
    private const array INITIAL_VALUES = [
        [Timezone::TIMEZONE_ET_NAME,  Timezone::TIMEZONE_ET_SHORT_NAME],
        [Timezone::TIMEZONE_CT_NAME,  Timezone::TIMEZONE_CT_SHORT_NAME],
        [Timezone::TIMEZONE_MT_NAME,  Timezone::TIMEZONE_MT_SHORT_NAME],
        [Timezone::TIMEZONE_PT_NAME,  Timezone::TIMEZONE_PT_SHORT_NAME],
        [Timezone::TIMEZONE_AKT_NAME, Timezone::TIMEZONE_AKT_SHORT_NAME],
        [Timezone::TIMEZONE_HAT_NAME, Timezone::TIMEZONE_HAT_SHORT_NAME],
    ];

    public function up(Schema $schema): void
    {
        $this->createTimezoneTable();
        $this->insertInitialTimezones();
    }

    public function down(Schema $schema): void
    {
        $this->dropTimezoneTable();
    }

    private function createTimezoneTable(): void
    {
        $this->addSql('
            CREATE TABLE timezone (
                id SERIAL NOT NULL,
                name TEXT NOT NULL,
                short_name TEXT NOT NULL,
                PRIMARY KEY(id)
            )
        ');
        $this->addSql('
            CREATE UNIQUE INDEX UNIQ_3701B2975E237E06
            ON timezone (name)
        ');
    }

    private function dropTimezoneTable(): void
    {
        $this->addSql('DROP TABLE timezone');
    }

    private function insertInitialTimezones(): void
    {
        foreach (self::INITIAL_VALUES as [$name, $short]) {
            $this->addSql(sprintf(
                "INSERT INTO timezone (name, short_name) VALUES ('%s', '%s')",
                $name,
                $short
            ));
        }
    }
}
