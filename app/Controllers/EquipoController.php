<?php

require_once __DIR__ . '/../Models/Equipo.php';

/**
 * Clase EquipoController
 * -----------------------------------------------------
 * Responsable única: coordinar las acciones del módulo
 * de Equipos con el Modelo Equipo.
 *
 * No contiene SQL. No contiene HTML.
 * -----------------------------------------------------
 */
class EquipoController
{
    // Única fuente de verdad de los estados válidos (igual que
    // TrabajoController::ESTADOS_VALIDOS).
    public const ESTADOS_VALIDOS = ['Pendiente', 'Devuelto', 'Cambio de equipo'];

    // Valor por defecto del campo Encargado. Centralizado aquí para
    // que, cuando exista el módulo de Usuarios, solo haya que cambiar
    // esta línea (o convertirla en una consulta real).
    public const ENCARGADO_POR_DEFECTO = 'Ingrid Castillo';

    // Única fuente de verdad de los motivos válidos para un cambio
    // de equipo (sección "Registrar cambio de equipo").
    public const MOTIVOS_VALIDOS = [
        'Equipo con falla',
        'Equipo dañado',
        'Mantenimiento',
        'Solicitud del cliente',
        'Otro',
    ];

    private Equipo $equipoModel;

    public function __construct()
    {
        $this->equipoModel = new Equipo();
    }

    public function listar(?string $estado = null): array
    {
        if ($estado === 'Todos' || $estado === '') {
            $estado = null;
        }

        return $this->equipoModel->listarPorEstado($estado);
    }

    public function verDetalle(int $idEquipo): ?array
    {
        return $this->equipoModel->buscarPorId($idEquipo);
    }

    /**
     * Catálogo completo de equipos, para poblar los selects
     * de "Tipo de equipo" / "Equipo-Marca" en el formulario.
     */
    public function obtenerCatalogo(): array
    {
        return $this->equipoModel->obtenerCatalogoEquipos();
    }

    public function registrar(array $datosFormulario): array
    {
        $camposObligatorios = ['id_trabajo', 'contacto', 'fecha_salida', 'hora_salida'];

        foreach ($camposObligatorios as $campo) {
            if (empty($datosFormulario[$campo]) && $datosFormulario[$campo] !== '0') {
                return [
                    'exito'   => false,
                    'mensaje' => "El campo '$campo' es obligatorio.",
                ];
            }
        }

        $equiposUtilizados = $this->normalizarEquiposUtilizados($datosFormulario['equipos_utilizados'] ?? []);

        $errorEquipos = $this->validarEquiposUtilizados($equiposUtilizados);
        if ($errorEquipos !== null) {
            return ['exito' => false, 'mensaje' => $errorEquipos];
        }

        $datosFormulario['equipos_utilizados'] = $equiposUtilizados;

        $datosFormulario['encargado'] = trim($datosFormulario['encargado'] ?? '') !== ''
            ? trim($datosFormulario['encargado'])
            : self::ENCARGADO_POR_DEFECTO;

        $datosFormulario['estado'] = $datosFormulario['estado'] ?? 'Pendiente';

        $errorValidacion = $this->validarReglasDeNegocio($datosFormulario);
        if ($errorValidacion !== null) {
            return ['exito' => false, 'mensaje' => $errorValidacion];
        }


        $creado = $this->equipoModel->crear($datosFormulario);

        return [
            'exito'   => $creado,
            'mensaje' => $creado
                ? 'Registro de equipos guardado correctamente.'
                : 'Ocurrió un error al guardar el registro de equipos.',
        ];
    }

    public function actualizar(int $idEquipo, array $datosFormulario): array
    {
        $equipoExistente = $this->equipoModel->buscarPorId($idEquipo);

        if ($equipoExistente === null) {
            return [
                'exito'   => false,
                'mensaje' => 'El registro que intentas editar no existe.',
            ];
        }

        $equiposUtilizados = $this->normalizarEquiposUtilizados($datosFormulario['equipos_utilizados'] ?? []);

        $errorEquipos = $this->validarEquiposUtilizados($equiposUtilizados);
        if ($errorEquipos !== null) {
            return ['exito' => false, 'mensaje' => $errorEquipos];
        }

        $datosFormulario['equipos_utilizados'] = $equiposUtilizados;

        $datosFormulario['encargado'] = trim($datosFormulario['encargado'] ?? '') !== ''
            ? trim($datosFormulario['encargado'])
            : self::ENCARGADO_POR_DEFECTO;

        $errorValidacion = $this->validarReglasDeNegocio($datosFormulario);
        if ($errorValidacion !== null) {
            return ['exito' => false, 'mensaje' => $errorValidacion];
        }

        $actualizado = $this->equipoModel->actualizar($idEquipo, $datosFormulario);

        return [
            'exito'   => $actualizado,
            'mensaje' => $actualizado
                ? 'Registro de equipos actualizado correctamente.'
                : 'Ocurrió un error al actualizar el registro.',
        ];
    }

    /**
     * Registra un cambio de equipo (retirar uno / agregar otro) sobre
     * un registro existente. Es una acción independiente de actualizar():
     * no toca los demás campos del registro general, no depende del
     * botón "Actualizar Registro" y siempre queda guardada en el
     * historial permanente (equipos_cambios), sin importar qué pase
     * después con el resto del formulario.
     *
     * $datosFormulario debe incluir:
     *   id_catalogo_equipo_retirado, cantidad_retirada,
     *   id_catalogo_equipo_nuevo, cantidad_nueva,
     *   motivo, fecha_cambio, observacion (opcional)
     */
    public function registrarCambio(int $idEquipo, array $datosFormulario): array
    {
        $equipoExistente = $this->equipoModel->buscarPorId($idEquipo);

        if ($equipoExistente === null) {
            return [
                'exito'   => false,
                'mensaje' => 'El registro de equipos sobre el que intentas registrar un cambio no existe.',
            ];
        }

        $camposObligatorios = [
            'id_catalogo_equipo_retirado',
            'cantidad_retirada',
            'id_catalogo_equipo_nuevo',
            'cantidad_nueva',
            'motivo',
            'fecha_cambio',
        ];

        foreach ($camposObligatorios as $campo) {
            if (empty($datosFormulario[$campo]) && $datosFormulario[$campo] !== '0') {
                return [
                    'exito'   => false,
                    'mensaje' => "El campo '$campo' es obligatorio para registrar el cambio de equipo.",
                ];
            }
        }

        $idCatalogoRetirado = (int) $datosFormulario['id_catalogo_equipo_retirado'];
        $cantidadRetirada   = (int) $datosFormulario['cantidad_retirada'];
        $idCatalogoNuevo    = (int) $datosFormulario['id_catalogo_equipo_nuevo'];
        $cantidadNueva      = (int) $datosFormulario['cantidad_nueva'];
        $motivo             = trim($datosFormulario['motivo']);

        if ($idCatalogoRetirado <= 0) {
            return ['exito' => false, 'mensaje' => 'Seleccionaste un equipo retirado inválido.'];
        }

        if ($idCatalogoNuevo <= 0) {
            return ['exito' => false, 'mensaje' => 'Seleccionaste un equipo nuevo inválido.'];
        }

        if ($cantidadRetirada <= 0) {
            return ['exito' => false, 'mensaje' => 'La cantidad retirada debe ser mayor a 0.'];
        }

        if ($cantidadNueva <= 0) {
            return ['exito' => false, 'mensaje' => 'La cantidad nueva debe ser mayor a 0.'];
        }

        if (!in_array($motivo, self::MOTIVOS_VALIDOS, true)) {
            return ['exito' => false, 'mensaje' => 'El motivo del cambio no es válido.'];
        }

        // El equipo retirado debe pertenecer actualmente al registro
        // (misma regla que ya aplica Equipo::registrarCambio() en la
        // base de datos; se valida también aquí para dar un mensaje
        // más claro antes de llegar al modelo).
        $perteneceAlRegistro = false;
        foreach ($equipoExistente['equipos_utilizados'] as $filaActual) {
            if ((int) $filaActual['id_catalogo_equipo'] === $idCatalogoRetirado) {
                $perteneceAlRegistro = true;

                if ($cantidadRetirada > (int) $filaActual['cantidad']) {
                    return [
                        'exito'   => false,
                        'mensaje' => 'La cantidad retirada no puede ser mayor a la cantidad actualmente disponible de ese equipo.',
                    ];
                }

                break;
            }
        }

        if (!$perteneceAlRegistro) {
            return [
                'exito'   => false,
                'mensaje' => 'El equipo retirado no pertenece actualmente a este registro.',
            ];
        }

        $datosCambio = [
            'id_catalogo_equipo_retirado' => $idCatalogoRetirado,
            'cantidad_retirada'           => $cantidadRetirada,
            'id_catalogo_equipo_nuevo'    => $idCatalogoNuevo,
            'cantidad_nueva'              => $cantidadNueva,
            'motivo'                      => $motivo,
            'fecha_cambio'                => $datosFormulario['fecha_cambio'],
            'observacion'                 => trim($datosFormulario['observacion'] ?? '') !== ''
                ? trim($datosFormulario['observacion'])
                : null,
            'usuario'                     => $datosFormulario['usuario'] ?? null,
        ];

        $registrado = $this->equipoModel->registrarCambio($idEquipo, $datosCambio);

        return [
            'exito'   => $registrado,
            'mensaje' => $registrado
                ? 'Cambio de equipo registrado correctamente.'
                : 'No se pudo registrar el cambio de equipo. Verifica la cantidad disponible del equipo retirado.',
        ];
    }

    public function eliminar(int $idEquipo): array
    {
        $eliminado = $this->equipoModel->eliminar($idEquipo);

        return [
            'exito'   => $eliminado,
            'mensaje' => $eliminado
                ? 'Registro eliminado correctamente.'
                : 'Ocurrió un error al eliminar el registro.',
        ];
    }

    /**
     * Limpia las filas de equipos utilizados que llegan del
     * formulario: descarta filas vacías (por ejemplo, una fila
     * "+ Agregar equipo" que el usuario dejó sin completar) y
     * castea id_catalogo_equipo/cantidad a entero.
     */
    private function normalizarEquiposUtilizados(array $filas): array
    {
        $normalizadas = [];

        foreach ($filas as $fila) {
            $idCatalogoEquipo = $fila['id_catalogo_equipo'] ?? '';
            $cantidad = $fila['cantidad'] ?? '';

            if ($idCatalogoEquipo === '' || $cantidad === '') {
                continue;
            }

            $normalizadas[] = [
                'id_catalogo_equipo' => (int) $idCatalogoEquipo,
                'cantidad'           => (int) $cantidad,
            ];
        }

        return $normalizadas;
    }

    private function validarEquiposUtilizados(array $equiposUtilizados): ?string
    {
        if (empty($equiposUtilizados)) {
            return 'Debes agregar al menos un equipo utilizado.';
        }

        foreach ($equiposUtilizados as $fila) {
            if ($fila['id_catalogo_equipo'] <= 0) {
                return 'Seleccionaste un equipo inválido en la lista de equipos utilizados.';
            }

            if ($fila['cantidad'] <= 0) {
                return 'La cantidad de cada equipo utilizado debe ser mayor a 0.';
            }
        }

        return null;
    }

    private function validarReglasDeNegocio(array $datos): ?string
    {
        if (!empty($datos['fecha_regreso']) && $datos['fecha_regreso'] < $datos['fecha_salida']) {
            return 'La fecha de regreso no puede ser anterior a la fecha de salida.';
        }

        foreach (['costo', 'pago_1', 'pago_2'] as $campoMonto) {
            $valor = $datos[$campoMonto] ?? 0;
            if ($valor !== '' && (!is_numeric($valor) || (float) $valor < 0)) {
                return "El campo '$campoMonto' debe ser un número igual o mayor a 0.";
            }
        }

        if (!in_array($datos['estado'], self::ESTADOS_VALIDOS, true)) {
            return 'El estado enviado no es válido.';
        }

        return null;
    }
}