-- =====================================================
-- Tabla: equipos_cambios
-- Módulo: Equipos (Mejora - Historial de cambios)
-- Descripción: Registro permanente (append-only) de cada
--              cambio de equipo realizado sobre un registro
--              de "equipos": qué equipo se retiró, cuánto,
--              qué equipo entró, cuánto, motivo, fecha y
--              observación. Nunca se edita ni se borra una
--              fila existente: cada cambio queda guardado
--              para siempre como historial.
--
--              El estado "actual" de equipos sigue viviendo
--              en equipos_detalle; esta tabla es solo el
--              registro histórico de cómo se llegó ahí.
-- =====================================================

CREATE TABLE equipos_cambios (

    id_cambio INT AUTO_INCREMENT PRIMARY KEY,

    -- Registro general de equipos al que pertenece este cambio
    id_equipo INT NOT NULL,

    -- Equipo específico que se retiró y cuánto
    id_catalogo_equipo_retirado INT NOT NULL,
    cantidad_retirada INT NOT NULL,

    -- Equipo específico que entró y cuánto
    id_catalogo_equipo_nuevo INT NOT NULL,
    cantidad_nueva INT NOT NULL,

    -- Motivo del cambio. VARCHAR (no ENUM) para poder ampliar
    -- la lista de motivos más adelante sin alterar la tabla.
    -- Validado en la aplicación (EquipoController::MOTIVOS_VALIDOS).
    motivo VARCHAR(100) NOT NULL,

    -- Fecha en la que ocurrió el cambio (puede diferir de
    -- la fecha en que se registró en el sistema)
    fecha_cambio DATE NOT NULL,

    -- Observación opcional
    observacion TEXT NULL,

    -- Usuario que realizó el cambio. El proyecto todavía no
    -- tiene sesión de usuario implementada, así que queda
    -- como texto libre y opcional (NULL permitido).
    usuario VARCHAR(150) NULL,

    -- Auditoría: cuándo quedó registrado en el sistema
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_cambio_equipo
        FOREIGN KEY (id_equipo) REFERENCES equipos(id_equipo)
        ON DELETE CASCADE,

    CONSTRAINT fk_cambio_catalogo_retirado
        FOREIGN KEY (id_catalogo_equipo_retirado) REFERENCES catalogo_equipos(id_catalogo_equipo),

    CONSTRAINT fk_cambio_catalogo_nuevo
        FOREIGN KEY (id_catalogo_equipo_nuevo) REFERENCES catalogo_equipos(id_catalogo_equipo)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;