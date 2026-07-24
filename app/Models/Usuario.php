<?php

require_once __DIR__ . '/../../config/Database.php';

/**
 * Clase Usuario (Modelo)
 * -----------------------------------------------------
 * Responsable única: gestionar el acceso de solo lectura
 * a la tabla 'usuarios' en la base de datos.
 *
 * La tabla 'usuarios' pertenece al sistema principal de
 * Valtop SRL y ya tiene registros cargados por otro medio.
 * Este Modelo solo lee esos registros para llenar el
 * selector "Responsable" del formulario de Trabajos.
 * -----------------------------------------------------
 */
class Usuario
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::conectar();
    }

    /**
     * Devuelve todos los usuarios como array de filas,
     * ordenados por nombre. Mismo patrón que Cliente::listar().
     */
    public function listar(): array
    {
        $sql = "SELECT id_usuario, nombre_usuario
                FROM usuarios
                ORDER BY nombre_usuario ASC";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }
}