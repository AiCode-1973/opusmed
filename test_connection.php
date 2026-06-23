<?php
require_once __DIR__ . '/config/database.php';

try {
    $db  = Database::getInstance()->getConnection();
    $stmt = $db->query('SELECT 1');
    echo "Conexão com o banco de dados bem-sucedida!";
} catch (PDOException $e) {
    echo "Erro na conexão: " . $e->getMessage();
}
