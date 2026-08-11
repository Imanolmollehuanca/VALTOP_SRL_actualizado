-- =====================================================
-- Tabla: tareo
-- Módulo: Tareo (Fase 4)
-- Descripción: Registra la asistencia/actividad diaria del
--              personal en un trabajo determinado. No crea
--              trabajadores ni trabajos nuevos: solo une
--              ambos catálogos existentes mediante registros
--              diarios (Trabajo -> Tareo -> Personal).
--              Es un módulo independiente: no vive dentro
--              del Expediente de un Trabajo.
-- =====================================================

CREATE TABLE tareo (

    id_tareo INT AUTO_INCREMENT PRIMARY KEY,

    -- Relación con el trabajo en el que se registra la actividad
    id_trabajo INT NOT NULL,

    -- Relación con el trabajador del módulo Personal
    id_personal INT NOT NULL,

    -- Fecha del registro (día específico de asistencia)
    fecha DATE NOT NULL,

    -- Actividad realizada ese día. Solo una opción por registro,
    -- reforzado a nivel de aplicación (select único) y de BD (ENUM).
    actividad ENUM(
        'Campo',
        'Dibujo',
        'Falto',
        'Vacaciones'
    ) NOT NULL,

    -- Lugar donde se realizó la actividad ese día (texto libre)
    lugar VARCHAR(150) NOT NULL,

    -- Observaciones opcionales del día
    observaciones TEXT NULL,

    -- Auditoría básica
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_tareo_trabajo
        FOREIGN KEY (id_trabajo) REFERENCES trabajos(id_trabajo),

    CONSTRAINT fk_tareo_personal
        FOREIGN KEY (id_personal) REFERENCES personal(id_personal)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;