<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\EventStatus;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241111172746 extends AbstractMigration
{
    private array $campaignEventStatus = [
        EventStatus::PENDING,
        EventStatus::PROCESSING,
        EventStatus::CREATED,
        EventStatus::FAILED,
    ];

    public function up(Schema $schema): void
    {
        // Create Campaign Event Table
        $this->addSql('CREATE TABLE campaign_event (
            id SERIAL NOT NULL,
            campaign_event_status_id INT NOT NULL,
            campaign_identifier TEXT NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))'
        );
        $this->addSql('CREATE INDEX IDX_75AB6EC890D5B195 ON campaign_event (campaign_event_status_id)');
        $this->addSql('CREATE INDEX campaign_event_campaign_identifier_idx ON campaign_event (campaign_identifier)');
        $this->addSql('CREATE INDEX campaign_event_created_at_idx ON campaign_event (created_at)');
        $this->addSql('COMMENT ON COLUMN campaign_event.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN campaign_event.updated_at IS \'(DC2Type:datetime_immutable)\'');

        // Create Campaign Event Status Table
        $this->addSql('CREATE TABLE campaign_event_status (
            id SERIAL NOT NULL,
            name TEXT NOT NULL,
            PRIMARY KEY(id))
        ');
        $this->addSql('ALTER TABLE campaign_event ADD CONSTRAINT FK_75AB6EC890D5B195 FOREIGN KEY (campaign_event_status_id) REFERENCES campaign_event_status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        // Populate Campaign Event Status Table with initial values
        foreach ($this->campaignEventStatus as $status) {
            $this->addSql(sprintf("INSERT INTO campaign_event_status (name) VALUES ('%s')", $status));
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE campaign_event DROP CONSTRAINT FK_75AB6EC890D5B195');
        $this->addSql('DROP TABLE campaign_event');
        $this->addSql('DROP TABLE campaign_event_status');
    }
}
