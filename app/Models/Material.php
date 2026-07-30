<?php

require_once __DIR__ . '/../../config/Database.php';

/**
 * Clase Material (Modelo)
 * -----------------------------------------------------
 * Responsable única: gestionar el catálogo de materiales
 * (tabla 'materiales'), igual que Cliente gestiona el
 * catálogo de clientes.
 *
 * El usuario escribe el nombre del material libremente en
 * un campo de texto con autocompletado; si el material no
 * existe todavía, se crea automáticamente al guardar un
 * registro en trabajo_materiales (mismo patrón "buscar o
 * crear" que ya usa ClienteController).
 * -----------------------------------------------------
 */
class Material
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::conectar();
    }

    public function crear(string $nombreMaterial): int|false
    {
        $sql = "INSERT INTO materiales (nombre_material) VALUES (:nombre_material)";

        $stmt = $this->db->prepare($sql);
        $creado = $stmt->execute(['nombre_material' => $nombreMaterial]);

        return $creado ? (int) $this->db->lastInsertId() : false;
    }

    public function buscarPorNombre(string $nombre): ?array
    {
        $sql = "SELECT id_material, nombre_material
                FROM materiales
                WHERE LOWER(nombre_material) = LOWER(:nombre)
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['nombre' => trim($nombre)]);

        $resultado = $stmt->fetch();

        return $resultado ?: null;
    }

    
    public function listar(): array
    {
        $sql = "SELECT id_material, nombre_material
                FROM materiales
                ORDER BY nombre_material ASC";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }
}