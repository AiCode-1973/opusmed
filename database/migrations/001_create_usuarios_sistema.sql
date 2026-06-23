-- ============================================================
--  OpusMed - Sistema Hospitalar
--  Migração 001: Módulos, Perfis, Usuários e Permissões
--  Data: 2026-06-23
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- 1. MÓDULOS DO SISTEMA
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `modulos` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `nome`        VARCHAR(100)    NOT NULL,
    `descricao`   VARCHAR(255)        NULL,
    `icone`       VARCHAR(100)        NULL COMMENT 'Classe CSS do ícone (ex: fa-user)',
    `rota`        VARCHAR(150)        NULL COMMENT 'Caminho da URL do módulo',
    `ordem`       TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `ativo`       TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_modulos_nome` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 2. PERFIS DE ACESSO
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `perfis` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `nome`        VARCHAR(100)    NOT NULL,
    `descricao`   VARCHAR(255)        NULL,
    `ativo`       TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_perfis_nome` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 3. PERMISSÕES: PERFIL × MÓDULO
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `perfil_modulo_permissao` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `perfil_id`    INT UNSIGNED NOT NULL,
    `modulo_id`    INT UNSIGNED NOT NULL,
    `pode_ver`     TINYINT(1)   NOT NULL DEFAULT 0,
    `pode_criar`   TINYINT(1)   NOT NULL DEFAULT 0,
    `pode_editar`  TINYINT(1)   NOT NULL DEFAULT 0,
    `pode_excluir` TINYINT(1)   NOT NULL DEFAULT 0,
    `created_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_perfil_modulo` (`perfil_id`, `modulo_id`),
    CONSTRAINT `fk_pmp_perfil` FOREIGN KEY (`perfil_id`) REFERENCES `perfis` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_pmp_modulo` FOREIGN KEY (`modulo_id`) REFERENCES `modulos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 4. USUÁRIOS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
    `id`             INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `perfil_id`      INT UNSIGNED    NOT NULL,
    `nome`           VARCHAR(150)    NOT NULL,
    `email`          VARCHAR(200)    NOT NULL,
    `senha`          VARCHAR(255)    NOT NULL COMMENT 'Hash bcrypt',
    `cpf`            CHAR(11)            NULL,
    `telefone`       VARCHAR(20)         NULL,
    `foto`           VARCHAR(255)        NULL,
    `token_reset`    VARCHAR(255)        NULL COMMENT 'Token para recuperação de senha',
    `token_expira`   DATETIME            NULL,
    `ultimo_acesso`  DATETIME            NULL,
    `ativo`          TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_usuarios_email` (`email`),
    UNIQUE KEY `uq_usuarios_cpf`   (`cpf`),
    CONSTRAINT `fk_usuarios_perfil` FOREIGN KEY (`perfil_id`) REFERENCES `perfis` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  DADOS INICIAIS (SEED)
-- ============================================================

-- Módulos do sistema hospitalar
INSERT INTO `modulos` (`nome`, `descricao`, `icone`, `rota`, `ordem`) VALUES
('Dashboard',       'Painel principal com indicadores',        'fa-tachometer-alt', '/dashboard',       1),
('Pacientes',       'Cadastro e gestão de pacientes',          'fa-user-injured',   '/pacientes',       2),
('Agendamento',     'Agenda de consultas e procedimentos',     'fa-calendar-alt',   '/agendamento',     3),
('Prontuário',      'Prontuário eletrônico do paciente',       'fa-file-medical',   '/prontuario',      4),
('Internação',      'Gestão de leitos e internações',          'fa-bed',            '/internacao',      5),
('Farmácia',        'Controle de estoque e dispensação',       'fa-pills',          '/farmacia',        6),
('Laboratório',     'Solicitação e resultados de exames',      'fa-flask',          '/laboratorio',     7),
('Financeiro',      'Faturamento, cobranças e contas',         'fa-dollar-sign',    '/financeiro',      8),
('Relatórios',      'Relatórios e exportações',                'fa-chart-bar',      '/relatorios',      9),
('Configurações',   'Configurações gerais do sistema',         'fa-cog',            '/configuracoes',  10),
('Usuários',        'Gestão de usuários e perfis de acesso',   'fa-users-cog',      '/usuarios',       11);

-- Perfis de acesso
INSERT INTO `perfis` (`nome`, `descricao`) VALUES
('Administrador',  'Acesso total ao sistema'),
('Médico',         'Acesso a prontuários, agendamento e laboratório'),
('Enfermeiro',     'Acesso a prontuários e internação'),
('Recepcionista',  'Acesso a pacientes e agendamento'),
('Farmacêutico',   'Acesso à farmácia e relatórios'),
('Laboratorista',  'Acesso ao laboratório e relatórios'),
('Financeiro',     'Acesso ao módulo financeiro e relatórios');

-- Permissões: Administrador tem acesso total a todos os módulos
INSERT INTO `perfil_modulo_permissao` (`perfil_id`, `modulo_id`, `pode_ver`, `pode_criar`, `pode_editar`, `pode_excluir`)
SELECT 1, id, 1, 1, 1, 1 FROM `modulos`;

-- Permissões: Médico
INSERT INTO `perfil_modulo_permissao` (`perfil_id`, `modulo_id`, `pode_ver`, `pode_criar`, `pode_editar`, `pode_excluir`)
SELECT 2, id,
    CASE WHEN nome IN ('Dashboard','Pacientes','Agendamento','Prontuário','Internação','Laboratório','Relatórios') THEN 1 ELSE 0 END,
    CASE WHEN nome IN ('Prontuário','Agendamento') THEN 1 ELSE 0 END,
    CASE WHEN nome IN ('Prontuário','Agendamento') THEN 1 ELSE 0 END,
    0
FROM `modulos`;

-- Permissões: Enfermeiro
INSERT INTO `perfil_modulo_permissao` (`perfil_id`, `modulo_id`, `pode_ver`, `pode_criar`, `pode_editar`, `pode_excluir`)
SELECT 3, id,
    CASE WHEN nome IN ('Dashboard','Pacientes','Prontuário','Internação') THEN 1 ELSE 0 END,
    CASE WHEN nome IN ('Internação') THEN 1 ELSE 0 END,
    CASE WHEN nome IN ('Prontuário','Internação') THEN 1 ELSE 0 END,
    0
FROM `modulos`;

-- Permissões: Recepcionista
INSERT INTO `perfil_modulo_permissao` (`perfil_id`, `modulo_id`, `pode_ver`, `pode_criar`, `pode_editar`, `pode_excluir`)
SELECT 4, id,
    CASE WHEN nome IN ('Dashboard','Pacientes','Agendamento') THEN 1 ELSE 0 END,
    CASE WHEN nome IN ('Pacientes','Agendamento') THEN 1 ELSE 0 END,
    CASE WHEN nome IN ('Pacientes','Agendamento') THEN 1 ELSE 0 END,
    0
FROM `modulos`;

-- Permissões: Farmacêutico
INSERT INTO `perfil_modulo_permissao` (`perfil_id`, `modulo_id`, `pode_ver`, `pode_criar`, `pode_editar`, `pode_excluir`)
SELECT 5, id,
    CASE WHEN nome IN ('Dashboard','Farmácia','Relatórios') THEN 1 ELSE 0 END,
    CASE WHEN nome IN ('Farmácia') THEN 1 ELSE 0 END,
    CASE WHEN nome IN ('Farmácia') THEN 1 ELSE 0 END,
    0
FROM `modulos`;

-- Permissões: Laboratorista
INSERT INTO `perfil_modulo_permissao` (`perfil_id`, `modulo_id`, `pode_ver`, `pode_criar`, `pode_editar`, `pode_excluir`)
SELECT 6, id,
    CASE WHEN nome IN ('Dashboard','Laboratório','Relatórios') THEN 1 ELSE 0 END,
    CASE WHEN nome IN ('Laboratório') THEN 1 ELSE 0 END,
    CASE WHEN nome IN ('Laboratório') THEN 1 ELSE 0 END,
    0
FROM `modulos`;

-- Permissões: Financeiro
INSERT INTO `perfil_modulo_permissao` (`perfil_id`, `modulo_id`, `pode_ver`, `pode_criar`, `pode_editar`, `pode_excluir`)
SELECT 7, id,
    CASE WHEN nome IN ('Dashboard','Financeiro','Relatórios') THEN 1 ELSE 0 END,
    CASE WHEN nome IN ('Financeiro') THEN 1 ELSE 0 END,
    CASE WHEN nome IN ('Financeiro') THEN 1 ELSE 0 END,
    0
FROM `modulos`;

-- Usuário administrador padrão (senha: Admin@2026 — TROQUE IMEDIATAMENTE)
-- Hash gerado com password_hash('Admin@2026', PASSWORD_BCRYPT)
INSERT INTO `usuarios` (`perfil_id`, `nome`, `email`, `senha`) VALUES
(1, 'Administrador', 'admin@opusmed.com.br', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

SET FOREIGN_KEY_CHECKS = 1;
