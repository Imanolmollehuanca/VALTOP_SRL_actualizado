<?php

/**
 * Clase Database
 * -----------------------------------------------------
 * Responsable única: crear y entregar la conexión a la
 * base de datos usando PDO.
 *
 * Se usa el patrón "un solo punto de conexión": cualquier
 * modelo que necesite hablar con la BD llama a
 * Database::conectar() en vez de escribir su propia
 * conexión repetida.
 * -----------------------------------------------------
 */
class Database
{
    private static string $host = 'localhost';
    private static string $Puerto = '3306';
    private static string $nombreBD = 'valtop_srl';
    private static string $usuario = 'root';
    private static string $password = '';

    private static ?PDO $conexion = null;

    public static function conectar(): PDO
    {
        if (self::$conexion === null){
            $dsn = 
                "mysql:host=". self::$host .
                ";port=". self::$Puerto .
                ";dbname=". self::$nombreBD .
                ";charset=utf8mb4";
            
            try {
                self::$conexion = new PDO(
                    $dsn,
                    self::$usuario,
                    self::$password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            }catch (PDOException $e){
                die('Error al conectar a la base de datos: ' . $e->getMessage());
            }
        }
        return self::$conexion;
    }
}