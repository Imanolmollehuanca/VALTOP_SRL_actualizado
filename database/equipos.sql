-- =====================================================
-- Tabla: equipos
-- Módulo: Equipos (Fase 2)
-- Descripción: Registro general de los equipos utilizados
--              en cada trabajo. Cada fila es un "préstamo"
--              o uso de equipos asociado a un trabajo puntual.
--
--              Relación con 'trabajos': 1 trabajo -> N registros
--              de equipos, mediante id_trabajo (FK).
-- =====================================================

CREATE TABLE equipos (

    ALTER TABLE equipos
        ADD COLUMN telefono_contacto VARCHAR(30) NULL AFTER contacto;
    -- Identificador interno autoincremental
    id_equipo INT AUTO_INCREMENT PRIMARY KEY,

    -- Relación con la tabla 'trabajos' (a qué trabajo pertenece este registro)
    id_trabajo INT NOT NULL,

    -- Cantidad de equipos utilizados en este trabajo (ej: 8, 5, 12...)
    cantidad_equipos INT NOT NULL DEFAULT 1,

    -- Persona de contacto para la entrega/recojo de los equipos
    contacto VARCHAR(150) NOT NULL,

    -- Persona encargada del registro. Por ahora siempre "Ingrid Castillo",
    -- pero se guarda como texto editable para no depender de un módulo
    -- de Usuarios que todavía no existe (mismo criterio que id_responsable
    -- en la tabla 'trabajos').
    encargado VARCHAR(100) NOT NULL DEFAULT 'Ingrid Castillo',

    -- Fecha y hora en que los equipos salieron
    fecha_salida DATE NOT NULL,
    hora_salida TIME NOT NULL,

    -- Fecha y hora en que los equipos regresaron.
    -- Pueden quedar vacías mientras el estado sea 'Pendiente'.
    fecha_regreso DATE NULL,
    hora_regreso TIME NULL,

    -- Tiempo total de uso, en texto libre (ej: "3 días", "5 horas").
    -- No se calcula automáticamente porque fecha_regreso puede no existir aún.
    tiempo VARCHAR(50) NULL,

    -- Montos en soles (S/). DECIMAL(10,2) por precisión, igual que precio_neto.
    costo   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    pago_1  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    pago_2  DECIMAL(10,2) NOT NULL DEFAULT 0.00,

    -- Estado del préstamo de equipos
    estado ENUM(
        'Pendiente',
        'Devuelto',
        'Cambio de equipo'
    ) NOT NULL DEFAULT 'Pendiente',

    -- Auditoría básica
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Relación real con 'trabajos'. A diferencia de 'clientes' en trabajos.sql,
    -- aquí SÍ se agrega la FK porque 'equipos' es una tabla nueva, sin datos
    -- previos que puedan romperse.
    CONSTRAINT fk_equipos_trabajo
        FOREIGN KEY (id_trabajo) REFERENCES trabajos(id_trabajo)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;