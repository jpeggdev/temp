<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250601221436 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX unique_payment_profile_id
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX unique_payment_profile ON payment_profile (employee_id, authnet_customer_id, authnet_payment_profile_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX unique_payment_profile
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX unique_payment_profile_id ON payment_profile (authnet_payment_profile_id)
        SQL);
    }
}
