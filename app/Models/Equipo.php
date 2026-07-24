<?php

require_once __DIR__ . '/../../config/Database.php';

/**
 * Clase Equipo (Modelo)
 * -----------------------------------------------------
 * Responsable única: gestionar el acceso a la tabla
 * 'equipos' en la base de datos.
 *
 * Cada registro representa el uso/préstamo de equipos
 * en un trabajo específico (relación por id_trabajo).
 * -----------------------------------------------------
 */
class Equipo
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::conectar();
    }

    public function crear(array $datos): bool
    {
        $sql = "INSERT INTO equipos
                (id_trabajo, cantidad_equipos, contacto, telefono_contacto, encargado,
                 fecha_salida, hora_salida, fecha_regreso, hora_regreso,
                 tiempo, costo, pago_1, pago_2, estado)
                VALUES
                (:id_trabajo, :cantidad_equipos, :contacto, :telefono_contacto, :encargado,
                 :fecha_salida, :hora_salida, :fecha_regreso, :hora_regreso,
                 :tiempo, :costo, :pago_1, :pago_2, :estado)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id_trabajo'         => $datos['id_trabajo'],
            'cantidad_equipos'   => $datos['cantidad_equipos'],
            'contacto'           => $datos['contacto'],
            'telefono_contacto'  => $datos['telefono_contacto'] ?? null,
            'encargado'          => $datos['encargado'],
            'fecha_salida'     => $datos['fecha_salida'],
            'hora_salida'      => $datos['hora_salida'],
            'fecha_regreso'    => $datos['fecha_regreso'] ?: null,
            'hora_regreso'     => $datos['hora_regreso'] ?: null,
            'tiempo'           => $datos['tiempo'] ?: null,
            'costo'            => $datos['costo'] ?? 0.00,
            'pago_1'           => $datos['pago_1'] ?? 0.00,
            'pago_2'           => $datos['pago_2'] ?? 0.00,
            'estado'           => $datos['estado'] ?? 'Pendiente',
        ]);
    }

    public function listar(): array
    {
        $sql = "SELECT
                    e.*,
                    t.codigo_trabajo,
                    t.proyecto
                FROM equipos e
                LEFT JOIN trabajos t ON t.id_trabajo = e.id_trabajo
                ORDER BY e.id_equipo DESC";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }

    public function listarPorEstado(?string $estado = null): array
    {
        $sql = "SELECT
                    e.*,
                    t.codigo_trabajo,
                    t.proyecto
                FROM equipos e
                LEFT JOIN trabajos t ON t.id_trabajo = e.id_trabajo";

        $parametros = [];

        if ($estado !== null) {
            $sql .= " WHERE e.estado = :estado";
            $parametros['estado'] = $estado;
        }

        $sql .= " ORDER BY e.id_equipo DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll();
    }

    public function buscarPorId(int $idEquipo): ?array
    {
        $sql = "SELECT
                    e.*,
                    t.codigo_trabajo,
                    t.proyecto
                FROM equipos e
                LEFT JOIN trabajos t ON t.id_trabajo = e.id_trabajo
                WHERE e.id_equipo = :id_equipo";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_equipo' => $idEquipo]);

        $resultado = $stmt->fetch();

        return $resultado ?: null;
    }

    public function actualizar(int $idEquipo, array $datos): bool
    {
        $sql = "UPDATE equipos SET
                    id_trabajo = :id_trabajo,
                    cantidad_equipos = :cantidad_equipos,
                    contacto = :contacto,
                    telefono_contacto = :telefono_contacto,
                    encargado = :encargado,
                    fecha_salida = :fecha_salida,
                    hora_salida = :hora_salida,
                    fecha_regreso = :fecha_regreso,
                    hora_regreso = :hora_regreso,
                    tiempo = :tiempo,
                    costo = :costo,
                    pago_1 = :pago_1,
                    pago_2 = :pago_2,
                    estado = :estado
                WHERE id_equipo = :id_equipo";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id_trabajo'         => $datos['id_trabajo'],
            'cantidad_equipos'   => $datos['cantidad_equipos'],
            'contacto'           => $datos['contacto'],
            'telefono_contacto'  => $datos['telefono_contacto'] ?? null,
            'encargado'          => $datos['encargado'],
            'fecha_salida'     => $datos['fecha_salida'],
            'hora_salida'      => $datos['hora_salida'],
            'fecha_regreso'    => $datos['fecha_regreso'] ?: null,
            'hora_regreso'     => $datos['hora_regreso'] ?: null,
            'tiempo'           => $datos['tiempo'] ?: null,
            'costo'            => $datos['costo'] ?? 0.00,
            'pago_1'           => $datos['pago_1'] ?? 0.00,
            'pago_2'           => $datos['pago_2'] ?? 0.00,
            'estado'           => $datos['estado'],
            'id_equipo'        => $idEquipo,
        ]);
    }

    public function eliminar(int $idEquipo): bool
    {
        $sql = "DELETE FROM equipos WHERE id_equipo = :id_equipo";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute(['id_equipo' => $idEquipo]);
    }
}