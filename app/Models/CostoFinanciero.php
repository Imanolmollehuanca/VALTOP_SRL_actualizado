<?php

require_once __DIR__ . '/../../config/Database.php';

/**
 * Clase CostoFinanciero (Modelo)
 * -----------------------------------------------------
 * Responsable única: gestionar el acceso a la tabla
 * 'costo_financiero' Y calcular, a partir de los demás
 * módulos (Personal+Tareo, Equipos, Viáticos, Materiales,
 * Gastos Generales), el Capital Invertido, los Días y el
 * Costo Financiero de cada trabajo.
 *
 * Ningún otro archivo del sistema conoce la fórmula del
 * Costo Financiero: todos le piden el resultado a este
 * Modelo. Cuando la empresa confirme la fórmula real,
 * solo se edita el método calcularCostoFinanciero().
 * -----------------------------------------------------
 */
class CostoFinanciero
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::conectar();
    }

    public function asegurarRegistro(int $idTrabajo): void
    {
        $sql = "INSERT INTO costo_financiero (id_trabajo, porcentaje_financiero)
                SELECT :id_trabajo, 0.00
                WHERE NOT EXISTS (
                    SELECT 1 FROM costo_financiero WHERE id_trabajo = :id_trabajo_existente)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id_trabajo'           => $idTrabajo,
            'id_trabajo_existente' => $idTrabajo,
        ]);
    }

    public function recalcularTodos(): void
    {
        $sql = "SELECT id_trabajo FROM trabajos";
        $stmt = $this->db->query($sql);
        $idsTrabajos = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($idsTrabajos as $idTrabajo) {
            $this->asegurarRegistro((int) $idTrabajo);
        }
    }

    public function listar(): array
    {
        $sql = $this->consultaBaseConCostos() . " ORDER BY t.id_trabajo DESC";

        $stmt = $this->db->query($sql);
        $filas = $stmt->fetchAll();

        foreach ($filas as &$fila) {
            $fila = $this->completarCalculos($fila);
        }
        unset($fila);

        return $filas;
    }

    public function buscarPorId(int $idTrabajo): ?array
    {
        $sql = $this->consultaBaseConCostos() . " AND t.id_trabajo = :id_trabajo";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_trabajo' => $idTrabajo]);

        $fila = $stmt->fetch();

        if (!$fila) {
            return null;
        }

        return $this->completarCalculos($fila);
    }

    public function actualizar(int $idTrabajo, array $datos): bool
    {
        $this->asegurarRegistro($idTrabajo);

        $sql = "UPDATE costo_financiero SET
                    fecha_factura = :fecha_factura,
                    fecha_cobro = :fecha_cobro,
                    porcentaje_financiero = :porcentaje_financiero
                WHERE id_trabajo = :id_trabajo";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'fecha_factura'         => $datos['fecha_factura'] ?: null,
            'fecha_cobro'           => $datos['fecha_cobro'] ?: null,
            'porcentaje_financiero' => $datos['porcentaje_financiero'] ?? 0.00,
            'id_trabajo'            => $idTrabajo,
        ]);
    }

    private function consultaBaseConCostos(): string
    {
        return "SELECT
                    t.id_trabajo,
                    t.codigo_trabajo,
                    t.proyecto,
                    c.nombre_cliente,
                    cf.fecha_factura,
                    cf.fecha_cobro,
                    cf.porcentaje_financiero,

                    0.00 AS costo_personal,

                    COALESCE(eq.costo_equipos, 0)     AS costo_equipos,
                    COALESCE(vi.costo_viaticos, 0)    AS costo_viaticos,
                    COALESCE(mat.costo_materiales, 0) AS costo_materiales

                FROM trabajos t

                INNER JOIN clientes c
                    ON c.id_cliente = t.id_cliente

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
        $fila['costo_gastos_generales'] = 0.00;

        $fila['capital_invertido'] =
            (float) $fila['costo_personal']
            + (float) $fila['costo_equipos']
            + (float) $fila['costo_viaticos']
            + (float) $fila['costo_materiales']
            + (float) $fila['costo_gastos_generales'];

        $fila['dias'] = $this->calcularDias($fila['fecha_factura'], $fila['fecha_cobro']);

        $fila['costo_financiero'] = $this->calcularCostoFinanciero(
            $fila['capital_invertido'],
            $fila['dias'],
            (float) ($fila['porcentaje_financiero'] ?? 0)
        );

        return $fila;
    }

    private function calcularDias(?string $fechaFactura, ?string $fechaCobro): ?int
    {
        if (empty($fechaFactura) || empty($fechaCobro)) {
            return null;
        }

        $inicio = new DateTime($fechaFactura);
        $fin    = new DateTime($fechaCobro);

        return $fin->diff($inicio)->days;
    }

    private function calcularCostoFinanciero(
        float $capitalInvertido,
        ?int $dias,
        float $porcentajeFinanciero
    ): float {
        return $capitalInvertido * ($porcentajeFinanciero / 100);
    }
}