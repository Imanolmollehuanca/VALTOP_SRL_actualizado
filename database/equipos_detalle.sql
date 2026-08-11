-- =====================================================
-- Tabla: equipos_detalle
-- Módulo: Equipos (Mejora - Detalle relacional)
-- Descripción: Tabla intermedia que relaciona un registro
--              de "equipos" (salida/préstamo general) con
--              los equipos específicos del catálogo que se
--              usaron en ese registro, junto con la cantidad
--              de cada uno.
--              Reemplaza el campo suelto "cantidad_equipos"
--              como fuente de verdad: cantidad_equipos pasa
--              a calcularse como SUM(cantidad) de esta tabla.
-- =====================================================

CREATE TABLE equipos_detalle (

    id_equipo_detalle INT AUTO_INCREMENT PRIMARY KEY,

    -- Relación con el registro padre (préstamo/salida de equipos)
    id_equipo INT NOT NULL,

    -- Relación con el catálogo: qué equipo específico se usó
    id_catalogo_equipo INT NOT NULL,

    -- Cantidad de ese equipo específico en este registro
    cantidad INT NOT NULL DEFAULT 1,

    CONSTRAINT fk_detalle_equipo
        FOREIGN KEY (id_equipo) REFERENCES equipos(id_equipo)
        ON DELETE CASCADE,

    CONSTRAINT fk_detalle_catalogo
        FOREIGN KEY (id_catalogo_equipo) REFERENCES catalogo_equipos(id_catalogo_equipo)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;