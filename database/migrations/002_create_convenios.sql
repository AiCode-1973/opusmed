-- ============================================================
--  OpusMed - Migração 002: Convênios
--  Execute no banco: apassa73_opusmed
-- ============================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `convenios` (
    `id`               INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `nome`             VARCHAR(150)    NOT NULL,
    `codigo_ans`       VARCHAR(20)          NULL COMMENT 'Registro na ANS',
    `cnpj`             CHAR(14)             NULL,
    `telefone`         VARCHAR(20)          NULL,
    `email`            VARCHAR(200)         NULL,
    `site`             VARCHAR(255)         NULL,
    `endereco`         VARCHAR(255)         NULL,
    `tipo`             ENUM('plano_saude','sus','particular','outros') NOT NULL DEFAULT 'plano_saude',
    `carencia_dias`    SMALLINT UNSIGNED    NULL DEFAULT 0 COMMENT 'Carência em dias',
    `observacoes`      TEXT                 NULL,
    `ativo`            TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_convenios_nome` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Adiciona módulo Convênios ao sistema (se não existir)
INSERT IGNORE INTO `modulos` (`nome`, `descricao`, `icone`, `rota`, `ordem`) VALUES
('Convênios', 'Gestão de convênios e planos de saúde', 'fa-id-card', '/convenios', 12);

-- Dá acesso total ao Administrador no novo módulo
INSERT INTO `perfil_modulo_permissao` (`perfil_id`, `modulo_id`, `pode_ver`, `pode_criar`, `pode_editar`, `pode_excluir`)
SELECT 1, id, 1, 1, 1, 1 FROM `modulos` WHERE nome = 'Convênios'
ON DUPLICATE KEY UPDATE pode_ver=1, pode_criar=1, pode_editar=1, pode_excluir=1;
