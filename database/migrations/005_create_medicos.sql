-- ============================================================
-- OpusMed — Tabela de médicos
-- ============================================================

CREATE TABLE IF NOT EXISTS medicos (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    nome            VARCHAR(150)    NOT NULL,
    cpf             CHAR(11)        NULL,
    crm             VARCHAR(20)     NOT NULL,
    crm_uf          CHAR(2)         NOT NULL,
    especialidade   VARCHAR(100)    NULL,
    rqe             VARCHAR(30)     NULL,
    email           VARCHAR(150)    NULL,
    telefone        VARCHAR(20)     NULL,
    tipo_vinculo    ENUM('clt','pj','autonomo','cooperado','voluntario','residente','outros') NOT NULL DEFAULT 'autonomo',
    setor_id        INT UNSIGNED    NULL,
    status          ENUM('ativo','inativo','ferias','afastado','desligado') NOT NULL DEFAULT 'ativo',
    ativo           TINYINT(1)      NOT NULL DEFAULT 1,
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_medicos_crm_uf (crm, crm_uf),
    UNIQUE KEY uq_medicos_cpf    (cpf),
    KEY idx_medicos_setor        (setor_id),
    KEY idx_medicos_status       (status),

    CONSTRAINT fk_medicos_setor FOREIGN KEY (setor_id)
        REFERENCES setores (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
