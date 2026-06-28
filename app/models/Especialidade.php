<?php

require_once __DIR__ . '/../../config/database.php';

class Especialidade {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ── Listagem ─────────────────────────────────────────────

    public function listarTodos(bool $somenteAtivos = true): array {
        $sql = 'SELECT * FROM especialidades';
        if ($somenteAtivos) {
            $sql .= ' WHERE ativo = 1';
        }
        $sql .= ' ORDER BY nome';
        return $this->db->query($sql)->fetchAll();
    }

    public function listarComFiltro(string $busca = '', string $ativo = ''): array {
        $where  = ['1=1'];
        $params = [];

        if ($busca !== '') {
            $where[] = '(nome LIKE :busca OR codigo_cbos LIKE :busca2)';
            $like = '%' . $busca . '%';
            $params[':busca']  = $like;
            $params[':busca2'] = $like;
        }

        if ($ativo !== '') {
            $where[] = 'ativo = :ativo';
            $params[':ativo'] = (int) $ativo;
        }

        $sql = 'SELECT * FROM especialidades WHERE ' . implode(' AND ', $where) . ' ORDER BY nome';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id) {
        $stmt = $this->db->prepare('SELECT * FROM especialidades WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function contarMedicos(int $id): int {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM medicos WHERE especialidade = (SELECT nome FROM especialidades WHERE id = ?)"
        );
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn();
    }

    // ── Escrita ──────────────────────────────────────────────

    public function criar(array $dados): int {
        $stmt = $this->db->prepare('
            INSERT INTO especialidades (nome, codigo_cbos, descricao, ativo)
            VALUES (:nome, :codigo_cbos, :descricao, :ativo)
        ');
        $stmt->execute([
            ':nome'        => $dados['nome'],
            ':codigo_cbos' => $dados['codigo_cbos'] ?: null,
            ':descricao'   => $dados['descricao']   ?: null,
            ':ativo'       => (int) ($dados['ativo'] ?? 1),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function atualizar(int $id, array $dados): bool {
        $stmt = $this->db->prepare('
            UPDATE especialidades SET
                nome        = :nome,
                codigo_cbos = :codigo_cbos,
                descricao   = :descricao,
                ativo       = :ativo
            WHERE id = :id
        ');
        return $stmt->execute([
            ':nome'        => $dados['nome'],
            ':codigo_cbos' => $dados['codigo_cbos'] ?: null,
            ':descricao'   => $dados['descricao']   ?: null,
            ':ativo'       => (int) ($dados['ativo'] ?? 1),
            ':id'          => $id,
        ]);
    }

    public function excluir(int $id): bool {
        $stmt = $this->db->prepare('DELETE FROM especialidades WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
