<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\EmailTemplateVariable;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250411151346 extends AbstractMigration
{
    private const array EMAIL_TEMPLATE_VARIABLES = [
        [
            'name' => EmailTemplateVariable::SESSION_NAME,
            'description' => 'Name of the session',
        ],
        [
            'name' => EmailTemplateVariable::SESSION_START_DATE,
            'description' => 'Start date of the session',
        ],
        [
            'name' => EmailTemplateVariable::SESSION_END_DATE,
            'description' => 'End date of the session',
        ],
        [
            'name' => EmailTemplateVariable::SESSION_START_TIME,
            'description' => 'Start time of the session',
        ],
        [
            'name' => EmailTemplateVariable::SESSION_END_TIME,
            'description' => 'End time of the session',
        ],
        [
            'name' => EmailTemplateVariable::SESSION_TIME_ZONE,
            'description' => 'Timezone of the session',
        ],
        [
            'name' => EmailTemplateVariable::EVENT_DESCRIPTION,
            'description' => 'Description of the event',
        ],
        [
            'name' => EmailTemplateVariable::EVENT_IMAGE_URL,
            'description' => 'URL to the event image',
        ],
        [
            'name' => EmailTemplateVariable::EVENT_TYPE,
            'description' => 'Type of the event',
        ],
        [
            'name' => EmailTemplateVariable::EVENT_CATEGORY,
            'description' => 'Category of the event',
        ],
        [
            'name' => EmailTemplateVariable::EVENT_VIRTUAL_LINK,
            'description' => 'Virtual link URL',
        ],
    ];

    public function up(Schema $schema): void
    {
        $this->dropCompanyRelation();
        $this->dropValueColumn();
        $this->insertEmailTemplateVariables();
    }

    public function down(Schema $schema): void
    {
        $this->removeEmailTemplateVariables();
    }

    private function dropCompanyRelation(): void
    {
        $this->addSql('
            ALTER TABLE email_template_variable
            DROP CONSTRAINT IF EXISTS fk_c610a839979b1ad6
        ');

        $this->addSql('DROP INDEX IF EXISTS idx_c610a839979b1ad6');

        $this->addSql('
            ALTER TABLE email_template_variable
            DROP IF EXISTS company_id
        ');
    }

    private function dropValueColumn(): void
    {
        $this->addSql('
            ALTER TABLE email_template_variable
            DROP IF EXISTS value
        ');
    }

    private function insertEmailTemplateVariables(): void
    {
        $sql = <<<SQL
            INSERT INTO email_template_variable (
                name,
                description,
                created_at,
                updated_at
            ) VALUES (%s, %s, NOW(), NOW())
        SQL;

        foreach (self::EMAIL_TEMPLATE_VARIABLES as $templateVariable) {
            $name = $this->connection->quote($templateVariable['name']);
            $description = $this->connection->quote($templateVariable['description']);

            $this->addSql(sprintf($sql, $name, $description));
        }
    }

    private function removeEmailTemplateVariables(): void
    {
        $names = array_map(
            fn (array $v) => $this->connection->quote($v['name']),
            self::EMAIL_TEMPLATE_VARIABLES
        );

        if (!empty($names)) {
            $this->addSql(sprintf(
                'DELETE FROM email_template_variable WHERE name IN (%s)',
                implode(', ', $names)
            ));
        }
    }
}
