-- ============================================================
--  OpusMed - Migração 003: Pacientes
--  Execute no banco: apassa73_opusmed
-- ============================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `pacientes` (
    `id`                        INT UNSIGNED    NOT NULL AUTO_INCREMENT,

    -- 1. Identificação
    `prontuario`                VARCHAR(30)          NULL COMMENT 'Número interno/prontuário',
    `nome`                      VARCHAR(200)    NOT NULL,
    `nome_social`               VARCHAR(200)         NULL,
    `data_nascimento`           DATE                 NULL,
    `sexo_biologico`            ENUM('M','F','I')    NULL COMMENT 'M=Masculino F=Feminino I=Intersexo',
    `genero`                    VARCHAR(60)          NULL,
    `cpf`                       CHAR(11)             NULL,
    `rg`                        VARCHAR(20)          NULL,
    `rg_orgao`                  VARCHAR(20)          NULL,
    `cns`                       VARCHAR(15)          NULL COMMENT 'Cartão Nacional de Saúde',
    `nome_mae`                  VARCHAR(200)         NULL,
    `nome_pai`                  VARCHAR(200)         NULL,
    `estado_civil`              ENUM('solteiro','casado','divorciado','viuvo','uniao_estavel','outros') NULL,
    `nacionalidade`             VARCHAR(80)          NULL DEFAULT 'Brasileira',
    `naturalidade`              VARCHAR(100)         NULL,
    `foto`                      VARCHAR(255)         NULL,

    -- 2. Contato
    `telefone`                  VARCHAR(20)          NULL,
    `telefone2`                 VARCHAR(20)          NULL,
    `whatsapp`                  VARCHAR(20)          NULL,
    `email`                     VARCHAR(200)         NULL,
    `preferencia_contato`       ENUM('telefone','whatsapp','email','sms') NOT NULL DEFAULT 'telefone',
    `aceite_mensagens`          TINYINT(1)      NOT NULL DEFAULT 0,

    -- 3. Endereço
    `cep`                       CHAR(8)              NULL,
    `logradouro`                VARCHAR(200)         NULL,
    `numero`                    VARCHAR(20)          NULL,
    `complemento`               VARCHAR(100)         NULL,
    `bairro`                    VARCHAR(100)         NULL,
    `cidade`                    VARCHAR(100)         NULL,
    `estado_uf`                 CHAR(2)              NULL,
    `referencia`                VARCHAR(200)         NULL,

    -- 4. Responsável legal
    `resp_nome`                 VARCHAR(200)         NULL,
    `resp_parentesco`           VARCHAR(60)          NULL,
    `resp_cpf`                  CHAR(11)             NULL,
    `resp_telefone`             VARCHAR(20)          NULL,
    `resp_email`                VARCHAR(200)         NULL,
    `resp_observacao`           TEXT                 NULL,

    -- 5. Dados assistenciais
    `alergias`                  TEXT                 NULL,
    `doencas_preexistentes`     TEXT                 NULL,
    `medicamentos_continuos`    TEXT                 NULL,
    `tipo_sanguineo`            ENUM('A+','A-','B+','B-','AB+','AB-','O+','O-') NULL,
    `condicoes_especiais`       TEXT                 NULL,
    `deficiencia`               VARCHAR(200)         NULL,
    `gestante`                  TINYINT(1)      NOT NULL DEFAULT 0,
    `restricao_alimentar`       VARCHAR(200)         NULL,

    -- 6. Convênio
    `convenio_id`               INT UNSIGNED         NULL,
    `convenio_carteirinha`      VARCHAR(60)          NULL,
    `convenio_validade`         DATE                 NULL,
    `convenio_titular`          VARCHAR(200)         NULL,
    `convenio_matricula`        VARCHAR(60)          NULL,
    `convenio_plano`            VARCHAR(100)         NULL,
    `convenio_cod_beneficiario` VARCHAR(60)          NULL,

    -- 7. Administrativo
    `status`                    ENUM('ativo','inativo','obito','transferido') NOT NULL DEFAULT 'ativo',
    `unidade`                   VARCHAR(100)         NULL,
    `origem_cadastro`           ENUM('recepcao','internet','transferencia','outros') NOT NULL DEFAULT 'recepcao',
    `observacoes`               TEXT                 NULL,
    `cadastrado_por`            INT UNSIGNED         NULL,

    -- 8. LGPD
    `lgpd_consentimento`        TINYINT(1)      NOT NULL DEFAULT 0,
    `lgpd_whatsapp`             TINYINT(1)      NOT NULL DEFAULT 0,
    `lgpd_sms`                  TINYINT(1)      NOT NULL DEFAULT 0,
    `lgpd_email_consent`        TINYINT(1)      NOT NULL DEFAULT 0,
    `lgpd_data_aceite`          DATETIME             NULL,
    `lgpd_responsavel_aceite`   VARCHAR(200)         NULL,
    `lgpd_finalidade`           TEXT                 NULL,

    `ativo`                     TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`                DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`                DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_pacientes_prontuario` (`prontuario`),
    UNIQUE KEY `uq_pacientes_cpf`        (`cpf`),
    UNIQUE KEY `uq_pacientes_cns`        (`cns`),
    KEY `idx_pacientes_nome`   (`nome`),
    KEY `idx_pacientes_status` (`status`),
    CONSTRAINT `fk_pac_convenio` FOREIGN KEY (`convenio_id`)   REFERENCES `convenios` (`id`) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT `fk_pac_usuario` FOREIGN KEY (`cadastrado_por`) REFERENCES `usuarios`  (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Garante que o módulo Pacientes existe (já criado pelo seed)
INSERT IGNORE INTO `modulos` (`nome`, `descricao`, `icone`, `rota`, `ordem`)
VALUES ('Pacientes', 'Cadastro e gestão de pacientes', 'fa-user-injured', '/pacientes', 2);

-- Permissão total para Administrador
INSERT INTO `perfil_modulo_permissao` (`perfil_id`, `modulo_id`, `pode_ver`, `pode_criar`, `pode_editar`, `pode_excluir`)
SELECT 1, id, 1, 1, 1, 1 FROM `modulos` WHERE nome = 'Pacientes'
ON DUPLICATE KEY UPDATE pode_ver=1, pode_criar=1, pode_editar=1, pode_excluir=1;
