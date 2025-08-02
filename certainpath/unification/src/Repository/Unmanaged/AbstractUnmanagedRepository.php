<?php

namespace App\Repository\Unmanaged;

use Doctrine\DBAL\Connection;
use InvalidArgumentException;

use function app_db;
use function App\Functions\app_stringList;

abstract class AbstractUnmanagedRepository
{
    protected ?Connection $db;
    protected string $databaseUrl;
    protected array $databaseTables;

    public function __construct()
    {
        $this->db = app_db(
            $this->getDatabaseUrl()
        );
    }

    public function getDatabase(): ?Connection
    {
        return $this->db;
    }

    public function getDatabaseUrl(): string
    {
        return $this->databaseUrl;
    }

    public function getDatabaseTables(): array
    {
        return $this->databaseTables;
    }

    public function isLocalDatabase(): bool
    {
        $params = $this->getDatabase()?->getParams();
        $host = $params['host'] ?? '';

        return in_array($host, ['localhost', '127.0.0.1', '::1'], true);
    }

    protected function validateTable(string $table): void
    {
        if (!in_array($table, $this->getDatabaseTables(), true)) {
            throw new InvalidArgumentException(sprintf(
                '%s is not a valid table. Valid tables are: %s',
                $table,
                app_stringList($this->getDatabaseTables())
            ));
        }
    }
}
