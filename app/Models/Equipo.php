<?php

require_once __DIR__ . '/../../config/Database.php';

/**
 * Clase Equipo (Modelo)
 * -----------------------------------------------------
 * Responsable única: gestionar el acceso a la tabla
 * 'equipos' en la base de datos, junto con:
 *
 * - 'equipos_detalle'  → estado ACTUAL de equipos usados
 *                        en cada registro (se reemplaza
 *                        por completo al editar el
 *                        formulario general).
 * - 'catalogo_equipos' → catálogo maestro de equipos.
 *                        Incluye 'serie': el número de
 *                        serie de la unidad física, propio
 *                        de cada fila del catálogo (no del
 *                        registro de uso). El usuario nunca
 *                        la escribe: siempre se obtiene
 *                        automáticamente vía JOIN según el
 *                        id_catalogo_equipo seleccionado.
 * - 'equipos_cambios'  → historial PERMANENTE de cambios
 *                        de equipo (retirar/agregar). Solo
 *                        se agregan filas nuevas, nunca se
 *                        editan ni se borran.
 *
 * Cada registro de 'equipos' representa el uso/préstamo
 * general de equipos en un trabajo específico (relación
 * por id_trabajo). 'cantidad_equipos' se calcula siempre
 * como la suma de las cantidades de 'equipos_detalle'.
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
     * Trae el registro general junto con:
     * - 'equipos_utilizados'  → estado actual (equipos_detalle)
     * - 'historial_cambios'   → historial permanente (equipos_cambios)
     *
     * Ambos ya unidos al catálogo, listos para pintar el
     * formulario de edición y el detalle sin consultas extra.
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
        $resultado['historial_cambios']  = $this->listarHistorialCambios($idEquipo);

        return $resultado;
    }

    /**
     * Actualiza el registro general y reemplaza por completo
     * sus filas de detalle con las que llegan del formulario
     * general (edición libre, sin generar historial).
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

    /**
     * Registra un cambio de equipo controlado y auditado:
     * retira cantidad de un equipo actual, agrega/aumenta
     * cantidad de un equipo nuevo, recalcula el total y
     * guarda una fila permanente en el historial.
     *
     * Todo ocurre en una única transacción: si algo falla,
     * no queda nada aplicado a medias.
     *
     * $datos debe incluir:
     *   id_catalogo_equipo_retirado, cantidad_retirada,
     *   id_catalogo_equipo_nuevo, cantidad_nueva,
     *   motivo, fecha_cambio, observacion (opcional), usuario (opcional)
     *
     * Devuelve false si el equipo retirado no existe en los
     * equipos actuales del registro, o si no hay cantidad
     * suficiente para retirar (nunca deja cantidades negativas).
     */
    public function registrarCambio(int $idEquipo, array $datos): bool
    {
        $this->db->beginTransaction();

        try {
            // 1. Verificar cantidad actualmente disponible del equipo retirado
            //    (FOR UPDATE: bloquea la fila mientras dura la transacción,
            //    para evitar condiciones de carrera con otro cambio simultáneo)
            $sqlActual = "SELECT cantidad FROM equipos_detalle
                          WHERE id_equipo = :id_equipo AND id_catalogo_equipo = :id_catalogo_equipo
                          FOR UPDATE";

            $stmt = $this->db->prepare($sqlActual);
            $stmt->execute([
                'id_equipo'          => $idEquipo,
                'id_catalogo_equipo' => $datos['id_catalogo_equipo_retirado'],
            ]);
            $filaActual = $stmt->fetch();

            if ($filaActual === false || (int) $filaActual['cantidad'] < (int) $datos['cantidad_retirada']) {
                $this->db->rollBack();
                return false;
            }

            $cantidadRestante = (int) $filaActual['cantidad'] - (int) $datos['cantidad_retirada'];

            // 2. Descontar (o eliminar si llega a 0) la fila del equipo retirado
            if ($cantidadRestante > 0) {
                $sqlActualizarRetirado = "UPDATE equipos_detalle SET cantidad = :cantidad
                                          WHERE id_equipo = :id_equipo AND id_catalogo_equipo = :id_catalogo_equipo";
                $stmt = $this->db->prepare($sqlActualizarRetirado);
                $stmt->execute([
                    'cantidad'           => $cantidadRestante,
                    'id_equipo'          => $idEquipo,
                    'id_catalogo_equipo' => $datos['id_catalogo_equipo_retirado'],
                ]);
            } else {
                $sqlEliminarRetirado = "DELETE FROM equipos_detalle
                                        WHERE id_equipo = :id_equipo AND id_catalogo_equipo = :id_catalogo_equipo";
                $stmt = $this->db->prepare($sqlEliminarRetirado);
                $stmt->execute([
                    'id_equipo'          => $idEquipo,
                    'id_catalogo_equipo' => $datos['id_catalogo_equipo_retirado'],
                ]);
            }

            // 3. Sumar cantidad al equipo nuevo si ya estaba entre los actuales,
            //    o insertar una fila nueva si no estaba
            $sqlBuscarNuevo = "SELECT cantidad FROM equipos_detalle
                               WHERE id_equipo = :id_equipo AND id_catalogo_equipo = :id_catalogo_equipo
                               FOR UPDATE";
            $stmt = $this->db->prepare($sqlBuscarNuevo);
            $stmt->execute([
                'id_equipo'          => $idEquipo,
                'id_catalogo_equipo' => $datos['id_catalogo_equipo_nuevo'],
            ]);
            $filaNuevo = $stmt->fetch();

            if ($filaNuevo !== false) {
                $sqlSumarNuevo = "UPDATE equipos_detalle SET cantidad = cantidad + :cantidad
                                  WHERE id_equipo = :id_equipo AND id_catalogo_equipo = :id_catalogo_equipo";
                $stmt = $this->db->prepare($sqlSumarNuevo);
                $stmt->execute([
                    'cantidad'           => (int) $datos['cantidad_nueva'],
                    'id_equipo'          => $idEquipo,
                    'id_catalogo_equipo' => $datos['id_catalogo_equipo_nuevo'],
                ]);
            } else {
                $sqlInsertarNuevo = "INSERT INTO equipos_detalle (id_equipo, id_catalogo_equipo, cantidad)
                                     VALUES (:id_equipo, :id_catalogo_equipo, :cantidad)";
                $stmt = $this->db->prepare($sqlInsertarNuevo);
                $stmt->execute([
                    'id_equipo'          => $idEquipo,
                    'id_catalogo_equipo' => $datos['id_catalogo_equipo_nuevo'],
                    'cantidad'           => (int) $datos['cantidad_nueva'],
                ]);
            }

            // 4. Recalcular el total de equipos del registro general
            $sqlTotal = "SELECT COALESCE(SUM(cantidad), 0) AS total
                        FROM equipos_detalle WHERE id_equipo = :id_equipo";
            $stmt = $this->db->prepare($sqlTotal);
            $stmt->execute(['id_equipo' => $idEquipo]);
            $total = (int) $stmt->fetch()['total'];

            $sqlActualizarTotal = "UPDATE equipos SET cantidad_equipos = :cantidad_equipos
                                   WHERE id_equipo = :id_equipo";
            $stmt = $this->db->prepare($sqlActualizarTotal);
            $stmt->execute([
                'cantidad_equipos' => $total,
                'id_equipo'        => $idEquipo,
            ]);

            // 5. Guardar el movimiento en el historial permanente
            $sqlHistorial = "INSERT INTO equipos_cambios
                            (id_equipo, id_catalogo_equipo_retirado, cantidad_retirada,
                             id_catalogo_equipo_nuevo, cantidad_nueva, motivo, fecha_cambio,
                             observacion, usuario)
                            VALUES
                            (:id_equipo, :id_catalogo_equipo_retirado, :cantidad_retirada,
                             :id_catalogo_equipo_nuevo, :cantidad_nueva, :motivo, :fecha_cambio,
                             :observacion, :usuario)";
            $stmt = $this->db->prepare($sqlHistorial);
            $stmt->execute([
                'id_equipo'                   => $idEquipo,
                'id_catalogo_equipo_retirado' => $datos['id_catalogo_equipo_retirado'],
                'cantidad_retirada'           => (int) $datos['cantidad_retirada'],
                'id_catalogo_equipo_nuevo'    => $datos['id_catalogo_equipo_nuevo'],
                'cantidad_nueva'              => (int) $datos['cantidad_nueva'],
                'motivo'                      => $datos['motivo'],
                'fecha_cambio'                => $datos['fecha_cambio'],
                'observacion'                 => $datos['observacion'] ?: null,
                'usuario'                     => $datos['usuario'] ?: null,
            ]);

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
     * los selects de "Tipo de equipo" / "Equipo-Marca" en
     * el formulario (Equipos Utilizados y Equipo nuevo).
     * Incluye 'serie': se usa en el frontend para mostrarla
     * automáticamente en cuanto el usuario elige un equipo
     * del catálogo (nunca se escribe a mano).
     */
    public function obtenerCatalogoEquipos(): array
    {
        $sql = "SELECT id_catalogo_equipo, tipo_equipo, equipo_marca, serie
                FROM catalogo_equipos
                ORDER BY tipo_equipo ASC, equipo_marca ASC";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }

    /**
     * Filas de equipos_detalle de un registro (estado ACTUAL),
     * ya unidas al catálogo (incluyendo 'serie'). También se
     * usa para poblar el select "Equipo retirado" en Registrar
     * cambio de equipo, ya que solo deben aparecer ahí los
     * equipos que el registro tiene actualmente.
     */
    private function listarDetallePorEquipo(int $idEquipo): array
    {
        $sql = "SELECT
                    ed.id_equipo_detalle,
                    ed.id_catalogo_equipo,
                    ed.cantidad,
                    ce.tipo_equipo,
                    ce.equipo_marca,
                    ce.serie
                FROM equipos_detalle ed
                INNER JOIN catalogo_equipos ce ON ce.id_catalogo_equipo = ed.id_catalogo_equipo
                WHERE ed.id_equipo = :id_equipo
                ORDER BY ed.id_equipo_detalle ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_equipo' => $idEquipo]);

        return $stmt->fetchAll();
    }

    /**
     * Historial permanente de cambios de un registro, ya unido
     * al catálogo (dos veces: equipo retirado y equipo nuevo)
     * para traer sus nombres y series listos para mostrar. Se
     * ordena del cambio más reciente al más antiguo.
     */
    private function listarHistorialCambios(int $idEquipo): array
    {
        $sql = "SELECT
                    ec.id_cambio,
                    ec.fecha_cambio,
                    ec.cantidad_retirada,
                    ec.cantidad_nueva,
                    ec.motivo,
                    ec.observacion,
                    ec.usuario,
                    ec.creado_en,
                    cr.tipo_equipo  AS tipo_equipo_retirado,
                    cr.equipo_marca AS equipo_marca_retirado,
                    cr.serie        AS serie_retirado,
                    cn.tipo_equipo  AS tipo_equipo_nuevo,
                    cn.equipo_marca AS equipo_marca_nuevo,
                    cn.serie        AS serie_nuevo
                FROM equipos_cambios ec
                INNER JOIN catalogo_equipos cr ON cr.id_catalogo_equipo = ec.id_catalogo_equipo_retirado
                INNER JOIN catalogo_equipos cn ON cn.id_catalogo_equipo = ec.id_catalogo_equipo_nuevo
                WHERE ec.id_equipo = :id_equipo
                ORDER BY ec.fecha_cambio DESC, ec.id_cambio DESC";

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