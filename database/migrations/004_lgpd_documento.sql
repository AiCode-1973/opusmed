-- ============================================================
--  OpusMed - Migração 004: Adiciona coluna lgpd_documento
-- ============================================================
SET NAMES utf8mb4;

ALTER TABLE `pacientes`
    ADD COLUMN `lgpd_documento` VARCHAR(255) NULL COMMENT 'Nome do arquivo do termo LGPD escaneado'
    AFTER `lgpd_finalidade`;
