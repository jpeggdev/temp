<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250519192603 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE invoice ALTER total DROP DEFAULT');
        $this->addSql('ALTER TABLE invoice ALTER total TYPE NUMERIC(10, 2) USING NULLIF(TRIM(total), \'\')::numeric(10,2)');
        $this->addSql('ALTER TABLE invoice ALTER total SET DEFAULT 0');

        $this->addSql('ALTER TABLE invoice ALTER balance DROP DEFAULT');
        $this->addSql('ALTER TABLE invoice ALTER balance TYPE NUMERIC(10, 2) USING NULLIF(TRIM(balance), \'\')::numeric(10,2)');
        $this->addSql('ALTER TABLE invoice ALTER balance SET DEFAULT 0');

        $this->addSql('ALTER TABLE invoice ALTER sub_total DROP DEFAULT');
        $this->addSql('ALTER TABLE invoice ALTER sub_total TYPE NUMERIC(10, 2) USING NULLIF(TRIM(sub_total), \'\')::numeric(10,2)');
        $this->addSql('ALTER TABLE invoice ALTER sub_total SET DEFAULT 0');

        $this->addSql('ALTER TABLE invoice ALTER tax DROP DEFAULT');
        $this->addSql('ALTER TABLE invoice ALTER tax TYPE NUMERIC(10, 2) USING NULLIF(TRIM(tax), \'\')::numeric(10,2)');
        $this->addSql('ALTER TABLE invoice ALTER tax SET DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE invoice ALTER total DROP DEFAULT');
        $this->addSql('ALTER TABLE invoice ALTER total TYPE TEXT USING total::TEXT');
        $this->addSql('ALTER TABLE invoice ALTER total SET DEFAULT \'0.00\'');

        $this->addSql('ALTER TABLE invoice ALTER balance DROP DEFAULT');
        $this->addSql('ALTER TABLE invoice ALTER balance TYPE TEXT USING balance::TEXT');
        $this->addSql('ALTER TABLE invoice ALTER balance SET DEFAULT \'0.00\'');

        $this->addSql('ALTER TABLE invoice ALTER sub_total DROP DEFAULT');
        $this->addSql('ALTER TABLE invoice ALTER sub_total TYPE TEXT USING sub_total::TEXT');
        $this->addSql('ALTER TABLE invoice ALTER sub_total SET DEFAULT \'0.00\'');

        $this->addSql('ALTER TABLE invoice ALTER tax DROP DEFAULT');
        $this->addSql('ALTER TABLE invoice ALTER tax TYPE TEXT USING tax::TEXT');
        $this->addSql('ALTER TABLE invoice ALTER tax SET DEFAULT \'0.00\'');
    }
}
