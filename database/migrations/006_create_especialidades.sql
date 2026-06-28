-- ============================================================
-- OpusMed — Tabela de especialidades médicas
-- ============================================================

CREATE TABLE IF NOT EXISTS especialidades (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    nome        VARCHAR(120)  NOT NULL,
    codigo_cbos VARCHAR(10)   NULL COMMENT 'Código CBO-S da especialidade',
    descricao   TEXT          NULL,
    ativo       TINYINT(1)    NOT NULL DEFAULT 1,
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_especialidades_nome (nome),
    KEY idx_especialidades_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
