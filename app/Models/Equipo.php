<?php

require_once __DIR__ . '/../../config/Database.php';

/**
 * Clase Equipo (Modelo)
 * -----------------------------------------------------
 * Responsable única: gestionar el acceso a la tabla
 * 'equipos' en la base de datos, junto con su detalle
 * relacional en 'equipos_detalle' (equipos específicos
 * del catálogo usados en cada registro) y el catálogo
 * maestro en 'catalogo_equipos'.
 *
 * Cada registro de 'equipos' representa el uso/préstamo
 * general de equipos en un trabajo específico (relación
 * por id_trabajo). 'cantidad_equipos' se calcula siempre
 * como la suma de las cantidades de 'equipos_detalle';
 * ya no se toma directo del formulario.
 * -----------------------------------------------------
 */
class Equipo
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::conectar();
    }

    /**
     * Crea el registro general de equipos junto con sus
     * filas de detalle (equipos específicos utilizados).
     *
     * $datos debe incluir, además de los campos habituales,
     * la clave 'equipos_utilizados' => array de filas
     * ['id_catalogo_equipo' => int, 'cantidad' => int].
     */
    public function crear(array $datos): bool
    {
        $equiposUtilizados = $datos['equipos_utilizados'] ?? [];
        $cantidadTotal = $this->calcularCantidadTotal($equiposUtilizados);

        $this->db->beginTransaction();

        try {
            $sql = "INSERT INTO equipos
                    (id_trabajo, cantidad_equipos, contacto, telefono_contacto, encargado,
                     fecha_salida, hora_salida, fecha_regreso, hora_regreso,
                     tiempo, costo, pago_1, pago_2, estado)
                    VALUES
                    (:id_trabajo, :cantidad_equipos, :contacto, :telefono_contacto, :encargado,
                     :fecha_salida, :hora_salida, :fecha_regreso, :hora_regreso,
                     :tiempo, :costo, :pago_1, :pago_2, :estado)";

            $stmt = $this->db->prepare($sql);

            $stmt->execute([
                'id_trabajo'         => $datos['id_trabajo'],
                'cantidad_equipos'   => $cantidadTotal,
                'contacto'           => $datos['contacto'],
                'telefono_contacto'  => $datos['telefono_contacto'] ?? null,
                'encargado'          => $datos['encargado'],
                'fecha_salida'       => $datos['fecha_salida'],
                'hora_salida'        => $datos['hora_salida'],
                'fecha_regreso'      => $datos['fecha_regreso'] ?: null,
                'hora_regreso'       => $datos['hora_regreso'] ?: null,
                'tiempo'             => $datos['tiempo'] ?: null,
                'costo'              => $datos['costo'] ?? 0.00,
                'pago_1'             => $datos['pago_1'] ?? 0.00,
                'pago_2'             => $datos['pago_2'] ?? 0.00,
                'estado'             => $datos['estado'] ?? 'Pendiente',
            ]);

            $idEquipo = (int) $this->db->lastInsertId();

            $this->guardarDetalle($idEquipo, $equiposUtilizados);

            $this->db->commit();

            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            return false;
        }
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

    /**
     * Trae el registro general junto con sus filas de
     * detalle (equipos específicos usados), ya unidas al
     * catálogo para tener tipo_equipo y equipo_marca listos
     * para mostrar en el formulario de edición y en el detalle.
     */
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

        if ($resultado === false) {
            return null;
        }

        $resultado['equipos_utilizados'] = $this->listarDetallePorEquipo($idEquipo);

        return $resultado;
    }

    /**
     * Actualiza el registro general y reemplaza por completo
     * sus filas de detalle con las que llegan del formulario.
     *
     * $datos debe incluir 'equipos_utilizados' igual que en crear().
     */
    public function actualizar(int $idEquipo, array $datos): bool
    {
        $equiposUtilizados = $datos['equipos_utilizados'] ?? [];
        $cantidadTotal = $this->calcularCantidadTotal($equiposUtilizados);

        $this->db->beginTransaction();

        try {
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

            $stmt->execute([
                'id_trabajo'         => $datos['id_trabajo'],
                'cantidad_equipos'   => $cantidadTotal,
                'contacto'           => $datos['contacto'],
                'telefono_contacto'  => $datos['telefono_contacto'] ?? null,
                'encargado'          => $datos['encargado'],
                'fecha_salida'       => $datos['fecha_salida'],
                'hora_salida'        => $datos['hora_salida'],
                'fecha_regreso'      => $datos['fecha_regreso'] ?: null,
                'hora_regreso'       => $datos['hora_regreso'] ?: null,
                'tiempo'             => $datos['tiempo'] ?: null,
                'costo'              => $datos['costo'] ?? 0.00,
                'pago_1'             => $datos['pago_1'] ?? 0.00,
                'pago_2'             => $datos['pago_2'] ?? 0.00,
                'estado'             => $datos['estado'],
                'id_equipo'          => $idEquipo,
            ]);

            $this->eliminarDetallePorEquipo($idEquipo);
            $this->guardarDetalle($idEquipo, $equiposUtilizados);

            $this->db->commit();

            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function eliminar(int $idEquipo): bool
    {
        $sql = "DELETE FROM equipos WHERE id_equipo = :id_equipo";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute(['id_equipo' => $idEquipo]);
    }

    /**
     * Lista completa del catálogo de equipos, para poblar
     * los selectores de Tipo de equipo / Equipo-Marca en
     * el formulario de registro/edición.
     */
    public function obtenerCatalogoEquipos(): array
    {
        $sql = "SELECT id_catalogo_equipo, tipo_equipo, equipo_marca
                FROM catalogo_equipos
                ORDER BY tipo_equipo ASC, equipo_marca ASC";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }

    /**
     * Filas de equipos_detalle de un registro, ya unidas al
     * catálogo para traer tipo_equipo y equipo_marca listos
     * para mostrar (formulario de edición y vista de detalle).
     */
    private function listarDetallePorEquipo(int $idEquipo): array
    {
        $sql = "SELECT
                    ed.id_equipo_detalle,
                    ed.id_catalogo_equipo,
                    ed.cantidad,
                    ce.tipo_equipo,
                    ce.equipo_marca
                FROM equipos_detalle ed
                INNER JOIN catalogo_equipos ce ON ce.id_catalogo_equipo = ed.id_catalogo_equipo
                WHERE ed.id_equipo = :id_equipo
                ORDER BY ed.id_equipo_detalle ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_equipo' => $idEquipo]);

        return $stmt->fetchAll();
    }

    private function guardarDetalle(int $idEquipo, array $equiposUtilizados): void
    {
        if (empty($equiposUtilizados)) {
            return;
        }

        $sql = "INSERT INTO equipos_detalle (id_equipo, id_catalogo_equipo, cantidad)
                VALUES (:id_equipo, :id_catalogo_equipo, :cantidad)";

        $stmt = $this->db->prepare($sql);

        foreach ($equiposUtilizados as $fila) {
            $stmt->execute([
                'id_equipo'          => $idEquipo,
                'id_catalogo_equipo' => $fila['id_catalogo_equipo'],
                'cantidad'           => $fila['cantidad'],
            ]);
        }
    }

    private function eliminarDetallePorEquipo(int $idEquipo): void
    {
        $sql = "DELETE FROM equipos_detalle WHERE id_equipo = :id_equipo";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_equipo' => $idEquipo]);
    }

    private function calcularCantidadTotal(array $equiposUtilizados): int
    {
        $total = 0;

        foreach ($equiposUtilizados as $fila) {
            $total += (int) ($fila['cantidad'] ?? 0);
        }

        return $total;
    }
}