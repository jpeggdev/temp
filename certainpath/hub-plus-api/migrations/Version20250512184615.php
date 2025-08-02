<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\DiscountType;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250512184615 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE discount_type ADD display_name TEXT DEFAULT NULL');

        $this->addSql("
            UPDATE discount_type
            SET display_name = 'Percentage (%)'
            WHERE name = '".DiscountType::EVENT_TYPE_PERCENTAGE."'
        ");

        $this->addSql("
            UPDATE discount_type
            SET display_name = 'Fixed Amount ($)'
            WHERE name = '".DiscountType::EVENT_TYPE_FIXED_AMOUNT."'
        ");

        $this->addSql('
            ALTER TABLE event_discount
            ADD deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL
        ');

        $this->addSql('COMMENT ON COLUMN event_discount.deleted_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE discount_type DROP display_name');
        $this->addSql('ALTER TABLE event_discount DROP deleted_at');
    }
}
