-- =====================================================
-- Tabla: costo_financiero
-- Módulo: Costo Financiero (Fase 8)
-- Descripción: Guarda ÚNICAMENTE los datos que un humano
--              decide manualmente para cada trabajo: fecha
--              de factura, fecha de cobro, y el porcentaje
--              financiero aplicado.
--
--              NO guarda Capital Invertido, NO guarda Días,
--              NO guarda Costo Financiero: esos tres valores
--              se calculan siempre al consultar (sumando
--              Personal + Equipos + Viáticos + Materiales +
--              Gastos Generales, y aplicando la fórmula
--              centralizada), para que nunca queden
--              desincronizados con los módulos de origen.
--
--              Un trabajo puede o no tener todavía un registro
--              aquí (antes de presionar "Recalcular" por primera
--              vez); por eso NO se crea automáticamente al crear
--              un trabajo en el módulo Trabajos.
-- =====================================================

CREATE TABLE costo_financiero (

    id_costo_financiero INT AUTO_INCREMENT PRIMARY KEY,

    -- Un trabajo tiene como máximo un registro de costo financiero
    id_trabajo INT NOT NULL UNIQUE,

    -- Fecha en la que se emitió la factura al cliente
    fecha_factura DATE NULL,

    -- Fecha en la que el cliente efectivamente pagó
    fecha_cobro DATE NULL,

    -- Porcentaje financiero aplicado sobre el Capital Invertido.
    -- Ej: 2.00 significa 2%.
    porcentaje_financiero DECIMAL(5,2) NOT NULL DEFAULT 0.00,

    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_costo_financiero_trabajo
        FOREIGN KEY (id_trabajo) REFERENCES trabajos(id_trabajo)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;