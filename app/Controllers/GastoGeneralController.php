<?php

require_once __DIR__ . '/../Models/GastoGeneral.php';

/**
 * Clase GastoGeneralController
 * -----------------------------------------------------
 * Responsable única: coordinar las acciones del módulo
 * Gastos Generales. No contiene SQL. No contiene HTML.
 * -----------------------------------------------------
 */
class GastoGeneralController
{
    private GastoGeneral $gastoModel;

    public function __construct()
    {
        $this->gastoModel = new GastoGeneral();
    }

    public function listar(?string $busqueda = null): array
    {
        return $this->gastoModel->listar($busqueda);
    }

    public function verDetalle(int $idGasto): ?array
    {
        return $this->gastoModel->buscarPorId($idGasto);
    }

    public function total(): float
    {
        return $this->gastoModel->total();
    }

    public function registrar(array $datosFormulario): array
    {
        $camposObligatorios = ['concepto', 'fecha', 'monto'];

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

        $creado = $this->gastoModel->crear($datosFormulario);

        return [
            'exito'   => $creado,
            'mensaje' => $creado
                ? 'Gasto registrado correctamente.'
                : 'Ocurrió un error al registrar el gasto.',
        ];
    }

    public function actualizar(int $idGasto, array $datosFormulario): array
    {
        $gastoExistente = $this->gastoModel->buscarPorId($idGasto);

        if ($gastoExistente === null) {
            return [
                'exito'   => false,
                'mensaje' => 'El gasto que intentas editar no existe.',
            ];
        }

        $errorValidacion = $this->validarReglasDeNegocio($datosFormulario);
        if ($errorValidacion !== null) {
            return ['exito' => false, 'mensaje' => $errorValidacion];
        }

        $actualizado = $this->gastoModel->actualizar($idGasto, $datosFormulario);

        return [
            'exito'   => $actualizado,
            'mensaje' => $actualizado
                ? 'Gasto actualizado correctamente.'
                : 'Ocurrió un error al actualizar el gasto.',
        ];
    }

    public function eliminar(int $idGasto): array
    {
        $eliminado = $this->gastoModel->eliminar($idGasto);

        return [
            'exito'   => $eliminado,
            'mensaje' => $eliminado
                ? 'Gasto eliminado correctamente.'
                : 'Ocurrió un error al eliminar el gasto.',
        ];
    }

    public function vaciarLista(): array
    {
        $vaciado = $this->gastoModel->eliminarTodos();

        return [
            'exito'   => $vaciado,
            'mensaje' => $vaciado
                ? 'Se eliminaron todos los gastos generales.'
                : 'Ocurrió un error al vaciar la lista.',
        ];
    }

    private function validarReglasDeNegocio(array $datos): ?string
    {
        if (!is_numeric($datos['monto']) || (float) $datos['monto'] < 0) {
            return 'El monto debe ser un número igual o mayor a 0.';
        }

        return null;
    }
}