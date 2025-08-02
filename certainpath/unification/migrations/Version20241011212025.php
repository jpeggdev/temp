<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241011212025 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE prospect ALTER address1 DROP NOT NULL');
        $this->addSql('ALTER TABLE prospect ALTER city DROP NOT NULL');
        $this->addSql('ALTER TABLE prospect ALTER state DROP NOT NULL');
        $this->addSql('ALTER TABLE prospect ALTER postal_code DROP NOT NULL');
        $this->addSql('ALTER TABLE prospect ALTER postal_code_short DROP NOT NULL');
        $this->addSql('ALTER TABLE prospect ALTER json DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE prospect ALTER address1 SET NOT NULL');
        $this->addSql('ALTER TABLE prospect ALTER city SET NOT NULL');
        $this->addSql('ALTER TABLE prospect ALTER state SET NOT NULL');
        $this->addSql('ALTER TABLE prospect ALTER postal_code SET NOT NULL');
        $this->addSql('ALTER TABLE prospect ALTER postal_code_short SET NOT NULL');
        $this->addSql('ALTER TABLE prospect ALTER json SET NOT NULL');
    }
}
