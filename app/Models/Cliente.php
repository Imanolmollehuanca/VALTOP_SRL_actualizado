<?php

require_once __DIR__ . '/../../config/Database.php';

/**
 * Clase Cliente (Modelo)
 * -----------------------------------------------------
 * Responsable única: gestionar el acceso a la tabla
 * 'clientes' en la base de datos.
 *
 * NOTA: esto NO es un módulo de Clientes completo.
 * Valtop SRL no lleva un CRUD de clientes; antes anotaban
 * el nombre en un Excel cuando aparecía uno nuevo. Este
 * Modelo solo reemplaza ese Excel: guarda clientes nuevos
 * y los lista para el selector del formulario de Trabajos.
 * Por eso no existen métodos de actualizar/eliminar.
 * -----------------------------------------------------
 */
class Cliente
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::conectar();
    }

    /**
     * Guarda un cliente nuevo. Devuelve el id_cliente generado,
     * o false si ocurrió un error al insertar.
     */
    public function crear(array $datos): int|false
    {
        $sql = "INSERT INTO clientes
                (nombre_cliente, ruc, telefono, correo, observaciones)
                VALUES
                (:nombre_cliente, :ruc, :telefono, :correo, :observaciones)";

        $stmt = $this->db->prepare($sql);

        $ejecutado = $stmt->execute([
            'nombre_cliente' => $datos['nombre_cliente'],
            'ruc'            => $datos['ruc'] !== '' ? $datos['ruc'] : null,
            'telefono'       => $datos['telefono'] !== '' ? $datos['telefono'] : null,
            'correo'         => $datos['correo'] !== '' ? $datos['correo'] : null,
            'observaciones'  => $datos['observaciones'] !== '' ? $datos['observaciones'] : null,
        ]);

        if (!$ejecutado) {
            return false;
        }

        return (int) $this->db->lastInsertId();
    }

    /**
     * Devuelve todos los clientes registrados, ordenados
     * alfabéticamente. Usado para llenar el selector "Cliente"
     * del formulario de Nuevo Trabajo.
     */
    public function listar(): array
    {
        $sql = "SELECT id_cliente, nombre_cliente
                FROM clientes
                ORDER BY nombre_cliente ASC";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }

    public function buscarPorNombre(string $nombre): ?array
    {
        $sql = "SELECT *
                FROM clientes
                WHERE nombre_cliente = :nombre
                LIMIT 1";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'nombre' => trim($nombre)
        ]);

        $cliente = $stmt->fetch();

        return $cliente ?: null;
    }

    public function buscarPorCoincidencia(string $texto): array
    {
        $sql = "SELECT
                    id_cliente,
                    nombre_cliente
                FROM clientes
                WHERE nombre_cliente LIKE :texto
                ORDER BY nombre_cliente
                LIMIT 10";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'texto' => '%' . trim($texto) . '%'
        ]);

        return $stmt->fetchAll();
    }
}
