<?php

require_once __DIR__ . '/../Models/Viatico.php';

/**
 * Clase ViaticoController
 * -----------------------------------------------------
 * Responsable única: coordinar las acciones del módulo
 * de Viáticos con el Modelo Viatico.
 *
 * No contiene SQL. No contiene HTML.
 * -----------------------------------------------------
 */
class ViaticoController
{
    public const CONCEPTOS_VALIDOS = [
        'Alimentación', 'Hospedaje', 'Agua', 'Movilidad',
        'Peajes', 'Combustible', 'Pasajes', 'Otros',
    ];

    public const ESTADOS_VALIDOS = ['Pendiente', 'Pagado', 'Anulado'];

    private Viatico $viaticoModel;

    public function __construct()
    {
        $this->viaticoModel = new Viatico();
    }

    public function listar(): array
    {
        return $this->viaticoModel->listar();
    }

    public function verDetalle(int $idViatico): ?array
    {
        return $this->viaticoModel->buscarPorId($idViatico);
    }

    public function verDetalleConTrabajo(int $idViatico): ?array
    {
        return $this->viaticoModel->buscarPorIdConTrabajo($idViatico);
    }

    public function registrar(array $datosFormulario): array
    {
        $camposObligatorios = ['id_trabajo', 'fecha', 'concepto', 'descripcion', 'monto'];

        foreach ($camposObligatorios as $campo) {
            if (empty($datosFormulario[$campo]) && $datosFormulario[$campo] !== '0') {
                return [
                    'exito'   => false,
                    'mensaje' => "El campo '$campo' es obligatorio.",
                ];
            }
        }

        $datosFormulario['estado'] = $datosFormulario['estado'] ?? 'Pendiente';

        $errorValidacion = $this->validarReglasDeNegocio($datosFormulario);
        if ($errorValidacion !== null) {
            return ['exito' => false, 'mensaje' => $errorValidacion];
        }

        $creado = $this->viaticoModel->crear($datosFormulario);

        return [
            'exito'   => $creado,
            'mensaje' => $creado
                ? 'Viático registrado correctamente.'
                : 'Ocurrió un error al registrar el viático.',
        ];
    }

    public function actualizar(int $idViatico, array $datosFormulario): array
    {
        $viaticoExistente = $this->viaticoModel->buscarPorId($idViatico);

        if ($viaticoExistente === null) {
            return [
                'exito'   => false,
                'mensaje' => 'El viático que intentas editar no existe.',
            ];
        }

        $errorValidacion = $this->validarReglasDeNegocio($datosFormulario);
        if ($errorValidacion !== null) {
            return ['exito' => false, 'mensaje' => $errorValidacion];
        }

        $actualizado = $this->viaticoModel->actualizar($idViatico, $datosFormulario);

        return [
            'exito'   => $actualizado,
            'mensaje' => $actualizado
                ? 'Viático actualizado correctamente.'
                : 'Ocurrió un error al actualizar el viático.',
        ];
    }

    public function eliminar(int $idViatico): array
    {
        $eliminado = $this->viaticoModel->eliminar($idViatico);

        return [
            'exito'   => $eliminado,
            'mensaje' => $eliminado
                ? 'Viático eliminado correctamente.'
                : 'Ocurrió un error al eliminar el viático.',
        ];
    }

    private function validarReglasDeNegocio(array $datos): ?string
    {
        if (!in_array($datos['concepto'], self::CONCEPTOS_VALIDOS, true)) {
            return 'El concepto seleccionado no es válido.';
        }

        if (!in_array($datos['estado'], self::ESTADOS_VALIDOS, true)) {
            return 'El estado enviado no es válido.';
        }

        if (!is_numeric($datos['monto']) || (float) $datos['monto'] < 0) {
            return 'El monto debe ser un número igual o mayor a 0.';
        }

        return null;
    }
}