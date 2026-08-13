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
-- Migración: agregar SERIE al catálogo de equipos
-- Módulo: Equipos (Mejora - Serie por equipo físico)
-- -----------------------------------------------------
-- La serie identifica la unidad física real dentro del
-- catálogo. Pertenece a catalogo_equipos, no a
-- equipos_detalle ni a equipos_cambios: esas tablas ya
-- referencian el equipo específico mediante
-- id_catalogo_equipo, así que basta con este cambio para
-- que toda la aplicación tenga acceso a la serie correcta,
-- sin romper ninguna relación existente.
-- =====================================================

ALTER TABLE catalogo_equipos
    ADD COLUMN serie VARCHAR(100) NULL AFTER equipo_marca;

-- -----------------------------------------------------
-- Corrección de nombre: "ROVER TRIMBLE R4-2" -> "ROVERTRIMBLE R4-2"
-- (según catálogo real de la empresa)
-- -----------------------------------------------------
UPDATE catalogo_equipos SET equipo_marca = 'ROVERTRIMBLE R4-2'
WHERE id_catalogo_equipo = 1;

-- -----------------------------------------------------
-- Corrección de categoría: "OTROS" -> "Manuscrito"
-- (según catálogo real de la empresa)
-- -----------------------------------------------------
UPDATE catalogo_equipos SET tipo_equipo = 'Manuscrito'
WHERE tipo_equipo = 'OTROS';

-- -----------------------------------------------------
-- Carga de series por id_catalogo_equipo
-- (el orden de los IDs corresponde exactamente al orden
-- de inserción original del archivo catalogo_equipos.sql,
-- incluyendo los equipos duplicados por nombre)
-- -----------------------------------------------------

-- GPS (ids 1-16)
UPDATE catalogo_equipos SET serie = '5316434639'          WHERE id_catalogo_equipo = 1;  -- ROVERTRIMBLE R4-2
UPDATE catalogo_equipos SET serie = '4538158073'          WHERE id_catalogo_equipo = 2;  -- BASE TRIMBLE R8-1
UPDATE catalogo_equipos SET serie = '5813R00055'          WHERE id_catalogo_equipo = 3;  -- ROVER TRIMBLE R8S
UPDATE catalogo_equipos SET serie = '5731R01966'          WHERE id_catalogo_equipo = 4;  -- BASE TRIMBLE R8S
UPDATE catalogo_equipos SET serie = '6012F00172'          WHERE id_catalogo_equipo = 5;  -- ROVER TRIMBLE R10-2 (1)
UPDATE catalogo_equipos SET serie = '6012F00180'          WHERE id_catalogo_equipo = 6;  -- ROVER TRIMBLE R10-2 (2)
UPDATE catalogo_equipos SET serie = '6210F00596'          WHERE id_catalogo_equipo = 7;  -- ROVER TRIMBLE R12i LT
UPDATE catalogo_equipos SET serie = '6423738366'          WHERE id_catalogo_equipo = 8;  -- ROVER TRIMBLE R980
UPDATE catalogo_equipos SET serie = '6228FO0538'          WHERE id_catalogo_equipo = 9;  -- ROVER TRIMBLE R780
UPDATE catalogo_equipos SET serie = 'S914CB148657851PKA'  WHERE id_catalogo_equipo = 10; -- BASE SOUTH G7 (Ingrid)
UPDATE catalogo_equipos SET serie = 'S914CB148657852PKA'  WHERE id_catalogo_equipo = 11; -- ROVER SOUTH G7 (Ingrid)
UPDATE catalogo_equipos SET serie = 'S914D4148677140PKA'  WHERE id_catalogo_equipo = 12; -- BASE SOUTH G7 (Edgar)
UPDATE catalogo_equipos SET serie = 'S914D4148677158PKA'  WHERE id_catalogo_equipo = 13; -- ROVER SOUTH G7 (Edgar)
UPDATE catalogo_equipos SET serie = '4147376'             WHERE id_catalogo_equipo = 14; -- BASE CHC NAV i93
UPDATE catalogo_equipos SET serie = '4147377'             WHERE id_catalogo_equipo = 15; -- ROVER CHC NAV i93
-- id 16 (RADIO EXTERNA TDL 450) se deja sin serie a propósito: no la tiene en el catálogo real.

-- E.T. (ids 17-32)
UPDATE catalogo_equipos SET serie = '2071285'  WHERE id_catalogo_equipo = 17; -- LEICA TS01 5" (1)
UPDATE catalogo_equipos SET serie = '2071580'  WHERE id_catalogo_equipo = 18; -- LEICA TS01 5" (2)
UPDATE catalogo_equipos SET serie = '3309537'  WHERE id_catalogo_equipo = 19; -- LEICA TS03 5"
UPDATE catalogo_equipos SET serie = '1378447'  WHERE id_catalogo_equipo = 20; -- LEICA TS06 1"
UPDATE catalogo_equipos SET serie = '1401366'  WHERE id_catalogo_equipo = 21; -- LEICA TS06 1" -MAQ
UPDATE catalogo_equipos SET serie = '1381250'  WHERE id_catalogo_equipo = 22; -- LEICA TS06 1" - VICDIA
UPDATE catalogo_equipos SET serie = '3320929'  WHERE id_catalogo_equipo = 23; -- LEICA TS07 1" R500 (1)
UPDATE catalogo_equipos SET serie = '3315847'  WHERE id_catalogo_equipo = 24; -- LEICA TS07 1" R1000
UPDATE catalogo_equipos SET serie = '3339043'  WHERE id_catalogo_equipo = 25; -- LEICA TS07 1" R500 (2)
UPDATE catalogo_equipos SET serie = '3338989'  WHERE id_catalogo_equipo = 26; -- LEICA TS07 1" R500 - EV
UPDATE catalogo_equipos SET serie = '3339153'  WHERE id_catalogo_equipo = 27; -- LEICA TS07 1" R500 (3)
UPDATE catalogo_equipos SET serie = '3354263'  WHERE id_catalogo_equipo = 28; -- LEICA TS07 1" R500 (4)
UPDATE catalogo_equipos SET serie = '3352826'  WHERE id_catalogo_equipo = 29; -- LEICA TS07 1" R500 (5)
UPDATE catalogo_equipos SET serie = '3352545'  WHERE id_catalogo_equipo = 30; -- LEICA TS07 1" R500 (6)
UPDATE catalogo_equipos SET serie = '3358262'  WHERE id_catalogo_equipo = 31; -- LEICA TS07 1" R500 (7)
UPDATE catalogo_equipos SET serie = 'H04532'   WHERE id_catalogo_equipo = 32; -- CHC NAV CTS 112 R4

-- RPAS (id 33)
UPDATE catalogo_equipos SET serie = '11UCF9K0A50185' WHERE id_catalogo_equipo = 33; -- PHANTON 4PRO

-- NIVEL (ids 34-39)
UPDATE catalogo_equipos SET serie = 'X57477'         WHERE id_catalogo_equipo = 34; -- Topcon ATB4
UPDATE catalogo_equipos SET serie = 'QQ2927'         WHERE id_catalogo_equipo = 35; -- Topcon ATB2
UPDATE catalogo_equipos SET serie = 'WP178184'       WHERE id_catalogo_equipo = 36; -- Topcon ATB4A
UPDATE catalogo_equipos SET serie = '946324339569'   WHERE id_catalogo_equipo = 37; -- Leica NA324 (1)
UPDATE catalogo_equipos SET serie = '946324338892'   WHERE id_catalogo_equipo = 38; -- Leica NA324 (2)
UPDATE catalogo_equipos SET serie = '938324337472'   WHERE id_catalogo_equipo = 39; -- Leica NA324 (3)

-- Manuscrito (ids 40-42, antes "OTROS")
UPDATE catalogo_equipos SET serie = '945232' WHERE id_catalogo_equipo = 40; -- LEICA TS01-5"
UPDATE catalogo_equipos SET serie = '799185' WHERE id_catalogo_equipo = 41; -- cargador LEICA GKL 311 Original
-- id 42 (Miniprisma Leica c/bastón) se deja sin serie a propósito: no la tiene en el catálogo real.