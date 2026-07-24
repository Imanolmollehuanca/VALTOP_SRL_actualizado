<?php

require_once __DIR__ . '/../../config/Database.php';

/**
 * Clase Tareo (Modelo)
 * -----------------------------------------------------
 * Responsable única: gestionar el acceso a la tabla
 * 'tareo' en la base de datos.
 *
 * No crea trabajos ni personal: solo registra qué
 * trabajador participó en qué trabajo, en qué fecha,
 * y qué actividad realizó ese día.
 * -----------------------------------------------------
 */
class Tareo
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::conectar();
    }

    public function crear(array $datos): bool
    {
        $sql = "INSERT INTO tareo
                (id_trabajo, id_personal, fecha, actividad, observaciones)
                VALUES
                (:id_trabajo, :id_personal, :fecha, :actividad, :observaciones)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id_trabajo'    => $datos['id_trabajo'],
            'id_personal'   => $datos['id_personal'],
            'fecha'         => $datos['fecha'],
            'actividad'     => $datos['actividad'],
            'observaciones' => $datos['observaciones'] ?: null,
        ]);
    }

    public function listar(): array
    {
        $sql = "SELECT
                    tar.id_tareo,
                    tar.id_trabajo,
                    t.codigo_trabajo,
                    t.id_responsable,
                    u.nombre_usuario AS nombre_responsable,
                    tar.id_personal,
                    p.nombre_completo AS nombre_trabajador,
                    tar.fecha,
                    tar.actividad,
                    tar.observaciones
                FROM tareo tar
                INNER JOIN trabajos t ON t.id_trabajo = tar.id_trabajo
                INNER JOIN personal p ON p.id_personal = tar.id_personal
                LEFT JOIN usuarios u ON u.id_usuario = t.id_responsable
                ORDER BY tar.fecha DESC, tar.id_tareo DESC";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }

    public function buscarPorId(int $idTareo): ?array
    {
        $sql = "SELECT * FROM tareo WHERE id_tareo = :id_tareo";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_tareo' => $idTareo]);

        $resultado = $stmt->fetch();

        return $resultado ?: null;
    }

    public function actualizar(int $idTareo, array $datos): bool
    {
        $sql = "UPDATE tareo SET
                    id_trabajo = :id_trabajo,
                    id_personal = :id_personal,
                    fecha = :fecha,
                    actividad = :actividad,
                    observaciones = :observaciones
                WHERE id_tareo = :id_tareo";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id_trabajo'    => $datos['id_trabajo'],
            'id_personal'   => $datos['id_personal'],
            'fecha'         => $datos['fecha'],
            'actividad'     => $datos['actividad'],
            'observaciones' => $datos['observaciones'] ?: null,
            'id_tareo'      => $idTareo,
        ]);
    }

    public function eliminar(int $idTareo): bool
    {
        $sql = "DELETE FROM tareo WHERE id_tareo = :id_tareo";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute(['id_tareo' => $idTareo]);
    }
}