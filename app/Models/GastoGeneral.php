<?php

require_once __DIR__ . '/../../config/Database.php';

/**
 * Clase GastoGeneral (Modelo)
 * -----------------------------------------------------
 * Responsable única: gestionar el acceso a la tabla
 * 'gastos_generales' en la base de datos.
 *
 * Este módulo es independiente: no conoce 'trabajos' ni
 * ningún otro módulo del sistema.
 * -----------------------------------------------------
 */
class GastoGeneral
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::conectar();
    }

    public function crear(array $datos): bool
    {
        $sql = "INSERT INTO gastos_generales (concepto, fecha, monto, observacion)
                VALUES (:concepto, :fecha, :monto, :observacion)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'concepto'    => $datos['concepto'],
            'fecha'       => $datos['fecha'],
            'monto'       => $datos['monto'],
            'observacion' => $datos['observacion'] ?: null,
        ]);
    }

    public function listar(?string $busqueda = null): array
    {
        $sql = "SELECT * FROM gastos_generales WHERE 1=1";

        $parametros = [];

        if ($busqueda !== null && $busqueda !== '') {
            $sql .= " AND (concepto LIKE :busqueda_concepto OR observacion LIKE :busqueda_observacion)";
            $parametros['busqueda_concepto']    = '%' . $busqueda . '%';
            $parametros['busqueda_observacion'] = '%' . $busqueda . '%';
        }

        $sql .= " ORDER BY fecha DESC, id_gasto DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll();
    }

    public function buscarPorId(int $idGasto): ?array
    {
        $sql = "SELECT * FROM gastos_generales WHERE id_gasto = :id_gasto";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_gasto' => $idGasto]);

        $resultado = $stmt->fetch();

        return $resultado ?: null;
    }

    public function actualizar(int $idGasto, array $datos): bool
    {
        $sql = "UPDATE gastos_generales SET
                    concepto = :concepto,
                    fecha = :fecha,
                    monto = :monto,
                    observacion = :observacion
                WHERE id_gasto = :id_gasto";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'concepto'    => $datos['concepto'],
            'fecha'       => $datos['fecha'],
            'monto'       => $datos['monto'],
            'observacion' => $datos['observacion'] ?: null,
            'id_gasto'    => $idGasto,
        ]);
    }

    public function eliminar(int $idGasto): bool
    {
        $sql = "DELETE FROM gastos_generales WHERE id_gasto = :id_gasto";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute(['id_gasto' => $idGasto]);
    }

    public function eliminarTodos(): bool
    {
        return $this->db->exec("DELETE FROM gastos_generales") !== false;
    }

    public function total(): float
    {
        $sql = "SELECT COALESCE(SUM(monto), 0) AS total FROM gastos_generales";

        $stmt = $this->db->query($sql);

        return (float) $stmt->fetch()['total'];
    }
}