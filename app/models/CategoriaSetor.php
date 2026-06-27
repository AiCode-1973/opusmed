<?php

require_once __DIR__ . '/../../config/database.php';

class CategoriaSetor {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function listarTodos(bool $somenteAtivos = false): array {
        $where = $somenteAtivos ? 'WHERE ativo = 1' : '';
        return $this->db->query("SELECT * FROM categorias_setor $where ORDER BY nome")->fetchAll();
    }

    public function buscarPorId(int $id) {
        $stmt = $this->db->prepare('SELECT * FROM categorias_setor WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function criar(array $d): int {
        $stmt = $this->db->prepare('
            INSERT INTO categorias_setor (nome, descricao, ativo)
            VALUES (:nome, :descricao, :ativo)
        ');
        $stmt->execute([
            ':nome'      => trim($d['nome']),
            ':descricao' => trim($d['descricao'] ?? '') ?: null,
            ':ativo'     => (int) ($d['ativo'] ?? 1),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function atualizar(int $id, array $d): bool {
        $stmt = $this->db->prepare('
            UPDATE categorias_setor SET
                nome      = :nome,
                descricao = :descricao,
                ativo     = :ativo
            WHERE id = :id
        ');
        return $stmt->execute([
            ':nome'      => trim($d['nome']),
            ':descricao' => trim($d['descricao'] ?? '') ?: null,
            ':ativo'     => (int) ($d['ativo'] ?? 1),
            ':id'        => $id,
        ]);
    }

    public function toggleAtivo(int $id): bool {
        return $this->db->prepare('UPDATE categorias_setor SET ativo = IF(ativo=1,0,1) WHERE id = ?')->execute([$id]);
    }

    public function nomeExiste(string $nome, int $ignorarId = 0): bool {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM categorias_setor WHERE nome = ? AND id <> ?');
        $stmt->execute([$nome, $ignorarId]);
        return $stmt->fetchColumn() > 0;
    }

    public function contarSetores(int $id): int {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM setores WHERE categoria_id = ?');
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn();
    }
}
