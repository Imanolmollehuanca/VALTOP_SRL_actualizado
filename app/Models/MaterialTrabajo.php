<?php

require_once __DIR__ . '/../../config/Database.php';

/**
 * Clase MaterialTrabajo (Modelo)
 * -----------------------------------------------------
 * Responsable única: gestionar el detalle de materiales
 * usados por cada trabajo (tabla 'trabajo_materiales').
 *
 * El subtotal de cada fila y el costo total de un trabajo
 * NUNCA se calculan en PHP ni se guardan como columna: se
 * calculan siempre con SQL (cantidad * precio_unitario),
 * para que jamás queden desincronizados con los datos reales.
 * -----------------------------------------------------
 */
class MaterialTrabajo
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::conectar();
    }

    public function crear(array $datos): bool
    {
        $sql = "INSERT INTO trabajo_materiales
                (id_trabajo, id_material, cantidad, unidad, precio_unitario)
                VALUES
                (:id_trabajo, :id_material, :cantidad, :unidad, :precio_unitario)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id_trabajo'      => $datos['id_trabajo'],
            'id_material'     => $datos['id_material'],
            'cantidad'        => $datos['cantidad'],
            'unidad'          => $datos['unidad'],
            'precio_unitario' => $datos['precio_unitario'],
        ]);
    }

    public function listarPorTrabajo(int $idTrabajo): array
    {
        $sql = "SELECT
                    tm.id_trabajo_material,
                    tm.id_trabajo,
                    tm.id_material,
                    m.nombre_material,
                    tm.cantidad,
                    tm.unidad,
                    tm.precio_unitario,
                    (tm.cantidad * tm.precio_unitario) AS subtotal
                FROM trabajo_materiales tm
                INNER JOIN materiales m ON m.id_material = tm.id_material
                WHERE tm.id_trabajo = :id_trabajo
                ORDER BY tm.id_trabajo_material DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_trabajo' => $idTrabajo]);

        return $stmt->fetchAll();
    }

    public function buscarPorId(int $idTrabajoMaterial): ?array
    {
        $sql = "SELECT tm.*, m.nombre_material
                FROM trabajo_materiales tm
                INNER JOIN materiales m ON m.id_material = tm.id_material
                WHERE tm.id_trabajo_material = :id_trabajo_material";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_trabajo_material' => $idTrabajoMaterial]);

        $resultado = $stmt->fetch();

        return $resultado ?: null;
    }

    public function actualizar(int $idTrabajoMaterial, array $datos): bool
    {
        $sql = "UPDATE trabajo_materiales SET
                    id_material = :id_material,
                    cantidad = :cantidad,
                    unidad = :unidad,
                    precio_unitario = :precio_unitario
                WHERE id_trabajo_material = :id_trabajo_material";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id_material'         => $datos['id_material'],
            'cantidad'            => $datos['cantidad'],
            'unidad'              => $datos['unidad'],
            'precio_unitario'     => $datos['precio_unitario'],
            'id_trabajo_material' => $idTrabajoMaterial,
        ]);
    }

    public function eliminar(int $idTrabajoMaterial): bool
    {
        $sql = "DELETE FROM trabajo_materiales WHERE id_trabajo_material = :id_trabajo_material";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute(['id_trabajo_material' => $idTrabajoMaterial]);
    }

    public function eliminarTodosDeTrabajo(int $idTrabajo): bool
    {
        $sql = "DELETE FROM trabajo_materiales WHERE id_trabajo = :id_trabajo";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute(['id_trabajo' => $idTrabajo]);
    }

    public function costoTotalPorTrabajo(int $idTrabajo): float
    {
        $sql = "SELECT COALESCE(SUM(cantidad * precio_unitario), 0) AS costo_total
                FROM trabajo_materiales
                WHERE id_trabajo = :id_trabajo";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_trabajo' => $idTrabajo]);

        return (float) $stmt->fetch()['costo_total'];
    }

    public function listarResumenPorTrabajo(): array
    {
        $sql = "SELECT
                    t.id_trabajo,
                    t.codigo_trabajo,
                    t.proyecto,
                    t.estado,
                    u.nombre_usuario AS nombre_responsable,
                    COALESCE(mt.costo_materiales, 0) AS costo_materiales,
                    per.nombre_personal_principal,
                    COALESCE(per.total_personal, 0) AS total_personal
                FROM trabajos t
                LEFT JOIN usuarios u ON u.id_usuario = t.id_responsable
                LEFT JOIN (
                    SELECT id_trabajo, SUM(cantidad * precio_unitario) AS costo_materiales
                    FROM trabajo_materiales
                    GROUP BY id_trabajo
                ) mt ON mt.id_trabajo = t.id_trabajo
                LEFT JOIN (
                    SELECT
                        tar.id_trabajo,
                        MIN(p.nombre_completo) AS nombre_personal_principal,
                        COUNT(DISTINCT tar.id_personal) AS total_personal
                    FROM tareo tar
                    INNER JOIN personal p ON p.id_personal = tar.id_personal
                    GROUP BY tar.id_trabajo
                ) per ON per.id_trabajo = t.id_trabajo
                ORDER BY t.id_trabajo DESC";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }
}