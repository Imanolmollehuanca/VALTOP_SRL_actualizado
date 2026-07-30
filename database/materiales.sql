-- =====================================================
-- Tablas: materiales (catálogo) y trabajo_materiales (detalle)
-- Módulo: Materiales (Fase 6)
-- Descripción: 'materiales' es un catálogo simple de insumos
--              (Cemento, Arena, Pintura, etc.), que crece con
--              el tiempo igual que 'clientes': el usuario escribe
--              el nombre libremente y, si no existe, se crea al
--              guardar. 'trabajo_materiales' es el detalle real:
--              qué material, cuánto, a qué precio, y para qué
--              trabajo (id_trabajo). El subtotal y el costo total
--              NUNCA se guardan como columna: siempre se calculan
--              con SQL (cantidad * precio_unitario) al consultar,
--              para que nunca queden desincronizados.
--              Es un módulo independiente: no vive dentro del
--              Expediente de un Trabajo.
-- =====================================================

CREATE TABLE materiales (

    id_material INT AUTO_INCREMENT PRIMARY KEY,

    nombre_material VARCHAR(150) NOT NULL UNIQUE,

    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE trabajo_materiales (

    id_trabajo_material INT AUTO_INCREMENT PRIMARY KEY,

    -- Relación con el trabajo al que pertenece este material usado
    id_trabajo INT NOT NULL,

    -- Relación con el catálogo de materiales
    id_material INT NOT NULL,

    cantidad DECIMAL(10,2) NOT NULL,

    -- Unidad de medida de ESTE registro (Bolsa, Kg, Galón, Unidad...).
    -- Se guarda aquí y no en 'materiales' porque el mismo material
    -- puede comprarse en unidades distintas según el trabajo.
    unidad VARCHAR(30) NOT NULL,

    precio_unitario DECIMAL(10,2) NOT NULL,

    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_trabajo_materiales_trabajo
        FOREIGN KEY (id_trabajo) REFERENCES trabajos(id_trabajo),

    CONSTRAINT fk_trabajo_materiales_material
        FOREIGN KEY (id_material) REFERENCES materiales(id_material)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;