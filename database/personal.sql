-- =====================================================
-- Tabla: personal
-- Módulo: Personal (Fase 3)
-- Descripción: Catálogo del personal de la empresa,
--              disponible para ser asignado a los trabajos.
--              Es independiente del módulo Trabajos: no vive
--              dentro del Expediente de un trabajo, tiene su
--              propio listado y su propio formulario.
-- =====================================================

CREATE TABLE personal (

    id_personal INT AUTO_INCREMENT PRIMARY KEY,

    -- Datos personales
    nombre_completo VARCHAR(150) NOT NULL,
    telefono_principal VARCHAR(30) NOT NULL,
    telefono_secundario VARCHAR(30) NULL,
    telefono_contacto VARCHAR(30) NULL,

    -- Datos laborales
    cargo ENUM(
        'Topógrafo','Ayudante','Dibujante','Cadista','Ingeniero',
        'Supervisor','Chofer','Administrativo','Otro'
    ) NOT NULL,

    id_responsable INT NOT NULL,

    estado ENUM('Activo','Inactivo','Vacaciones') NOT NULL DEFAULT 'Activo',

    observaciones TEXT NULL,

    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_personal_responsable
        FOREIGN KEY (id_responsable) REFERENCES usuarios(id_usuario)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;