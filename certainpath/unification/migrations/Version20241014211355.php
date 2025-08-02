<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241014211355 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE invoice ALTER total DROP NOT NULL');
        $this->addSql('ALTER TABLE invoice ALTER balance DROP NOT NULL');
        $this->addSql('ALTER TABLE invoice ALTER description DROP NOT NULL');
        $this->addSql('ALTER TABLE invoice ALTER invoiced_at DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE invoice ALTER description SET NOT NULL');
        $this->addSql('ALTER TABLE invoice ALTER total SET NOT NULL');
        $this->addSql('ALTER TABLE invoice ALTER balance SET NOT NULL');
        $this->addSql('ALTER TABLE invoice ALTER invoiced_at SET NOT NULL');
    }
}
