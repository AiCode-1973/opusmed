<?php

require_once __DIR__ . '/../../config/database.php';

class Setor {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /** Retorna categorias ativas do banco: [id => nome, ...] */
    public function listarCategorias(): array {
        $rows = $this->db->query("SELECT id, nome FROM categorias_setor WHERE ativo = 1 ORDER BY nome")->fetchAll();
        $out  = [];
        foreach ($rows as $r) {
            $out[$r['id']] = $r['nome'];
        }
        return $out;
    }

    public function listarTodos(bool $somenteAtivos = false): array {
        $where = $somenteAtivos ? 'WHERE s.ativo = 1' : '';
        return $this->db->query("
            SELECT s.*, c.nome AS categoria_nome
            FROM setores s
            LEFT JOIN categorias_setor c ON c.id = s.categoria_id
            $where
            ORDER BY c.nome, s.nome
        ")->fetchAll();
    }

    public function listarComFiltro(string $busca = '', int $categoriaId = 0): array {
        $where  = ['1=1'];
        $params = [];

        if ($busca !== '') {
            $where[] = '(s.nome LIKE :busca OR s.codigo LIKE :busca2)';
            $like = '%' . $busca . '%';
            $params[':busca']  = $like;
            $params[':busca2'] = $like;
        }
        if ($categoriaId > 0) {
            $where[] = 's.categoria_id = :cat_id';
            $params[':cat_id'] = $categoriaId;
        }

        $sql  = 'SELECT s.*, c.nome AS categoria_nome
                 FROM setores s
                 LEFT JOIN categorias_setor c ON c.id = s.categoria_id
                 WHERE ' . implode(' AND ', $where) . '
                 ORDER BY c.nome, s.nome';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id) {
        $stmt = $this->db->prepare('
            SELECT s.*, c.nome AS categoria_nome
            FROM setores s
            LEFT JOIN categorias_setor c ON c.id = s.categoria_id
            WHERE s.id = ?
        ');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function criar(array $d): int {
        $stmt = $this->db->prepare('
            INSERT INTO setores (codigo, nome, categoria_id, descricao, ativo)
            VALUES (:codigo, :nome, :categoria_id, :descricao, :ativo)
        ');
        $stmt->execute([
            ':codigo'      => trim($d['codigo']),
            ':nome'        => trim($d['nome']),
            ':categoria_id'=> ($d['categoria_id'] != '' ? (int) $d['categoria_id'] : null),
            ':descricao'   => trim($d['descricao'] ?? '') ?: null,
            ':ativo'       => (int) ($d['ativo'] ?? 1),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function atualizar(int $id, array $d): bool {
        $stmt = $this->db->prepare('
            UPDATE setores SET
                codigo      = :codigo,
                nome        = :nome,
                categoria_id= :categoria_id,
                descricao   = :descricao,
                ativo       = :ativo
            WHERE id = :id
        ');
        return $stmt->execute([
            ':codigo'      => trim($d['codigo']),
            ':nome'        => trim($d['nome']),
            ':categoria_id'=> ($d['categoria_id'] != '' ? (int) $d['categoria_id'] : null),
            ':descricao'   => trim($d['descricao'] ?? '') ?: null,
            ':ativo'       => (int) ($d['ativo'] ?? 1),
            ':id'          => $id,
        ]);
    }

    public function toggleAtivo(int $id): bool {
        return $this->db->prepare('UPDATE setores SET ativo = IF(ativo=1,0,1) WHERE id = ?')->execute([$id]);
    }

    public function codigoExiste(string $codigo, int $ignorarId = 0): bool {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM setores WHERE codigo = ? AND id <> ?');
        $stmt->execute([$codigo, $ignorarId]);
        return $stmt->fetchColumn() > 0;
    }

    public function excluir(int $id): bool {
        return $this->db->prepare('DELETE FROM setores WHERE id = ?')->execute([$id]);
    }
}
