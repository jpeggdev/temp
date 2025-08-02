<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250207015422 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE campaign_event (
                id SERIAL NOT NULL,
                event_status_id INT NOT NULL,
                campaign_identifier TEXT NOT NULL,
                error_message TEXT DEFAULT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        ');
        $this->addSql('CREATE INDEX IDX_75AB6EC8ED623E80 ON campaign_event (event_status_id)');
        $this->addSql('CREATE INDEX company_event_created_at_idx ON campaign_event (created_at)');

        $this->addSql('COMMENT ON COLUMN campaign_event.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN campaign_event.updated_at IS \'(DC2Type:datetime_immutable)\'');

        $this->addSql('
            ALTER TABLE campaign_event
            ADD CONSTRAINT FK_75AB6EC8ED623E80
            FOREIGN KEY (event_status_id) REFERENCES event_status (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE campaign_event DROP CONSTRAINT FK_75AB6EC8ED623E80');
        $this->addSql('DROP TABLE campaign_event');
    }
}
