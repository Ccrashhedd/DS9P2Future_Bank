USE p2gestiongeneral;

CREATE TABLE IF NOT EXISTS sesiones_app (
    idSesion BIGINT AUTO_INCREMENT PRIMARY KEY,
    idUsuario BIGINT NOT NULL,
    tokenHash VARCHAR(255) NOT NULL UNIQUE,
    creadoEn DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expiraEn DATETIME NOT NULL,
    revocado TINYINT(1) NOT NULL DEFAULT 0,
    userAgent VARCHAR(255) NULL,
    INDEX idx_sesiones_app_idUsuario (idUsuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

USE p2gestiondocumentos;

DROP PROCEDURE IF EXISTS agregar_indice_documento_postulante;

DELIMITER //
CREATE PROCEDURE agregar_indice_documento_postulante()
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.statistics
        WHERE table_schema = DATABASE()
          AND table_name = 'documento_postulante'
          AND index_name = 'idx_documento_postulante_idPostulante'
    ) THEN
        ALTER TABLE documento_postulante
            ADD INDEX idx_documento_postulante_idPostulante (idPostulante);
    END IF;
END//
DELIMITER ;

CALL agregar_indice_documento_postulante();
DROP PROCEDURE agregar_indice_documento_postulante;
