<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

class Database
{
    private static string $host;
    private static string $Puerto;
    private static string $nombreBD;
    private static string $usuario;
    private static string $password;

    private static ?PDO $conexion = null;

    public static function conectar(): PDO
    {
        if (self::$conexion === null) {

            self::$host = $_ENV['DB_HOST'];
            self::$Puerto = $_ENV['DB_PORT'];
            self::$nombreBD = $_ENV['DB_NAME'];
            self::$usuario = $_ENV['DB_USER'];
            self::$password = $_ENV['DB_PASSWORD'];

            $dsn =
                "pgsql:host=" . self::$host .
                ";port=" . self::$Puerto .
                ";dbname=" . self::$nombreBD .
                ";sslmode=require";

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

            } catch (PDOException $e) {

                die('Error al conectar a la base de datos: ' . $e->getMessage());

            }
        }

        return self::$conexion;
    }
}