<?php

require_once __DIR__ . '/../../config/database.php';

class Usuario {

    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // -------------------------------------------------------
    // Busca
    // -------------------------------------------------------

    public function buscarPorId(int $id) {
        $stmt = $this->db->prepare('
            SELECT u.*, p.nome AS perfil_nome
            FROM usuarios u
            INNER JOIN perfis p ON p.id = u.perfil_id
            WHERE u.id = ?
        ');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function buscarPorEmail(string $email) {
        $stmt = $this->db->prepare('
            SELECT u.*, p.nome AS perfil_nome
            FROM usuarios u
            INNER JOIN perfis p ON p.id = u.perfil_id
            WHERE u.email = ?
        ');
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function listarTodos(bool $somenteAtivos = true): array {
        $sql = '
            SELECT u.id, u.nome, u.email, u.cpf, u.telefone,
                   u.ativo, u.ultimo_acesso, u.created_at,
                   p.nome AS perfil_nome
            FROM usuarios u
            INNER JOIN perfis p ON p.id = u.perfil_id
        ';
        if ($somenteAtivos) {
            $sql .= ' WHERE u.ativo = 1';
        }
        $sql .= ' ORDER BY u.nome';

        return $this->db->query($sql)->fetchAll();
    }

    // -------------------------------------------------------
    // Criação / Atualização
    // -------------------------------------------------------

    public function criar(array $dados): int {
        $stmt = $this->db->prepare('
            INSERT INTO usuarios (perfil_id, nome, email, senha, cpf, telefone, foto)
            VALUES (:perfil_id, :nome, :email, :senha, :cpf, :telefone, :foto)
        ');
        $stmt->execute([
            ':perfil_id' => $dados['perfil_id'],
            ':nome'      => $dados['nome'],
            ':email'     => $dados['email'],
            ':senha'     => password_hash($dados['senha'], PASSWORD_BCRYPT, ['cost' => 12]),
            ':cpf'       => $dados['cpf']      ?? null,
            ':telefone'  => $dados['telefone'] ?? null,
            ':foto'      => $dados['foto']     ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function atualizar(int $id, array $dados): bool {
        $campos = [];
        $valores = [];

        $permitidos = ['perfil_id', 'nome', 'email', 'cpf', 'telefone', 'foto', 'ativo'];
        foreach ($permitidos as $campo) {
            if (array_key_exists($campo, $dados)) {
                $campos[]         = "`$campo` = :$campo";
                $valores[":$campo"] = $dados[$campo];
            }
        }

        if (empty($campos)) {
            return false;
        }

        $valores[':id'] = $id;
        $sql = 'UPDATE usuarios SET ' . implode(', ', $campos) . ' WHERE id = :id';
        return $this->db->prepare($sql)->execute($valores);
    }

    public function alterarSenha(int $id, string $novaSenha): bool {
        $stmt = $this->db->prepare('UPDATE usuarios SET senha = ? WHERE id = ?');
        return $stmt->execute([
            password_hash($novaSenha, PASSWORD_BCRYPT, ['cost' => 12]),
            $id
        ]);
    }

    public function registrarUltimoAcesso(int $id): void {
        $stmt = $this->db->prepare('UPDATE usuarios SET ultimo_acesso = NOW() WHERE id = ?');
        $stmt->execute([$id]);
    }

    // -------------------------------------------------------
    // Autenticação
    // -------------------------------------------------------

    public function autenticar(string $email, string $senha) {
        $usuario = $this->buscarPorEmail($email);

        if (!$usuario || !$usuario['ativo']) {
            return false;
        }

        if (!password_verify($senha, $usuario['senha'])) {
            return false;
        }

        $this->registrarUltimoAcesso($usuario['id']);

        // Nunca retorna o hash da senha
        unset($usuario['senha']);
        return $usuario;
    }

    // -------------------------------------------------------
    // Exclusão lógica
    // -------------------------------------------------------

    public function desativar(int $id): bool {
        $stmt = $this->db->prepare('UPDATE usuarios SET ativo = 0 WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
