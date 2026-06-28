<?php

require_once __DIR__ . '/../../config/database.php';

class Medico {

    private $db;

    public static $tiposVinculo = [
        'clt'        => 'CLT',
        'pj'         => 'Pessoa Jurídica (PJ)',
        'autonomo'   => 'Autônomo',
        'cooperado'  => 'Cooperado',
        'voluntario' => 'Voluntário',
        'residente'  => 'Residente',
        'outros'     => 'Outros',
    ];

    public static $statusList = [
        'ativo'     => 'Ativo',
        'inativo'   => 'Inativo',
        'ferias'    => 'Férias',
        'afastado'  => 'Afastado',
        'desligado' => 'Desligado',
    ];

    public static $ufs = [
        'AC','AL','AM','AP','BA','CE','DF','ES','GO','MA',
        'MG','MS','MT','PA','PB','PE','PI','PR','RJ','RN',
        'RO','RR','RS','SC','SE','SP','TO',
    ];

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ── Listagem ─────────────────────────────────────────────

    public function listarTodos(bool $somenteAtivos = true): array {
        $where = $somenteAtivos ? 'WHERE m.ativo = 1' : '';
        return $this->db->query("
            SELECT m.id, m.nome, m.cpf, m.crm, m.crm_uf, m.especialidade,
                   m.email, m.telefone, m.tipo_vinculo, m.status, m.ativo,
                   s.nome AS setor_nome
            FROM medicos m
            LEFT JOIN setores s ON s.id = m.setor_id
            $where
            ORDER BY m.nome
        ")->fetchAll();
    }

    public function listarComFiltro(string $busca = '', string $status = ''): array {
        $where  = ['1=1'];
        $params = [];

        if ($busca !== '') {
            $where[] = '(m.nome LIKE :busca OR m.crm LIKE :busca2 OR m.cpf LIKE :busca3 OR m.especialidade LIKE :busca4)';
            $like = '%' . $busca . '%';
            $params[':busca']  = $like;
            $params[':busca2'] = $like;
            $params[':busca3'] = $like;
            $params[':busca4'] = $like;
        }

        if ($status !== '') {
            $where[] = 'm.status = :status';
            $params[':status'] = $status;
        }

        $sql = "
            SELECT m.id, m.nome, m.cpf, m.crm, m.crm_uf, m.especialidade,
                   m.email, m.telefone, m.tipo_vinculo, m.status, m.ativo,
                   s.nome AS setor_nome
            FROM medicos m
            LEFT JOIN setores s ON s.id = m.setor_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY m.nome
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id) {
        $stmt = $this->db->prepare('SELECT * FROM medicos WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // ── Escrita ──────────────────────────────────────────────

    public function criar(array $dados): int {
        $stmt = $this->db->prepare('
            INSERT INTO medicos
                (nome, cpf, crm, crm_uf, especialidade, rqe, email, telefone,
                 tipo_vinculo, setor_id, status)
            VALUES
                (:nome, :cpf, :crm, :crm_uf, :especialidade, :rqe, :email, :telefone,
                 :tipo_vinculo, :setor_id, :status)
        ');
        $stmt->execute($this->_params($dados));
        return (int) $this->db->lastInsertId();
    }

    public function atualizar(int $id, array $dados): bool {
        $params = $this->_params($dados);
        $params[':ativo'] = (int) ($dados['ativo'] ?? 1);
        $params[':id']    = $id;
        $stmt = $this->db->prepare('
            UPDATE medicos SET
                nome          = :nome,
                cpf           = :cpf,
                crm           = :crm,
                crm_uf        = :crm_uf,
                especialidade = :especialidade,
                rqe           = :rqe,
                email         = :email,
                telefone      = :telefone,
                tipo_vinculo  = :tipo_vinculo,
                setor_id      = :setor_id,
                status        = :status,
                ativo         = :ativo
            WHERE id = :id
        ');
        return $stmt->execute($params);
    }

    public function excluir(int $id): bool {
        $stmt = $this->db->prepare('DELETE FROM medicos WHERE id = ?');
        return $stmt->execute([$id]);
    }

    // ── Helpers ──────────────────────────────────────────────

    private function _params(array $d): array {
        return [
            ':nome'          => $d['nome'],
            ':cpf'           => $d['cpf']          ?: null,
            ':crm'           => $d['crm'],
            ':crm_uf'        => $d['crm_uf'],
            ':especialidade' => $d['especialidade'] ?: null,
            ':rqe'           => $d['rqe']           ?: null,
            ':email'         => $d['email']         ?: null,
            ':telefone'      => $d['telefone']      ?: null,
            ':tipo_vinculo'  => $d['tipo_vinculo']  ?? 'autonomo',
            ':setor_id'      => ($d['setor_id'] > 0 ? (int) $d['setor_id'] : null),
            ':status'        => $d['status']        ?? 'ativo',
        ];
    }
}
