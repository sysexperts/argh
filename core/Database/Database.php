<?php
/**
 * Database Connection Manager
 * 
 * @package SysExperts\BusinessManager\Database
 */

declare(strict_types=1);

namespace SysExperts\BusinessManager\Database;

use PDO;
use PDOException;

class Database
{
    private ?PDO $connection = null;
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Hole aktive Datenbankverbindung
     */
    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            $this->connect();
        }

        return $this->connection;
    }

    /**
     * Stelle Datenbankverbindung her
     */
    private function connect(): void
    {
        $driver = $this->config['default'];
        $config = $this->config['connections'][$driver];

        try {
            switch ($driver) {
                case 'sqlite':
                    $this->connectSqlite($config);
                    break;
                
                case 'mysql':
                    $this->connectMysql($config);
                    break;
                
                case 'pgsql':
                    $this->connectPgsql($config);
                    break;
                
                default:
                    throw new PDOException("Unsupported database driver: {$driver}");
            }

            // Setze PDO-Attribute
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        } catch (PDOException $e) {
            throw new PDOException("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * SQLite-Verbindung
     */
    private function connectSqlite(array $config): void
    {
        $dbPath = $config['database'];
        
        // Erstelle Verzeichnis falls nicht vorhanden
        $dir = dirname($dbPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $dsn = "sqlite:{$dbPath}";
        $this->connection = new PDO($dsn);
    }

    /**
     * MySQL-Verbindung
     */
    private function connectMysql(array $config): void
    {
        $dsn = sprintf(
            "mysql:host=%s;port=%d;dbname=%s;charset=%s",
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        $this->connection = new PDO(
            $dsn,
            $config['username'],
            $config['password'],
            [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$config['charset']} COLLATE {$config['collation']}"]
        );
    }

    /**
     * PostgreSQL-Verbindung
     */
    private function connectPgsql(array $config): void
    {
        $dsn = sprintf(
            "pgsql:host=%s;port=%d;dbname=%s",
            $config['host'],
            $config['port'],
            $config['database']
        );

        $this->connection = new PDO(
            $dsn,
            $config['username'],
            $config['password']
        );
    }

    /**
     * Query ausführen
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Einzelnes Ergebnis holen
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Alle Ergebnisse holen
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * INSERT ausführen und ID zurückgeben
     */
    public function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, array_values($data));
        
        return (int)$this->getConnection()->lastInsertId();
    }

    /**
     * UPDATE ausführen
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $set = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($data)));
        
        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";
        $stmt = $this->query($sql, array_merge(array_values($data), $whereParams));
        
        return $stmt->rowCount();
    }

    /**
     * DELETE ausführen
     */
    public function delete(string $table, string $where, array $whereParams = []): int
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->query($sql, $whereParams);
        
        return $stmt->rowCount();
    }

    /**
     * Transaktion starten
     */
    public function beginTransaction(): bool
    {
        return $this->getConnection()->beginTransaction();
    }

    /**
     * Transaktion committen
     */
    public function commit(): bool
    {
        return $this->getConnection()->commit();
    }

    /**
     * Transaktion rollback
     */
    public function rollback(): bool
    {
        return $this->getConnection()->rollBack();
    }
}
