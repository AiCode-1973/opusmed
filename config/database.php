<?php

define('DB_HOST', '69.49.241.25');
define('DB_USER', 'apassa73_opusmed');
define('DB_PASS', 'Dema@1973');
define('DB_NAME', 'apassa73_opusmed');
define('DB_PORT', 3306);
define('DB_CHARSET', 'utf8mb4');

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->pdo;
    }
}
