<?php

require_once __DIR__ . '/../../config/database.php';

class Convenio {

    private $db;

    public static $tipos = [
        'plano_saude' => 'Plano de Saúde',
        'sus'         => 'SUS',
        'particular'  => 'Particular',
        'outros'      => 'Outros',
    ];

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function listarTodos(bool $somenteAtivos = true): array {
        $sql = 'SELECT * FROM convenios';
        if ($somenteAtivos) {
            $sql .= ' WHERE ativo = 1';
        }
        $sql .= ' ORDER BY nome';
        return $this->db->query($sql)->fetchAll();
    }

    public function buscarPorId(int $id) {
        $stmt = $this->db->prepare('SELECT * FROM convenios WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function criar(array $dados): int {
        $stmt = $this->db->prepare('
            INSERT INTO convenios
                (nome, codigo_ans, cnpj, telefone, email, site, endereco, tipo, carencia_dias, observacoes)
            VALUES
                (:nome, :codigo_ans, :cnpj, :telefone, :email, :site, :endereco, :tipo, :carencia_dias, :observacoes)
        ');
        $stmt->execute([
            ':nome'          => $dados['nome'],
            ':codigo_ans'    => $dados['codigo_ans']    ?? null,
            ':cnpj'          => $dados['cnpj']          ?? null,
            ':telefone'      => $dados['telefone']      ?? null,
            ':email'         => $dados['email']         ?? null,
            ':site'          => $dados['site']          ?? null,
            ':endereco'      => $dados['endereco']      ?? null,
            ':tipo'          => $dados['tipo']          ?? 'plano_saude',
            ':carencia_dias' => (int) ($dados['carencia_dias'] ?? 0),
            ':observacoes'   => $dados['observacoes']   ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function atualizar(int $id, array $dados): bool {
        $stmt = $this->db->prepare('
            UPDATE convenios SET
                nome          = :nome,
                codigo_ans    = :codigo_ans,
                cnpj          = :cnpj,
                telefone      = :telefone,
                email         = :email,
                site          = :site,
                endereco      = :endereco,
                tipo          = :tipo,
                carencia_dias = :carencia_dias,
                observacoes   = :observacoes,
                ativo         = :ativo
            WHERE id = :id
        ');
        return $stmt->execute([
            ':nome'          => $dados['nome'],
            ':codigo_ans'    => $dados['codigo_ans']    ?? null,
            ':cnpj'          => $dados['cnpj']          ?? null,
            ':telefone'      => $dados['telefone']      ?? null,
            ':email'         => $dados['email']         ?? null,
            ':site'          => $dados['site']          ?? null,
            ':endereco'      => $dados['endereco']      ?? null,
            ':tipo'          => $dados['tipo']          ?? 'plano_saude',
            ':carencia_dias' => (int) ($dados['carencia_dias'] ?? 0),
            ':observacoes'   => $dados['observacoes']   ?? null,
            ':ativo'         => (int) ($dados['ativo']  ?? 1),
            ':id'            => $id,
        ]);
    }

    public function excluir(int $id): bool {
        $stmt = $this->db->prepare('DELETE FROM convenios WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
