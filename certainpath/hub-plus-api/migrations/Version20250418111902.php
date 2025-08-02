<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250418111902 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE employee_event_favorite_id_seq CASCADE');
        $this->addSql('CREATE TABLE event_employee_role_mapping (id SERIAL NOT NULL, event_id INT NOT NULL, employee_role_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1949CFA971F7E88B ON event_employee_role_mapping (event_id)');
        $this->addSql('CREATE INDEX IDX_1949CFA9564F74A3 ON event_employee_role_mapping (employee_role_id)');
        $this->addSql('CREATE TABLE event_tag (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_124672505E237E06 ON event_tag (name)');
        $this->addSql('CREATE TABLE event_tag_mapping (id SERIAL NOT NULL, event_id INT NOT NULL, event_tag_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D05ED2A071F7E88B ON event_tag_mapping (event_id)');
        $this->addSql('CREATE INDEX IDX_D05ED2A0884B1443 ON event_tag_mapping (event_tag_id)');
        $this->addSql('CREATE TABLE event_trade_mapping (id SERIAL NOT NULL, event_id INT NOT NULL, trade_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9E3FC4D071F7E88B ON event_trade_mapping (event_id)');
        $this->addSql('CREATE INDEX IDX_9E3FC4D0C2D9760 ON event_trade_mapping (trade_id)');
        $this->addSql('ALTER TABLE event_employee_role_mapping ADD CONSTRAINT FK_1949CFA971F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_employee_role_mapping ADD CONSTRAINT FK_1949CFA9564F74A3 FOREIGN KEY (employee_role_id) REFERENCES employee_role (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_tag_mapping ADD CONSTRAINT FK_D05ED2A071F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_tag_mapping ADD CONSTRAINT FK_D05ED2A0884B1443 FOREIGN KEY (event_tag_id) REFERENCES event_tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_trade_mapping ADD CONSTRAINT FK_9E3FC4D071F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_trade_mapping ADD CONSTRAINT FK_9E3FC4D0C2D9760 FOREIGN KEY (trade_id) REFERENCES trade (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE employee_event_favorite DROP CONSTRAINT fk_72ecff798c03f15c');
        $this->addSql('ALTER TABLE employee_event_favorite DROP CONSTRAINT fk_72ecff7971f7e88b');
        $this->addSql('DROP TABLE employee_event_favorite');
        $this->addSql('ALTER TABLE batch_postage ALTER cost SET NOT NULL');
        $this->addSql('ALTER TABLE event DROP CONSTRAINT fk_3bae0aa7a816713f');
        $this->addSql('DROP INDEX idx_3bae0aa7a816713f');
        $this->addSql('ALTER TABLE event DROP event_instructor_id');
        $this->addSql('ALTER TABLE event DROP hide_from_calendar');
        $this->addSql('ALTER TABLE event DROP hide_from_catalog');
        $this->addSql('ALTER TABLE event DROP sgi_voucher_value');
        $this->addSql('ALTER TABLE event DROP is_eligible_for_returning_student');
        $this->addSql('ALTER TABLE event DROP is_voucher_eligible');
        $this->addSql('ALTER TABLE event DROP docebo_event_id');
        $this->addSql('ALTER TABLE event DROP image_url');
        $this->addSql('ALTER TABLE event DROP craft_event_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE employee_event_favorite_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE employee_event_favorite (id SERIAL NOT NULL, employee_id INT NOT NULL, event_id INT NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX unique_employee_event_favorite ON employee_event_favorite (employee_id, event_id)');
        $this->addSql('CREATE INDEX idx_72ecff7971f7e88b ON employee_event_favorite (event_id)');
        $this->addSql('CREATE INDEX idx_72ecff798c03f15c ON employee_event_favorite (employee_id)');
        $this->addSql('COMMENT ON COLUMN employee_event_favorite.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN employee_event_favorite.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE employee_event_favorite ADD CONSTRAINT fk_72ecff798c03f15c FOREIGN KEY (employee_id) REFERENCES employee (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE employee_event_favorite ADD CONSTRAINT fk_72ecff7971f7e88b FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_employee_role_mapping DROP CONSTRAINT FK_1949CFA971F7E88B');
        $this->addSql('ALTER TABLE event_employee_role_mapping DROP CONSTRAINT FK_1949CFA9564F74A3');
        $this->addSql('ALTER TABLE event_tag_mapping DROP CONSTRAINT FK_D05ED2A071F7E88B');
        $this->addSql('ALTER TABLE event_tag_mapping DROP CONSTRAINT FK_D05ED2A0884B1443');
        $this->addSql('ALTER TABLE event_trade_mapping DROP CONSTRAINT FK_9E3FC4D071F7E88B');
        $this->addSql('ALTER TABLE event_trade_mapping DROP CONSTRAINT FK_9E3FC4D0C2D9760');
        $this->addSql('DROP TABLE event_employee_role_mapping');
        $this->addSql('DROP TABLE event_tag');
        $this->addSql('DROP TABLE event_tag_mapping');
        $this->addSql('DROP TABLE event_trade_mapping');
        $this->addSql('ALTER TABLE batch_postage ALTER cost DROP NOT NULL');
        $this->addSql('ALTER TABLE event ADD event_instructor_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD hide_from_calendar BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE event ADD hide_from_catalog BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE event ADD sgi_voucher_value NUMERIC(18, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD is_eligible_for_returning_student BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD is_voucher_eligible BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD docebo_event_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD image_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD craft_event_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT fk_3bae0aa7a816713f FOREIGN KEY (event_instructor_id) REFERENCES event_instructor (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_3bae0aa7a816713f ON event (event_instructor_id)');
    }
}
