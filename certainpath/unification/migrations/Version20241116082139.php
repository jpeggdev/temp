<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Trade;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241116082139 extends AbstractMigration
{
    private array $trades = [
        Trade::ELECTRICAL,
        Trade::PLUMBING,
        Trade::HVAC,
        Trade::ROOFING
    ];
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('
            CREATE TABLE trade
            (
                id SERIAL NOT NULL,
                name TEXT NOT NULL,
                PRIMARY KEY(id)
            )
        ');
        foreach ($this->trades as $trade) {
            $this->addSql(sprintf("INSERT INTO trade (name) VALUES ('%s')", $trade));
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE trade');
    }
}
