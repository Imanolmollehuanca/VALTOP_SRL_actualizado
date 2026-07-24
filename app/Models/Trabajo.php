<?php

require_once __DIR__ . '/../../config/Database.php';

/**
 * Clase Trabajo (Modelo)
 * -----------------------------------------------------
 * Responsable única: gestionar el acceso a la tabla
 * 'trabajos' en la base de datos.
 *
 * El Controlador nunca escribe SQL directamente: le pide
 * datos a este Modelo, y este Modelo es el único que sabe
 * cómo están guardados esos datos. 
 * -----------------------------------------------------
 */
class Trabajo
{

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::conectar();
    }

    public function crear(array $datos): bool
    {
        $sql = "INSERT INTO trabajos
                (codigo_trabajo, fecha_registro, id_cliente, proyecto,
                 descripcion, ubicacion, id_responsable, precio_neto,
                 fecha_inicio, fecha_fin, estado)
                VALUES
                (:codigo_trabajo, :fecha_registro, :id_cliente, :proyecto,
                 :descripcion, :ubicacion, :id_responsable, :precio_neto,
                 :fecha_inicio, :fecha_fin, :estado)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'codigo_trabajo'  => $datos['codigo_trabajo'],
            'fecha_registro'  => $datos['fecha_registro'],
            'id_cliente'      => $datos['id_cliente'],
            'proyecto'        => $datos['proyecto'],
            'descripcion'     => $datos['descripcion'],
            'ubicacion'       => $datos['ubicacion'],
            'id_responsable'  => $datos['id_responsable'],
            'precio_neto'     => $datos['precio_neto'] ?? 0.00,
            'fecha_inicio'    => $datos['fecha_inicio'],
            'fecha_fin'       => $datos['fecha_fin'],
            'estado'          => $datos['estado'] ?? 'Pendiente',
        ]);
    }

/**
     * Devuelve los trabajos, con filtros opcionales.
     * Trae el nombre del cliente y del responsable mediante JOIN.
     *
     * @param string|null $estado        Filtra por estado exacto (ej: 'Pendiente')
     * @param int|null    $idResponsable Filtra por responsable exacto
     * @param string|null $busqueda      Busca coincidencia en proyecto o código
     */
    public function listar(
            ?string $estado = null,
            ?int $idResponsable = null,
            ?string $busqueda = null
        ): array {
        $sql = "SELECT
                    t.id_trabajo,
                    t.codigo_trabajo,
                    t.id_cliente,
                    c.nombre_cliente,
                    t.proyecto,
                    t.ubicacion,
                    t.id_responsable,
                    u.nombre_usuario AS nombre_responsable,
                    t.precio_neto,
                    t.fecha_inicio,
                    t.fecha_fin,
                    t.estado,
                    CASE
                        WHEN DATEDIFF(t.fecha_fin, t.fecha_inicio) + 1 <= 2 THEN 'Rápido'
                        ELSE 'Proyecto'
                    END AS tipo
                FROM trabajos t
                LEFT JOIN clientes c ON c.id_cliente = t.id_cliente
                LEFT JOIN usuarios u ON u.id_usuario = t.id_responsable
                WHERE 1=1";

        $parametros = [];

        if ($estado !== null) {
            $sql .= " AND t.estado = :estado";
            $parametros['estado'] = $estado;
        }

        if ($idResponsable !== null) {
            $sql .= " AND t.id_responsable = :id_responsable";
            $parametros['id_responsable'] = $idResponsable;
        }

        if ($busqueda !== null && $busqueda !== '') {
            $sql .= " AND (t.proyecto LIKE :busqueda_proyecto OR t.codigo_trabajo LIKE :busqueda_codigo)";
            $parametros['busqueda_proyecto'] = '%' . $busqueda . '%';
            $parametros['busqueda_codigo'] = '%' . $busqueda . '%';
        }

        $sql .= " ORDER BY t.id_trabajo DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll();
    }

public function buscarPorId(int $idTrabajo): ?array
    {
        $sql = "SELECT t.*, c.nombre_cliente, u.nombre_usuario AS nombre_responsable
                FROM trabajos t
                LEFT JOIN clientes c ON c.id_cliente = t.id_cliente
                LEFT JOIN usuarios u ON u.id_usuario = t.id_responsable
                WHERE t.id_trabajo = :id_trabajo";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_trabajo' => $idTrabajo]);

        $resultado = $stmt->fetch();

        return $resultado ?: null;
    }

    public function actualizar(int $idTrabajo, array $datos): bool
    {
        $sql = "UPDATE trabajos SET
                    id_cliente = :id_cliente,
                    proyecto = :proyecto,
                    descripcion = :descripcion,
                    ubicacion = :ubicacion,
                    id_responsable = :id_responsable,
                    precio_neto = :precio_neto,
                    fecha_inicio = :fecha_inicio,
                    fecha_fin = :fecha_fin,
                    estado = :estado
                WHERE id_trabajo = :id_trabajo";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id_cliente'      => $datos['id_cliente'],
            'proyecto'        => $datos['proyecto'],
            'descripcion'     => $datos['descripcion'],
            'ubicacion'       => $datos['ubicacion'],
            'id_responsable'  => $datos['id_responsable'],
            'precio_neto'     => $datos['precio_neto'],
            'fecha_inicio'    => $datos['fecha_inicio'],
            'fecha_fin'       => $datos['fecha_fin'],
            'estado'          => $datos['estado'],
            'id_trabajo'      => $idTrabajo,
        ]);
    }

    public function cambiarEstado(int $idTrabajo, string $nuevoEstado): bool
    {
        $sql = "UPDATE trabajos SET estado = :estado WHERE id_trabajo = :id_trabajo";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'estado'     => $nuevoEstado,
            'id_trabajo' => $idTrabajo,
        ]);
    }

    public function generarSiguienteCodigo(): string
    {
        $sql = "SELECT codigo_trabajo FROM trabajos
                ORDER BY id_trabajo DESC LIMIT 1";

        $stmt = $this->db->query($sql);
        $ultimo = $stmt->fetch();

        if (!$ultimo) {
            return 'TR-001';
        }

        $numero = (int) str_replace('TR-', '', $ultimo['codigo_trabajo']);
        $siguiente = $numero + 1;

        return 'TR-' . str_pad((string) $siguiente, 3, '0', STR_PAD_LEFT);
    }

    public function eliminar(int $idTrabajo): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM trabajos
            WHERE id_trabajo = :id
        ");

        $stmt->execute([
            'id' => $idTrabajo
        ]);

        echo "Filas eliminadas: " . $stmt->rowCount();
        exit;
    }
    public function tieneEquipos(int $idTrabajo): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM equipos
            WHERE id_trabajo = :id
        ");

        $stmt->execute([
            'id' => $idTrabajo
        ]);

        return $stmt->fetchColumn() > 0;
    }
    
public function buscarAutocompletado(string $termino): array
{
    $sql = "SELECT
                id_trabajo AS id,
                codigo_trabajo AS codigo,
                proyecto
            FROM trabajos
            WHERE codigo_trabajo LIKE :busqueda
               OR proyecto LIKE :busqueda2
            ORDER BY id_trabajo DESC
            LIMIT 10";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([
        'busqueda'  => '%' . $termino . '%',
        'busqueda2' => '%' . $termino . '%',
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}