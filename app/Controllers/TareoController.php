<?php

require_once __DIR__ . '/../Models/Tareo.php';

/**
 * Clase TareoController
 * -----------------------------------------------------
 * Responsable única: coordinar las acciones del módulo
 * de Tareo con el Modelo Tareo.
 *
 * No contiene SQL. No contiene HTML. Solo valida y
 * coordina, igual que TrabajoController y PersonalController.
 * -----------------------------------------------------
 */
class TareoController
{
    public const ACTIVIDADES_VALIDAS = ['Campo', 'Dibujo', 'Falto', 'Vacaciones'];
    private Tareo $tareoModel;

    public function __construct()
    {
        $this->tareoModel = new Tareo();
    }

    public function listar(): array
    {
        return $this->tareoModel->listar();
    }

    public function verDetalle(int $idTareo): ?array
    {
        return $this->tareoModel->buscarPorId($idTareo);
    }

    public function registrar(array $datosFormulario): array
    {
        $camposObligatorios = ['id_trabajo', 'id_personal', 'fecha', 'actividad'];

        foreach ($camposObligatorios as $campo) {
            if (empty($datosFormulario[$campo])) {
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

        $creado = $this->tareoModel->crear($datosFormulario);

        return [
            'exito'   => $creado,
            'mensaje' => $creado
                ? 'Tareo registrado correctamente.'
                : 'Ocurrió un error al registrar el tareo.',
        ];
    }

    public function actualizar(int $idTareo, array $datosFormulario): array
    {
        $tareoExistente = $this->tareoModel->buscarPorId($idTareo);

        if ($tareoExistente === null) {
            return [
                'exito'   => false,
                'mensaje' => 'El registro de tareo que intentas editar no existe.',
            ];
        }

        $errorValidacion = $this->validarReglasDeNegocio($datosFormulario);
        if ($errorValidacion !== null) {
            return ['exito' => false, 'mensaje' => $errorValidacion];
        }

        $actualizado = $this->tareoModel->actualizar($idTareo, $datosFormulario);

        return [
            'exito'   => $actualizado,
            'mensaje' => $actualizado
                ? 'Tareo actualizado correctamente.'
                : 'Ocurrió un error al actualizar el tareo.',
        ];
    }

    public function eliminar(int $idTareo): array
    {
        $eliminado = $this->tareoModel->eliminar($idTareo);

        return [
            'exito'   => $eliminado,
            'mensaje' => $eliminado
                ? 'Registro de tareo eliminado correctamente.'
                : 'Ocurrió un error al eliminar el registro.',
        ];
    }

    private function validarReglasDeNegocio(array $datos): ?string
    {
        if (!in_array($datos['actividad'], self::ACTIVIDADES_VALIDAS, true)) {
            return 'La actividad seleccionada no es válida.';
        }

        return null;
    }
}