-- =====================================================
-- Tabla: clientes
-- Módulo: Catálogo simple de Clientes (no es un módulo aparte)
-- Descripción: Valtop SRL no maneja un módulo de clientes;
--              antes anotaban el nombre en un Excel cuando
--              aparecía un cliente nuevo. Esta tabla solo
--              reemplaza ese Excel para poder alimentar el
--              selector "Cliente" del formulario de Trabajos.
-- =====================================================

CREATE TABLE clientes (
    -- Identificador interno autoincremental
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,

    -- Nombre o razón social del cliente (único dato obligatorio)
    nombre_cliente VARCHAR(150) NOT NULL,

    -- Datos opcionales, tal como se conversó con la empresa
    ruc VARCHAR(20) NULL,
    telefono VARCHAR(20) NULL,
    correo VARCHAR(150) NULL,
    observaciones VARCHAR(255) NULL,

    -- Auditoría básica
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
