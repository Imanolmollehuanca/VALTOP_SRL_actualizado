<?php

require_once __DIR__ . '/../Models/Trabajo.php';
require_once __DIR__ . '/../Models/Cliente.php';
require_once __DIR__ . '/../Models/CostoFinanciero.php';

/**
 * Clase TrabajoController
 * -----------------------------------------------------
 * Responsable única: recibir las acciones del usuario
 * relacionadas al módulo de Trabajos y coordinarlas con
 * el Modelo Trabajo.
 *
 * No contiene SQL. No contiene HTML. Solo lógica de
 * coordinación y validación de negocio.
 * -----------------------------------------------------
 */
class TrabajoController
{
    // Única fuente de verdad de los estados válidos.
    // Se usa tanto para validar como para pintar los tabs en la Vista.
    public const ESTADOS_VALIDOS = ['Pendiente', 'Terminado', 'Cobrado', 'Fracaso'];

    private Trabajo $trabajoModel;

    public function __construct()
    {
        $this->trabajoModel = new Trabajo();
    }

    /**
     * Devuelve el listado de trabajos, aplicando los filtros
     * que hayan llegado desde la Vista (tabs, dropdown, buscador).
     */
    public function listar(
        ?string $estado = null,
        ?int $idResponsable = null,
        ?string $busqueda = null
    ): array {
        // Si el filtro de estado es "Todos" (o vacío), lo tratamos
        // como null para que el Modelo no aplique esa condición.
        if ($estado === 'Todos' || $estado === '') {
            $estado = null;
        }

        return $this->trabajoModel->listar($estado, $idResponsable, $busqueda);
    }

    /**
     * Registra un nuevo trabajo a partir de los datos
     * enviados desde el formulario ($_POST).
     */
    public function registrar(array $datosFormulario): array
    {
        $camposObligatorios = [
            'cliente', 'proyecto', 'descripcion', 'ubicacion',
            'id_responsable', 'precio_neto', 'fecha_inicio', 'fecha_fin'
        ];

        foreach ($camposObligatorios as $campo) {
            if (empty($datosFormulario[$campo]) && $datosFormulario[$campo] !== '0') {
                return [
                    'exito'   => false,
                    'mensaje' => "El campo '$campo' es obligatorio.",
                ];
            }
        }

        $errorValidacion = $this->validarReglasDeNegocio($datosFormulario);
        if ($errorValidacion !== null) {
            return ['exito' => false, 'mensaje' => $errorValidacion];
        }

        $clienteModel = new Cliente();

        $cliente = $clienteModel->buscarPorNombre(
            trim($datosFormulario['cliente'])
        );

        if ($cliente) {

            $datosFormulario['id_cliente'] = $cliente['id_cliente'];

        } else {

            $nuevoId = $clienteModel->crear([  

                'nombre_cliente' => trim($datosFormulario['cliente']),
                'ruc' => '',
                'telefono' => '',
                'correo' => '',
                'observaciones' => ''

            ]);

            $datosFormulario['id_cliente'] = $nuevoId;
        }

        $datosFormulario['codigo_trabajo'] = $this->trabajoModel->generarSiguienteCodigo();
        $datosFormulario['fecha_registro'] = date('Y-m-d');
        $datosFormulario['estado'] = $datosFormulario['estado'] ?? 'Pendiente';

        $idTrabajo = $this->trabajoModel->crear($datosFormulario);

        if ($idTrabajo !== false) {

            $costo = new CostoFinanciero();
            $costo->asegurarRegistro($idTrabajo);

            return [
                'exito'   => true,
                'mensaje' => 'Trabajo registrado correctamente.',
            ];
        }

        return [
            'exito'   => false,
            'mensaje' => 'Ocurrió un error al registrar el trabajo.',
        ];
    }

    /**
     * Actualiza un trabajo existente.
     */
    public function actualizar(int $idTrabajo, array $datosFormulario): array
    {
        $trabajoExistente = $this->trabajoModel->buscarPorId($idTrabajo);

        if ($trabajoExistente === null) {
            return [
                'exito'   => false,
                'mensaje' => 'El trabajo que intentas editar no existe.',
            ];
        }

        $errorValidacion = $this->validarReglasDeNegocio($datosFormulario);
        if ($errorValidacion !== null) {
            return ['exito' => false, 'mensaje' => $errorValidacion];
        }

        $clienteModel = new Cliente();
        $cliente = $clienteModel->buscarPorNombre(
            trim($datosFormulario['cliente'])
        );

        if ($cliente) {

            $datosFormulario['id_cliente'] = $cliente['id_cliente'];

        } else {

            $nuevoId = $clienteModel->crear([
                'nombre_cliente' => trim($datosFormulario['cliente']),
                'ruc' => '',
                'telefono' => '',
                'correo' => '',
                'observaciones' => ''
            ]);

            $datosFormulario['id_cliente'] = $nuevoId;
        }

        $actualizado = $this->trabajoModel->actualizar($idTrabajo, $datosFormulario);

        return [
            'exito'   => $actualizado,
            'mensaje' => $actualizado
                ? 'Trabajo actualizado correctamente.'
                : 'Ocurrió un error al actualizar el trabajo.',
        ];
    }

    /**
     * Devuelve los datos de un trabajo específico,
     * usado para el formulario de edición y el Expediente.
     */
    public function verDetalle(int $idTrabajo): ?array
    {
        return $this->trabajoModel->buscarPorId($idTrabajo);
    }

    /**
     * Cambia únicamente el estado de un trabajo.
     */
    public function cambiarEstado(int $idTrabajo, string $nuevoEstado): array
    {
        if (!in_array($nuevoEstado, self::ESTADOS_VALIDOS, true)) {
            return [
                'exito'   => false,
                'mensaje' => 'El estado enviado no es válido.',
            ];
        }

        $cambiado = $this->trabajoModel->cambiarEstado($idTrabajo, $nuevoEstado);

        return [
            'exito'   => $cambiado,
            'mensaje' => $cambiado
                ? 'Estado actualizado correctamente.'
                : 'Ocurrió un error al cambiar el estado.',
        ];
    }

    /**
     * Devuelve sugerencias de trabajos para el campo de
     * autocompletado (usado por Viáticos y futuros módulos
     * que necesiten seleccionar un trabajo por texto libre).
     * Si el texto viene vacío, no consulta nada (evita traer
     * los 10 más recientes sin que el usuario haya escrito algo).
     */
    public function buscarParaAutocompletado(string $texto): array
    {
        if (trim($texto) === '') {
            return [];
        }

        return $this->trabajoModel->buscarParaAutocompletado($texto);
    }
    
    public function buscarAutocompletado(string $texto): array
    { 
    return $this->trabajoModel->buscarAutocompletado($texto);
    }

    /**
     * Reglas de negocio compartidas entre registrar() y actualizar().
     * Devuelve null si todo está bien, o un mensaje de error si algo falla.
     *
     * Centralizarlas aquí evita repetir el mismo if/else en dos métodos
     * y asegura que registrar y editar validen exactamente lo mismo.
     */
    private function validarReglasDeNegocio(array $datos): ?string
    {
        if ($datos['fecha_inicio'] > $datos['fecha_fin']) {
            return 'La fecha de inicio no puede ser posterior a la fecha de fin.';
        }

        if (!is_numeric($datos['precio_neto']) || (float) $datos['precio_neto'] < 0) {
            return 'El precio neto debe ser un número igual o mayor a 0.';
        }

        return null;
    }

    /**
     * Devuelve sugerencias de clientes para el autocompletado.
     */
    public function buscarClientes(string $texto): array
    {
        $clienteModel = new Cliente();

        return $clienteModel->buscarPorCoincidencia($texto);
    }
    /**
     * Elimina un trabajo.
     */
    public function eliminar(int $idTrabajo): array
    {
        $trabajo = $this->trabajoModel->buscarPorId($idTrabajo);

        if ($trabajo === null) {
            return [
                'exito' => false,
                'mensaje' => 'El trabajo no existe.'
            ];
        }

        if ($this->trabajoModel->tieneEquipos($idTrabajo)) {
            return [
                'exito' => false,
                'mensaje' => 'No es posible eliminar este trabajo porque tiene equipos asociados.'
            ];
        }

        $eliminado = $this->trabajoModel->eliminar($idTrabajo);

        return [
            'exito' => $eliminado,
            'mensaje' => $eliminado
                ? 'Trabajo eliminado correctamente.'
                : 'No fue posible eliminar el trabajo.'
        ];
    }
}