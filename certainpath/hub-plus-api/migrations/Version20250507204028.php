<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250507204028 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE event_voucher
            DROP CONSTRAINT FK_96C50C77979B1AD6
        ');
        $this->addSql('
            ALTER TABLE event_voucher
            ADD CONSTRAINT FK_96C50C77979B1AD6
            FOREIGN KEY (company_id)
            REFERENCES company (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE event_voucher
            DROP CONSTRAINT fk_96c50c77979b1ad6
        ');
        $this->addSql('
            ALTER TABLE event_voucher
            ADD CONSTRAINT fk_96c50c77979b1ad6
            FOREIGN KEY (company_id)
            REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ');
    }
}
