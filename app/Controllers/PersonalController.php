<?php

require_once __DIR__ . '/../Models/Personal.php';

/**
 * Clase PersonalController
 * -----------------------------------------------------
 * Responsable única: coordinar las acciones del módulo
 * de Personal con el Modelo Personal.
 *
 * No contiene SQL. No contiene HTML.
 * -----------------------------------------------------
 */
class PersonalController
{
    public const ESTADOS_VALIDOS = ['Activo', 'Inactivo', 'Vacaciones'];

    public const CARGOS_VALIDOS = [
        'Topógrafo', 'Ayudante', 'Dibujante', 'Cadista', 'Ingeniero',
        'Supervisor', 'Chofer', 'Administrativo', 'Otro',
    ];

    private Personal $personalModel;

    public function __construct()
    {
        $this->personalModel = new Personal();
    }

    public function listar(?string $estado = null): array
    {
        if ($estado === 'Todos' || $estado === '') {
            $estado = null;
        }

        return $this->personalModel->listarPorEstado($estado);
    }

    public function verDetalle(int $idPersonal): ?array
    {
        return $this->personalModel->buscarPorId($idPersonal);
    }

    public function registrar(array $datosFormulario): array
    {
        $camposObligatorios = ['nombre_completo', 'telefono_principal', 'cargo', 'id_responsable'];

        foreach ($camposObligatorios as $campo) {
            if (empty($datosFormulario[$campo])) {
                return [
                    'exito'   => false,
                    'mensaje' => "El campo '$campo' es obligatorio.",
                ];
            }
        }

        $datosFormulario['estado'] = $datosFormulario['estado'] ?? 'Activo';

        $errorValidacion = $this->validarReglasDeNegocio($datosFormulario);
        if ($errorValidacion !== null) {
            return ['exito' => false, 'mensaje' => $errorValidacion];
        }

        $creado = $this->personalModel->crear($datosFormulario);

        return [
            'exito'   => $creado,
            'mensaje' => $creado
                ? 'Personal guardado correctamente.'
                : 'Ocurrió un error al guardar el registro de personal.',
        ];
    }

    public function actualizar(int $idPersonal, array $datosFormulario): array
    {
        $personalExistente = $this->personalModel->buscarPorId($idPersonal);

        if ($personalExistente === null) {
            return [
                'exito'   => false,
                'mensaje' => 'El registro que intentas editar no existe.',
            ];
        }

        $errorValidacion = $this->validarReglasDeNegocio($datosFormulario);
        if ($errorValidacion !== null) {
            return ['exito' => false, 'mensaje' => $errorValidacion];
        }

        $actualizado = $this->personalModel->actualizar($idPersonal, $datosFormulario);

        return [
            'exito'   => $actualizado,
            'mensaje' => $actualizado
                ? 'Personal actualizado correctamente.'
                : 'Ocurrió un error al actualizar el registro.',
        ];
    }

    public function eliminar(int $idPersonal): array
    {
        $eliminado = $this->personalModel->eliminar($idPersonal);

        return [
            'exito'   => $eliminado,
            'mensaje' => $eliminado
                ? 'Registro eliminado correctamente.'
                : 'Ocurrió un error al eliminar el registro.',
        ];
    }

    private function validarReglasDeNegocio(array $datos): ?string
    {
        if (!in_array($datos['cargo'], self::CARGOS_VALIDOS, true)) {
            return 'El cargo seleccionado no es válido.';
        }

        if (!in_array($datos['estado'], self::ESTADOS_VALIDOS, true)) {
            return 'El estado enviado no es válido.';
        }

        return null;
    }
}