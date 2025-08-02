<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250221180546 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE campaign_event
            ADD campaign_id INT DEFAULT NULL
        ');
        $this->addSql('
            ALTER TABLE campaign_event
            ADD CONSTRAINT FK_75AB6EC8F639F774
            FOREIGN KEY (campaign_id) REFERENCES campaign (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
        $this->addSql('CREATE INDEX IDX_75AB6EC8F639F774 ON campaign_event (campaign_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE campaign_event DROP CONSTRAINT FK_75AB6EC8F639F774
        ');
        $this->addSql('DROP INDEX IDX_75AB6EC8F639F774');
        $this->addSql('ALTER TABLE campaign_event DROP campaign_id');
    }
}
