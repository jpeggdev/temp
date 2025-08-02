<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250522190827 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE event_discount_ledger
            ADD event_discount_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_event_discount_id
            ON event_discount_ledger (event_discount_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_voucher_ledger
            ADD event_voucher_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_event_voucher_id
            ON event_voucher_ledger (event_voucher_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP INDEX IF EXISTS idx_event_voucher_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_voucher_ledger DROP COLUMN IF EXISTS event_voucher_id
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IF EXISTS idx_event_discount_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event_discount_ledger DROP COLUMN IF EXISTS event_discount_id
        SQL);
    }
}
