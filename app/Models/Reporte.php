<?php

require_once __DIR__ . '/../../config/Database.php';

/**
 * Clase Reporte (Modelo)
 * -----------------------------------------------------
 * Responsable única: armar la tabla resumen del módulo de
 * Reportes (Fase 10), a partir de datos que YA existen en
 * otros módulos (Trabajos, Clientes, Usuarios, Costo
 * Financiero, Equipos, Viáticos, Materiales).
 *
 * Este Modelo NO escribe en ninguna tabla. Solo lee y
 * calcula. La fórmula de Capital Invertido reutiliza el
 * mismo criterio que CostoFinanciero.php (Equipos +
 * Viáticos + Materiales); Gastos Generales no se incluye
 * porque esa tabla es independiente de 'trabajos'.
 * -----------------------------------------------------
 */
class Reporte
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::conectar();
    }

    public function listar(array $filtros = []): array
    {
        $sql = $this->consultaBase();
        $parametros = [];

        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND t.fecha_inicio >= :fecha_desde";
            $parametros['fecha_desde'] = $filtros['fecha_desde'];
        }

        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND t.fecha_inicio <= :fecha_hasta";
            $parametros['fecha_hasta'] = $filtros['fecha_hasta'];
        }

        if (!empty($filtros['id_responsable'])) {
            $sql .= " AND t.id_responsable = :id_responsable";
            $parametros['id_responsable'] = (int) $filtros['id_responsable'];
        }

        if (!empty($filtros['estado']) && $filtros['estado'] !== 'Todos') {
            $sql .= " AND t.estado = :estado";
            $parametros['estado'] = $filtros['estado'];
        }

        $sql .= " ORDER BY t.id_trabajo DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        $filas = $stmt->fetchAll();

        foreach ($filas as &$fila) {
            $fila = $this->completarCalculos($fila);
        }
        unset($fila);

        return $filas;
    }

    public function buscarPorId(int $idTrabajo): ?array
    {
        $sql = $this->consultaBase() . " AND t.id_trabajo = :id_trabajo";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_trabajo' => $idTrabajo]);

        $fila = $stmt->fetch();

        if (!$fila) {
            return null;
        }

        return $this->completarCalculos($fila);
    }

    private function consultaBase(): string
    {
        return "SELECT
                    t.id_trabajo,
                    t.codigo_trabajo,
                    t.proyecto,
                    t.precio_neto,
                    t.estado,
                    c.nombre_cliente,
                    u.nombre_usuario AS nombre_responsable,
                    cf.fecha_factura,
                    cf.fecha_cobro,
                    cf.porcentaje_financiero,

                    COALESCE(eq.costo_equipos, 0)     AS costo_equipos,
                    COALESCE(vi.costo_viaticos, 0)    AS costo_viaticos,
                    COALESCE(mat.costo_materiales, 0) AS costo_materiales

                FROM trabajos t

                LEFT JOIN clientes c
                    ON c.id_cliente = t.id_cliente

                LEFT JOIN usuarios u
                    ON u.id_usuario = t.id_responsable

                LEFT JOIN costo_financiero cf
                    ON cf.id_trabajo = t.id_trabajo

                LEFT JOIN (
                    SELECT
                        id_trabajo,
                        SUM(costo) AS costo_equipos
                    FROM equipos
                    GROUP BY id_trabajo
                ) eq
                    ON eq.id_trabajo = t.id_trabajo

                LEFT JOIN (
                    SELECT
                        id_trabajo,
                        SUM(monto) AS costo_viaticos
                    FROM viaticos
                    WHERE estado <> 'Anulado'
                    GROUP BY id_trabajo
                ) vi
                    ON vi.id_trabajo = t.id_trabajo

                LEFT JOIN (
                    SELECT
                        id_trabajo,
                        SUM(cantidad * precio_unitario) AS costo_materiales
                    FROM trabajo_materiales
                    GROUP BY id_trabajo
                ) mat
                    ON mat.id_trabajo = t.id_trabajo

                WHERE 1=1";
    }

    private function completarCalculos(array $fila): array
    {
        $fila['capital_invertido'] =
            (float) $fila['costo_equipos']
            + (float) $fila['costo_viaticos']
            + (float) $fila['costo_materiales'];

        $fila['costo_financiero'] =
            $fila['capital_invertido']
            * ((float) ($fila['porcentaje_financiero'] ?? 0) / 100);

        $fila['utilidad'] =
            (float) $fila['precio_neto']
            - $fila['capital_invertido']
            - $fila['costo_financiero'];

        $fila['estado_cobro'] = $this->calcularEstadoCobro(
            $fila['fecha_factura'],
            $fila['fecha_cobro']
        );

        return $fila;
    }

    private function calcularEstadoCobro(?string $fechaFactura, ?string $fechaCobro): string
    {
        if (!empty($fechaCobro)) {
            return 'Cobrado';
        }

        if (!empty($fechaFactura)) {
            return 'Pendiente';
        }

        return 'Debe';
    }
}