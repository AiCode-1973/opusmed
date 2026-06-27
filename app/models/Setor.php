<?php

require_once __DIR__ . '/../../config/database.php';

class Setor {

    private $db;

    public static $categorias = [
        'UTI'              => 'UTI',
        'Enfermaria'       => 'Enfermaria',
        'Centro Cirúrgico' => 'Centro Cirúrgico',
        'Pronto-Socorro'   => 'Pronto-Socorro',
        'Ambulatório'      => 'Ambulatório',
        'Maternidade'      => 'Maternidade',
        'Pediatria'        => 'Pediatria',
        'Oncologia'        => 'Oncologia',
        'Cardiologia'      => 'Cardiologia',
        'Radiologia'       => 'Radiologia',
        'Laboratório'      => 'Laboratório',
        'Farmácia'         => 'Farmácia',
        'Administrativo'   => 'Administrativo',
        'Recepção'         => 'Recepção',
        'Outros'           => 'Outros',
    ];

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function listarTodos(bool $somenteAtivos = false): array {
        $where = $somenteAtivos ? 'WHERE ativo = 1' : '';
        return $this->db->query("
            SELECT * FROM setores $where ORDER BY categoria, nome
        ")->fetchAll();
    }

    public function listarComFiltro(string $busca = '', string $categoria = ''): array {
        $where  = ['1=1'];
        $params = [];

        if ($busca !== '') {
            $where[] = '(nome LIKE :busca OR codigo LIKE :busca2)';
            $like = '%' . $busca . '%';
            $params[':busca']  = $like;
            $params[':busca2'] = $like;
        }
        if ($categoria !== '') {
            $where[] = 'categoria = :categoria';
            $params[':categoria'] = $categoria;
        }

        $sql  = 'SELECT * FROM setores WHERE ' . implode(' AND ', $where) . ' ORDER BY categoria, nome';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id) {
        $stmt = $this->db->prepare('SELECT * FROM setores WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function criar(array $d): int {
        $stmt = $this->db->prepare('
            INSERT INTO setores (codigo, nome, categoria, descricao, ativo)
            VALUES (:codigo, :nome, :categoria, :descricao, :ativo)
        ');
        $stmt->execute([
            ':codigo'    => trim($d['codigo']),
            ':nome'      => trim($d['nome']),
            ':categoria' => trim($d['categoria']),
            ':descricao' => trim($d['descricao'] ?? '') ?: null,
            ':ativo'     => (int) ($d['ativo'] ?? 1),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function atualizar(int $id, array $d): bool {
        $stmt = $this->db->prepare('
            UPDATE setores SET
                codigo    = :codigo,
                nome      = :nome,
                categoria = :categoria,
                descricao = :descricao,
                ativo     = :ativo
            WHERE id = :id
        ');
        return $stmt->execute([
            ':codigo'    => trim($d['codigo']),
            ':nome'      => trim($d['nome']),
            ':categoria' => trim($d['categoria']),
            ':descricao' => trim($d['descricao'] ?? '') ?: null,
            ':ativo'     => (int) ($d['ativo'] ?? 1),
            ':id'        => $id,
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
}
