<?php

require_once __DIR__ . '/../../config/Database.php';

/**
 * Clase Personal (Modelo)
 * -----------------------------------------------------
 * Responsable única: gestionar el acceso a la tabla
 * 'personal' en la base de datos.
 *
 * Cada registro representa a un trabajador de la empresa,
 * disponible para ser asignado en los trabajos. Este módulo
 * es independiente del módulo Trabajos.
 * -----------------------------------------------------
 */
class Personal
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::conectar();
    }

    public function crear(array $datos): bool
    {
        $sql = "INSERT INTO personal
                (nombre_completo, telefono_principal, telefono_secundario, telefono_contacto,
                 cargo, id_responsable, estado, observaciones)
                VALUES
                (:nombre_completo, :telefono_principal, :telefono_secundario, :telefono_contacto,
                 :cargo, :id_responsable, :estado, :observaciones)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'nombre_completo'     => $datos['nombre_completo'],
            'telefono_principal'  => $datos['telefono_principal'],
            'telefono_secundario' => $datos['telefono_secundario'] ?: null,
            'telefono_contacto'   => $datos['telefono_contacto'] ?: null,
            'cargo'               => $datos['cargo'],
            'id_responsable'      => $datos['id_responsable'],
            'estado'              => $datos['estado'] ?? 'Activo',
            'observaciones'       => $datos['observaciones'] ?: null,
        ]);
    }

    public function listarPorEstado(?string $estado = null): array
    {
        $sql = "SELECT
                    p.*,
                    u.nombre_usuario AS responsable_nombre
                FROM personal p
                LEFT JOIN usuarios u ON u.id_usuario = p.id_responsable";

        $parametros = [];

        if ($estado !== null) {
            $sql .= " WHERE p.estado = :estado";
            $parametros['estado'] = $estado;
        }

        $sql .= " ORDER BY p.id_personal DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll();
    }

    public function buscarPorId(int $idPersonal): ?array
    {
        $sql = "SELECT
                    p.*,
                    u.nombre_usuario AS responsable_nombre
                FROM personal p
                LEFT JOIN usuarios u ON u.id_usuario = p.id_responsable
                WHERE p.id_personal = :id_personal";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_personal' => $idPersonal]);
        $resultado = $stmt->fetch();
        return $resultado ?: null;
    }

    public function actualizar(int $idPersonal, array $datos): bool
    {
        $sql = "UPDATE personal SET
                    nombre_completo = :nombre_completo,
                    telefono_principal = :telefono_principal,
                    telefono_secundario = :telefono_secundario,
                    telefono_contacto = :telefono_contacto,
                    cargo = :cargo,
                    id_responsable = :id_responsable,
                    estado = :estado,
                    observaciones = :observaciones
                WHERE id_personal = :id_personal";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'nombre_completo'     => $datos['nombre_completo'],
            'telefono_principal'  => $datos['telefono_principal'],
            'telefono_secundario' => $datos['telefono_secundario'] ?: null,
            'telefono_contacto'   => $datos['telefono_contacto'] ?: null,
            'cargo'               => $datos['cargo'],
            'id_responsable'      => $datos['id_responsable'],
            'estado'              => $datos['estado'],
            'observaciones'       => $datos['observaciones'] ?: null,
            'id_personal'         => $idPersonal,
        ]);
    }

    public function eliminar(int $idPersonal): bool
    {
        $sql = "DELETE FROM personal WHERE id_personal = :id_personal";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id_personal' => $idPersonal]);
    }
}