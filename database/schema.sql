-- Base de datos de MacroFit (restaurante ficticio, proyecto de demostración)
-- Compatible con MySQL 8.0.16+ y MariaDB 10.4+

CREATE DATABASE IF NOT EXISTS macrofit
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE macrofit;

CREATE TABLE IF NOT EXISTS reservas (
    id           INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    nombre       VARCHAR(100)     NOT NULL,
    email        VARCHAR(150)     NOT NULL,
    telefono     VARCHAR(20)      NOT NULL,
    comensales   TINYINT UNSIGNED NOT NULL,
    fecha        DATE             NOT NULL,
    hora         TIME             NOT NULL,
    comentarios  VARCHAR(500)     NULL,
    -- Una solicitud nace "pendiente"; el restaurante la confirmaría o cancelaría.
    estado       ENUM('pendiente', 'confirmada', 'cancelada') NOT NULL DEFAULT 'pendiente',
    creada_en    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_reservas_fecha_hora (fecha, hora),
    CONSTRAINT chk_reservas_comensales CHECK (comensales BETWEEN 1 AND 10)
) ENGINE=InnoDB;

-- Usuario de la aplicación con los permisos mínimos que necesita.
-- Cambia la contraseña y usa la misma en config/config.php.
CREATE USER IF NOT EXISTS 'macrofit_app'@'localhost' IDENTIFIED BY 'CAMBIA_ESTA_CONTRASENA';
GRANT SELECT, INSERT ON macrofit.reservas TO 'macrofit_app'@'localhost';
