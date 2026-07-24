<?php

require_once __DIR__ . '/../../config/Database.php';

/**
 * Clase Viatico (Modelo)
 * -----------------------------------------------------
 * Responsable única: gestionar el acceso a la tabla
 * 'viaticos' en la base de datos.
 *
 * Cada registro pertenece a un único trabajo (id_trabajo).
 * Esta información alimentará más adelante la Valorización
 * (Fase 9) para calcular el costo real del trabajo.
 * -----------------------------------------------------
 */
class Viatico
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::conectar();
    }

    public function crear(array $datos): bool
    {
        $sql = "INSERT INTO viaticos
                (id_trabajo, fecha, concepto, descripcion, monto, estado, observaciones)
                VALUES
                (:id_trabajo, :fecha, :concepto, :descripcion, :monto, :estado, :observaciones)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id_trabajo'    => $datos['id_trabajo'],
            'fecha'         => $datos['fecha'],
            'concepto'      => $datos['concepto'],
            'descripcion'   => $datos['descripcion'],
            'monto'         => $datos['monto'] ?? 0.00,
            'estado'        => $datos['estado'] ?? 'Pendiente',
            'observaciones' => $datos['observaciones'] ?: null,
        ]);
    }

    /**
     * Devuelve todos los viáticos, trayendo el código del
     * trabajo mediante JOIN, para que la vista no necesite
     * consultar 'trabajos' por separado.
     */
public function listar(): array
{
    $sql = "SELECT
                v.id_viatico,
                v.id_trabajo,
                t.codigo_trabajo,
                t.proyecto AS proyecto_nombre,
                v.fecha,
                v.concepto,
                v.descripcion,
                v.monto,
                v.estado,
                v.observaciones
            FROM viaticos v
            INNER JOIN trabajos t ON t.id_trabajo = v.id_trabajo
            ORDER BY v.fecha DESC, v.id_viatico DESC";

    $stmt = $this->db->query($sql);

    return $stmt->fetchAll();
}

    public function buscarPorId(int $idViatico): ?array
    {
        $sql = "SELECT * FROM viaticos WHERE id_viatico = :id_viatico";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_viatico' => $idViatico]);

        $resultado = $stmt->fetch();

        return $resultado ?: null;
    }

    
    public function buscarPorIdConTrabajo(int $idViatico): ?array
    {
        $sql = "SELECT
                    v.*,
                    t.codigo_trabajo,
                    t.proyecto
                FROM viaticos v
                INNER JOIN trabajos t ON t.id_trabajo = v.id_trabajo
                WHERE v.id_viatico = :id_viatico";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_viatico' => $idViatico]);

        $resultado = $stmt->fetch();

        return $resultado ?: null;
    }

    public function actualizar(int $idViatico, array $datos): bool
    {
        $sql = "UPDATE viaticos SET
                    id_trabajo = :id_trabajo,
                    fecha = :fecha,
                    concepto = :concepto,
                    descripcion = :descripcion,
                    monto = :monto,
                    estado = :estado,
                    observaciones = :observaciones
                WHERE id_viatico = :id_viatico";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id_trabajo'    => $datos['id_trabajo'],
            'fecha'         => $datos['fecha'],
            'concepto'      => $datos['concepto'],
            'descripcion'   => $datos['descripcion'],
            'monto'         => $datos['monto'],
            'estado'        => $datos['estado'],
            'observaciones' => $datos['observaciones'] ?: null,
            'id_viatico'    => $idViatico,
        ]);
    }

    public function eliminar(int $idViatico): bool
    {
        $sql = "DELETE FROM viaticos WHERE id_viatico = :id_viatico";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute(['id_viatico' => $idViatico]);
    }
}