<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240912152618 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE account_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE address_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE business_unit_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE customer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE email_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE invoice_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE location_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE mail_package_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE phone_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE prospect_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE saved_query_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE subscription_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE users_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('ALTER TABLE address ADD CONSTRAINT FK_D4E6F819B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE business_unit ADD CONSTRAINT FK_8C200E5E9B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E09D182060A FOREIGN KEY (prospect_id) REFERENCES prospect (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E099B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE email ADD CONSTRAINT FK_E7927C749B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_906517449B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_9065174464D218E FOREIGN KEY (location_id) REFERENCES location (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_906517449A1887DC FOREIGN KEY (subscription_id) REFERENCES subscription (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744A58ECB40 FOREIGN KEY (business_unit_id) REFERENCES business_unit (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CB9B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CB6646778D FOREIGN KEY (physical_address_id) REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CB894DAC38 FOREIGN KEY (primary_email_id) REFERENCES email (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CB1A0C4E3A FOREIGN KEY (primary_phone_id) REFERENCES phone (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CBA58ECB40 FOREIGN KEY (business_unit_id) REFERENCES business_unit (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mail_package ADD CONSTRAINT FK_4D5E6FAD182060A FOREIGN KEY (prospect_id) REFERENCES prospect (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE phone ADD CONSTRAINT FK_444F97DD9B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE prospect ADD CONSTRAINT FK_C9CE8C7D9B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE saved_query ADD CONSTRAINT FK_496E6EF29B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D39B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_account ADD CONSTRAINT FK_253B48AEA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_account ADD CONSTRAINT FK_253B48AE9B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE account_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE address_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE business_unit_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE customer_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE email_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE invoice_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE location_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE mail_package_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE phone_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE prospect_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE saved_query_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE subscription_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE users_id_seq CASCADE');
        $this->addSql('ALTER TABLE business_unit DROP CONSTRAINT FK_8C200E5E9B6B5FBA');
        $this->addSql('ALTER TABLE user_account DROP CONSTRAINT FK_253B48AEA76ED395');
        $this->addSql('ALTER TABLE user_account DROP CONSTRAINT FK_253B48AE9B6B5FBA');
        $this->addSql('ALTER TABLE saved_query DROP CONSTRAINT FK_496E6EF29B6B5FBA');
        $this->addSql('ALTER TABLE customer DROP CONSTRAINT FK_81398E09D182060A');
        $this->addSql('ALTER TABLE customer DROP CONSTRAINT FK_81398E099B6B5FBA');
        $this->addSql('ALTER TABLE subscription DROP CONSTRAINT FK_A3C664D39B6B5FBA');
        $this->addSql('ALTER TABLE invoice DROP CONSTRAINT FK_906517449B6B5FBA');
        $this->addSql('ALTER TABLE invoice DROP CONSTRAINT FK_9065174464D218E');
        $this->addSql('ALTER TABLE invoice DROP CONSTRAINT FK_906517449A1887DC');
        $this->addSql('ALTER TABLE invoice DROP CONSTRAINT FK_90651744A58ECB40');
        $this->addSql('ALTER TABLE mail_package DROP CONSTRAINT FK_4D5E6FAD182060A');
        $this->addSql('ALTER TABLE email DROP CONSTRAINT FK_E7927C749B6B5FBA');
        $this->addSql('ALTER TABLE location DROP CONSTRAINT FK_5E9E89CB9B6B5FBA');
        $this->addSql('ALTER TABLE location DROP CONSTRAINT FK_5E9E89CB6646778D');
        $this->addSql('ALTER TABLE location DROP CONSTRAINT FK_5E9E89CB894DAC38');
        $this->addSql('ALTER TABLE location DROP CONSTRAINT FK_5E9E89CB1A0C4E3A');
        $this->addSql('ALTER TABLE location DROP CONSTRAINT FK_5E9E89CBA58ECB40');
        $this->addSql('ALTER TABLE prospect DROP CONSTRAINT FK_C9CE8C7D9B6B5FBA');
        $this->addSql('ALTER TABLE phone DROP CONSTRAINT FK_444F97DD9B6B5FBA');
        $this->addSql('ALTER TABLE address DROP CONSTRAINT FK_D4E6F819B6B5FBA');
    }
}
