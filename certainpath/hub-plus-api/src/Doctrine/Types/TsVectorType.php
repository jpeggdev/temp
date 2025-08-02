<?php

declare(strict_types=1);

namespace App\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * A custom DBAL type for PostgreSQL tsvector columns.
 *
 * Usage in your entity:
 *
 *  #[ORM\Column(type: "tsvector", nullable: true)]
 *  private ?string $searchVector = null;
 *
 * Then create a migration that has a column like:
 *  ALTER TABLE resource ADD COLUMN search_vector tsvector;
 *  CREATE INDEX resource_search_vector_idx ON resource USING GIN (search_vector);
 */
final class TsVectorType extends Type
{
    public const string NAME = 'tsvector';

    /**
     * Return the database column definition for this field.
     * Tells Doctrine how to declare the SQL column type (e.g., "TSVECTOR").
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        // This is how the column gets created in your migrations: "TSVECTOR"
        return 'TSVECTOR';
    }

    /**
     * The unique name of this custom type in Doctrine's system.
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * Convert the raw DB value to a PHP value.
     * Usually we just keep it as a string or null, because we don't manipulate tsvectors in PHP.
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        // If your DB returns a string like "'some':1 'text':2", you'll just store it directly.
        // Or you could parse it further if you wanted to do something with it.
        return $value;
    }

    /**
     * Convert the PHP value back to the DB value.
     * Typically you just return the string as is. If you do the to_tsvector(...) logic
     * in triggers or application code, no need to wrap anything here.
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        // We'll simply store whatever the user sets. e.g., "to_tsvector('english', 'some text')"
        // Or if a trigger sets it, we can store null or a raw string.
        return $value;
    }

    /**
     * Ensures that Doctrine adds a comment in the schema so it knows to keep this custom type.
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
