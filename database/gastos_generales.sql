-- =====================================================
-- Tabla: gastos_generales
-- Módulo: Gastos Generales (Fase 7)
-- Descripción: Registra los gastos administrativos de la
--              empresa (agua, luz, alquiler, honorarios, etc.).
--              Es un módulo totalmente independiente: NO se
--              relaciona con 'trabajos' ni con ningún otro
--              módulo. El concepto es texto libre, NO un
--              catálogo, porque la empresa puede registrar
--              cualquier gasto sin restricción de opciones.
-- =====================================================

CREATE TABLE gastos_generales (

    id_gasto INT AUTO_INCREMENT PRIMARY KEY,

    -- Texto libre, escrito por el usuario (Agua, Luz, Honorarios...)
    concepto VARCHAR(150) NOT NULL,

    fecha DATE NOT NULL,

    monto DECIMAL(10,2) NOT NULL DEFAULT 0.00,

    observacion VARCHAR(255) NULL,

    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;