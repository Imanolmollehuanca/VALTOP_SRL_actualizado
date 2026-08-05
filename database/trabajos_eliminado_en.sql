-- =====================================================
-- Mejora: Borrado lógico de Trabajos (Papelera)
-- =====================================================

ALTER TABLE trabajos
  ADD COLUMN eliminado_en DATETIME NULL DEFAULT NULL AFTER estado;

-- Índice para que el filtro de la Papelera y del listado
-- principal (que ahora siempre excluye eliminados) sea rápido.
ALTER TABLE trabajos
  ADD INDEX idx_trabajos_eliminado_en (eliminado_en);