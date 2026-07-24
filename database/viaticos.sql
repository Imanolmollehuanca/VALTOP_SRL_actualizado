-- =====================================================
-- Tabla: viaticos
-- Módulo: Viáticos (Fase 5)
-- Descripción: Registra los gastos de viáticos que la
--              empresa realiza para un trabajo determinado
--              (alimentación, hospedaje, movilidad, etc.).
--              Es un módulo independiente: no vive dentro
--              del Expediente de un Trabajo. Esta información
--              será usada más adelante en la Valorización
--              (Fase 9) para calcular el costo real del trabajo.
-- =====================================================

CREATE TABLE viaticos (

    id_viatico INT AUTO_INCREMENT PRIMARY KEY,

    -- Relación con el trabajo al que pertenece este gasto
    id_trabajo INT NOT NULL,

    -- Fecha en la que se realizó el gasto
    fecha DATE NOT NULL,

    -- Tipo de gasto
    concepto ENUM(
        'Alimentación',
        'Hospedaje',
        'Agua',
        'Movilidad',
        'Peajes',
        'Combustible',
        'Pasajes',
        'Otros'
    ) NOT NULL,

    -- Detalle específico del gasto (ej: "Almuerzo cuadrilla", "Hotel Juliaca")
    descripcion VARCHAR(255) NOT NULL,

    -- Monto del gasto, en soles (S/).
    monto DECIMAL(10,2) NOT NULL DEFAULT 0.00,

    -- Estado del pago de este gasto
    estado ENUM(
        'Pendiente',
        'Pagado',
        'Anulado'
    ) NOT NULL DEFAULT 'Pendiente',

    -- Observaciones opcionales
    observaciones TEXT NULL,

    -- Auditoría básica
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_viaticos_trabajo
        FOREIGN KEY (id_trabajo) REFERENCES trabajos(id_trabajo)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
