<?php

require_once __DIR__ . '/../../config/database.php';

class Modulo {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function listarTodos(bool $somenteAtivos = true): array {
        $sql = 'SELECT * FROM modulos';
        if ($somenteAtivos) {
            $sql .= ' WHERE ativo = 1';
        }
        $sql .= ' ORDER BY ordem, nome';
        return $this->db->query($sql)->fetchAll();
    }

    public function buscarPorId(int $id) {
        $stmt = $this->db->prepare('SELECT * FROM modulos WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
