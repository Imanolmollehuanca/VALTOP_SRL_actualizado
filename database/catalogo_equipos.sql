-- =====================================================
-- Tabla: catalogo_equipos
-- Módulo: Equipos (Mejora - Catálogo)
-- Descripción: Catálogo maestro de equipos físicos que
--              posee la empresa (GPS, Estaciones Totales,
--              Niveles, RPAS, otros). Cada fila representa
--              una unidad física real, incluso si comparte
--              nombre/modelo con otra unidad.
-- =====================================================

CREATE TABLE catalogo_equipos (

    id_catalogo_equipo INT AUTO_INCREMENT PRIMARY KEY,

    -- Categoría del equipo (GPS, E.T., NIVEL, RPAS, OTROS)
    tipo_equipo VARCHAR(50) NOT NULL,

    -- Nombre/modelo/marca del equipo específico
    equipo_marca VARCHAR(150) NOT NULL,

    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =====================================================
-- Carga inicial: catálogo real de equipos de VALTOP SRL
-- =====================================================

-- GPS
INSERT INTO catalogo_equipos (tipo_equipo, equipo_marca) VALUES
('GPS', 'ROVER TRIMBLE R4-2'),
('GPS', 'BASE TRIMBLE R8-1'),
('GPS', 'ROVER TRIMBLE R8S'),
('GPS', 'BASE TRIMBLE R8S'),
('GPS', 'ROVER TRIMBLE R10-2'),
('GPS', 'ROVER TRIMBLE R10-2'),
('GPS', 'ROVER TRIMBLE R12i LT'),
('GPS', 'ROVER TRIMBLE R980'),
('GPS', 'ROVER TRIMBLE R780'),
('GPS', 'BASE SOUTH G7 (Ingrid)'),
('GPS', 'ROVER SOUTH G7 (Ingrid)'),
('GPS', 'BASE SOUTH G7 (Edgar)'),
('GPS', 'ROVER SOUTH G7 (Edgar)'),
('GPS', 'BASE CHC NAV i93'),
('GPS', 'ROVER CHC NAV i93'),
('GPS', 'RADIO EXTERNA TDL 450');

-- E.T. (Estación Total)
INSERT INTO catalogo_equipos (tipo_equipo, equipo_marca) VALUES
('E.T.', 'LEICA TS01 5"'),
('E.T.', 'LEICA TS01 5"'),
('E.T.', 'LEICA TS03 5"'),
('E.T.', 'LEICA TS06 1"'),
('E.T.', 'LEICA TS06 1" -MAQ'),
('E.T.', 'LEICA TS06 1" - VICDIA'),
('E.T.', 'LEICA TS07 1" R500'),
('E.T.', 'LEICA TS07 1" R1000'),
('E.T.', 'LEICA TS07 1" R500'),
('E.T.', 'LEICA TS07 1" R500 - EV'),
('E.T.', 'LEICA TS07 1" R500'),
('E.T.', 'LEICA TS07 1" R500'),
('E.T.', 'LEICA TS07 1" R500'),
('E.T.', 'LEICA TS07 1" R500'),
('E.T.', 'LEICA TS07 1" R500'),
('E.T.', 'CHC NAV CTS 112 R4');

-- RPAS
INSERT INTO catalogo_equipos (tipo_equipo, equipo_marca) VALUES
('RPAS', 'PHANTON 4PRO');

-- NIVEL
INSERT INTO catalogo_equipos (tipo_equipo, equipo_marca) VALUES
('NIVEL', 'Topcon ATB4'),
('NIVEL', 'Topcon ATB2'),
('NIVEL', 'Topcon ATB4A'),
('NIVEL', 'Leica NA324'),
('NIVEL', 'Leica NA324'),
('NIVEL', 'Leica NA324');

-- OTROS
INSERT INTO catalogo_equipos (tipo_equipo, equipo_marca) VALUES
('OTROS', 'LEICA TS01-5"'),
('OTROS', 'cargador LEICA GKL 311 Original'),
('OTROS', 'Miniprisma Leica c/bastón');