-- =====================================================
-- Tabla: trabajos
-- Módulo: Trabajos (Fase 1)
-- Descripción: Almacena los trabajos/proyectos gestionados
--              por Valtop SRL, alineado al proceso real
--              validado con los Excel de la empresa.
--              Se relaciona con las tablas externas
--              'clientes' y 'usuarios' solo por ID.
-- =====================================================

CREATE TABLE trabajos (
    -- Identificador interno autoincremental (uso técnico, no visible al usuario)
    id_trabajo INT AUTO_INCREMENT PRIMARY KEY,

    -- Código visible para el usuario, ej: TR-001, TR-002...
    codigo_trabajo VARCHAR(20) NOT NULL UNIQUE,

    -- Fecha en la que se registró el trabajo en el sistema.
    -- No se muestra en el listado principal, pero se conserva
    -- para el Expediente y para auditoría.
    fecha_registro DATE NOT NULL,

    -- Relación con la tabla externa 'clientes'
    id_cliente INT NOT NULL,

    -- Nombre del proyecto asociado al trabajo
    proyecto VARCHAR(150) NOT NULL,

    -- Descripción detallada del trabajo a realizar
    descripcion TEXT NOT NULL,

    -- Lugar donde se ejecuta el trabajo.
    -- No se muestra en el listado principal, pero se conserva
    -- para el Expediente.
    ubicacion VARCHAR(150) NOT NULL,

    -- Relación con la tabla externa 'usuarios' (responsable del proyecto)
    id_responsable INT NOT NULL,

    -- Precio neto del trabajo, en soles (S/).
    -- DECIMAL(10,2): hasta 10 dígitos en total, 2 decimales.
    -- Nunca usar FLOAT/DOUBLE para dinero, por precisión.
    precio_neto DECIMAL(10,2) NOT NULL DEFAULT 0.00,

    -- Fechas estimadas de ejecución del trabajo
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,

    -- Estado actual del trabajo, según el proceso real de la empresa
    estado ENUM(
        'Pendiente',
        'Terminado',
        'Cobrado',
        'Fracaso'
    ) NOT NULL DEFAULT 'Pendiente',

    -- Auditoría básica: cuándo se creó y cuándo se actualizó el registro
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

    -- NOTA: la tabla 'clientes' ya existe (ver database/clientes.sql),
    -- como catálogo simple. La FOREIGN KEY hacia 'clientes' no se agrega
    -- todavía para no romper datos ya cargados en entornos existentes;
    -- puede agregarse más adelante con:
    --
    -- CONSTRAINT fk_trabajos_cliente
    --     FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
    --
    -- La tabla 'usuarios' (responsables) todavía no existe, por lo
    -- que id_responsable se mantiene sin FK hasta la Fase de Personal.

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;