<?php

namespace Config;

use Dotenv\Dotenv;

use PDO;
use PDOException;

class Database
{
    private static $pdo;
    private static $host;
    private static $db;
    private static $user;
    private static $pass;

    public static function getInstance()
    {
        // Load environment variables
        $dotenv = Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->load();
        
        if (self::$host === null) {
            self::$host = $_ENV['DB_HOST'];
        }
        if (self::$db === null) {
            self::$db = $_ENV['DB_NAME'];
        }
        if (self::$user === null) {
            self::$user = $_ENV['DB_USER'];
        }
        if (self::$pass === null) {
            self::$pass = $_ENV['DB_PASSWORD'];
        }
        if (self::$pdo === null) {
            $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$db . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            try {
                self::$pdo = new PDO($dsn, self::$user, self::$pass, $options);
            } catch (PDOException $e) {
                throw new PDOException($e->getMessage());
            }
        }
        return self::$pdo;
    }
    public function getConnection()
    {
        return self::getInstance();
    }
}
