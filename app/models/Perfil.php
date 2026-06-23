<?php

require_once __DIR__ . '/../../config/database.php';

class Perfil {

    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function listarTodos(bool $somenteAtivos = true): array {
        $sql = 'SELECT * FROM perfis';
        if ($somenteAtivos) {
            $sql .= ' WHERE ativo = 1';
        }
        $sql .= ' ORDER BY nome';
        return $this->db->query($sql)->fetchAll();
    }

    public function buscarPorId(int $id): array|false {
        $stmt = $this->db->prepare('SELECT * FROM perfis WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function criar(array $dados): int {
        $stmt = $this->db->prepare('
            INSERT INTO perfis (nome, descricao) VALUES (:nome, :descricao)
        ');
        $stmt->execute([
            ':nome'      => $dados['nome'],
            ':descricao' => $dados['descricao'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function atualizar(int $id, array $dados): bool {
        $stmt = $this->db->prepare('
            UPDATE perfis SET nome = :nome, descricao = :descricao, ativo = :ativo WHERE id = :id
        ');
        return $stmt->execute([
            ':nome'      => $dados['nome'],
            ':descricao' => $dados['descricao'] ?? null,
            ':ativo'     => $dados['ativo'] ?? 1,
            ':id'        => $id,
        ]);
    }

    public function excluir(int $id): bool {
        // Impede exclusão se houver usuários vinculados
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM usuarios WHERE perfil_id = ?');
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            throw new \RuntimeException('Não é possível excluir o perfil: existem usuários vinculados.');
        }

        $stmt = $this->db->prepare('DELETE FROM perfis WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
