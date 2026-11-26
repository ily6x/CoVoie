<?php

final class Database
{
    private static ?PDO $pdo = null;

    public static function get(): PDO
    {
        if (self::$pdo === null) {
            $dsn = getenv('COVOIE_DSN') ?: 'mysql:host=127.0.0.1;port=3306;dbname=covoiturage;charset=utf8mb4';
            $username = getenv('COVOIE_DB_USER') ?: 'root';
            $password = getenv('COVOIE_DB_PASSWORD') ?: '';
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];

            self::$pdo = new PDO($dsn, $username, $password, $options);
        }
        return self::$pdo;
    }

    public static function setConnection(PDO $pdo): void
    {
        self::$pdo = $pdo;
    }

    public static function query(string $sql, array $params = []): PDOStatement
    {
        $statement = self::get()->prepare($sql);
        $statement->execute($params);
        return $statement;
    }

    // Toutes les écritures réussissent ensemble, ou sont annulées en cas d'erreur.
    public static function transaction(callable $operation): mixed
    {
        $pdo = self::get();
        $pdo->beginTransaction();
        try {
            $resultat = $operation();
            $pdo->commit();
            return $resultat;
        }
        catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function lockClause(): string
    {
        return self::get()->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql' ? ' FOR UPDATE' : '';
    }
}
