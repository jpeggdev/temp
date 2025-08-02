<?php

use Doctrine\DBAL\{Connection, DriverManager};
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

$_CONFIG = $_ENV;

/**
 * Globally namespaced helper function to
 * return the default database connection.
 * @throws \Doctrine\DBAL\Exception
 */
function app_db(string $name = 'DATABASE_URL'): ?Connection
{
    static $connections = [ ];

    if (
        !str_starts_with($name, 'DATABASE_URL') ||
        !array_key_exists($name, $_ENV)
    ) {
        return null;
    }

    if (array_key_exists($name, $connections)) {
        return $connections[$name];
    }

    $connections[$name] = DriverManager::getConnection(
        ['url' => $_ENV[$name]]
    );

    return $connections[$name];
}

/**
 * Globally namespaced helper function to
 * return the configuration variables.
 *
 * @returns array
 */
function app_config(): array
{
    global $_CONFIG;
    return $_CONFIG;
}

/**
 * Globally namespaced helper function to
 * return the default log handler
 */
function app_logger($name = null): Logger
{
    $logger = new Logger('logger');
    $formatter = new LineFormatter();
    $id = $name ?? $_ENV['APP_ENV'] ?? 'log';

    // Debug level handler
    $handler = new StreamHandler(__DIR__ . '/var/log/' . $id . '.log');
    $handler->setFormatter($formatter);

    $logger->pushHandler($handler);

    return $logger;
}
