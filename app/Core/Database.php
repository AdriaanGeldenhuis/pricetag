<?php
/**
 * Database Connection Singleton
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;
    private static array $queryLog = [];

    private function __construct()
    {
    }

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $config = config('database.connections.mysql');

            $dsn = sprintf(
                '%s:host=%s;port=%s;dbname=%s;charset=%s',
                $config['driver'],
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );

            try {
                self::$instance = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    $config['options']
                );
            } catch (PDOException $e) {
                logMessage('error', 'Database connection failed', [
                    'message' => $e->getMessage(),
                ]);

                if (env('APP_DEBUG', false)) {
                    throw $e;
                }

                throw new \RuntimeException('Database connection failed');
            }
        }

        return self::$instance;
    }

    public static function beginTransaction(): bool
    {
        return self::getInstance()->beginTransaction();
    }

    public static function commit(): bool
    {
        return self::getInstance()->commit();
    }

    public static function rollBack(): bool
    {
        return self::getInstance()->rollBack();
    }

    public static function lastInsertId(): string
    {
        return self::getInstance()->lastInsertId();
    }

    public static function logQuery(string $query, array $params = [], float $time = 0): void
    {
        if (env('APP_DEBUG', false)) {
            self::$queryLog[] = [
                'query' => $query,
                'params' => $params,
                'time' => $time,
            ];
        }
    }

    public static function getQueryLog(): array
    {
        return self::$queryLog;
    }
}
