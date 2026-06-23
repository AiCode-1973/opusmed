<?php

require_once __DIR__ . '/../../config/database.php';

class PerfilPermissao {

    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Retorna todas as permissões de um perfil, indexadas pelo nome do módulo.
     * Exemplo de uso: $permissoes['Farmácia']['pode_ver']
     */
    public function buscarPorPerfil(int $perfilId): array {
        $stmt = $this->db->prepare('
            SELECT m.nome AS modulo, m.rota,
                   p.pode_ver, p.pode_criar, p.pode_editar, p.pode_excluir
            FROM perfil_modulo_permissao p
            INNER JOIN modulos m ON m.id = p.modulo_id
            WHERE p.perfil_id = ?
            ORDER BY m.ordem
        ');
        $stmt->execute([$perfilId]);
        $rows = $stmt->fetchAll();

        $resultado = [];
        foreach ($rows as $row) {
            $resultado[$row['modulo']] = $row;
        }
        return $resultado;
    }

    /**
     * Verifica se um perfil tem determinada permissão em um módulo (por nome).
     * Ex.: $this->pode($perfilId, 'Farmácia', 'pode_criar')
     */
    public function pode(int $perfilId, string $moduloNome, string $tipo = 'pode_ver'): bool {
        $tipos = ['pode_ver', 'pode_criar', 'pode_editar', 'pode_excluir'];
        if (!in_array($tipo, $tipos, true)) {
            return false;
        }

        $stmt = $this->db->prepare("
            SELECT p.`$tipo`
            FROM perfil_modulo_permissao p
            INNER JOIN modulos m ON m.id = p.modulo_id
            WHERE p.perfil_id = ? AND m.nome = ?
        ");
        $stmt->execute([$perfilId, $moduloNome]);
        $resultado = $stmt->fetchColumn();
        return (bool) $resultado;
    }

    /**
     * Salva (upsert) permissões de um perfil para um módulo.
     */
    public function salvar(int $perfilId, int $moduloId, array $permissoes): bool {
        $stmt = $this->db->prepare('
            INSERT INTO perfil_modulo_permissao
                (perfil_id, modulo_id, pode_ver, pode_criar, pode_editar, pode_excluir)
            VALUES
                (:perfil_id, :modulo_id, :pode_ver, :pode_criar, :pode_editar, :pode_excluir)
            ON DUPLICATE KEY UPDATE
                pode_ver     = VALUES(pode_ver),
                pode_criar   = VALUES(pode_criar),
                pode_editar  = VALUES(pode_editar),
                pode_excluir = VALUES(pode_excluir)
        ');
        return $stmt->execute([
            ':perfil_id'    => $perfilId,
            ':modulo_id'    => $moduloId,
            ':pode_ver'     => (int) ($permissoes['pode_ver']     ?? 0),
            ':pode_criar'   => (int) ($permissoes['pode_criar']   ?? 0),
            ':pode_editar'  => (int) ($permissoes['pode_editar']  ?? 0),
            ':pode_excluir' => (int) ($permissoes['pode_excluir'] ?? 0),
        ]);
    }

    /**
     * Retorna matriz completa: todos os perfis × todos os módulos.
     * Útil para renderizar a tela de gestão de permissões.
     */
    public function matrizCompleta(): array {
        return $this->db->query('
            SELECT
                p.id   AS perfil_id,   p.nome AS perfil_nome,
                m.id   AS modulo_id,   m.nome AS modulo_nome, m.ordem,
                COALESCE(pmp.pode_ver,     0) AS pode_ver,
                COALESCE(pmp.pode_criar,   0) AS pode_criar,
                COALESCE(pmp.pode_editar,  0) AS pode_editar,
                COALESCE(pmp.pode_excluir, 0) AS pode_excluir
            FROM perfis p
            CROSS JOIN modulos m
            LEFT JOIN perfil_modulo_permissao pmp
                ON pmp.perfil_id = p.id AND pmp.modulo_id = m.id
            WHERE p.ativo = 1 AND m.ativo = 1
            ORDER BY p.nome, m.ordem
        ')->fetchAll();
    }
}
