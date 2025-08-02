<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\EventStatus;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250203141124 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE campaign_event ADD company_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE campaign_event ADD CONSTRAINT FK_75AB6EC8979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $status = EventStatus::completed()->getName();
        $this->addSql(
            sprintf("INSERT INTO campaign_event_status (name) VALUES ('%s')", $status)
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE campaign_event DROP CONSTRAINT FK_75AB6EC8979B1AD6');
        $this->addSql('DROP INDEX IDX_75AB6EC8979B1AD6');
        $this->addSql('ALTER TABLE campaign_event DROP company_id');
        $status = EventStatus::completed()->getName();
        $this->addSql(
            sprintf("DELETE FROM campaign_event_status WHERE name = '%s'", $status)
        );
    }
}
