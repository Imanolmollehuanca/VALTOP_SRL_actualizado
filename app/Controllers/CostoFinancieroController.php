<?php

require_once __DIR__ . '/../Models/CostoFinanciero.php';

/**
 * Clase CostoFinancieroController
 * -----------------------------------------------------
 * Responsable única: coordinar las acciones del módulo
 * de Costo Financiero con el Modelo CostoFinanciero.
 *
 * No contiene SQL. No contiene HTML. No conoce la fórmula
 * de cálculo (esa vive únicamente en el Modelo).
 * -----------------------------------------------------
 */
class CostoFinancieroController
{
    private CostoFinanciero $costoFinancieroModel;

    public function __construct()
    {
        $this->costoFinancieroModel = new CostoFinanciero();
    }

    public function listar(): array
    {
        return $this->costoFinancieroModel->listar();
    }

    public function verDetalle(int $idTrabajo): ?array
    {
        return $this->costoFinancieroModel->buscarPorId($idTrabajo);
    }

    public function recalcular(): array
    {
        $this->costoFinancieroModel->recalcularTodos();

        return [
            'exito'   => true,
            'mensaje' => 'Costo financiero recalculado correctamente.',
        ];
    }

    public function actualizar(int $idTrabajo, array $datosFormulario): array
    {
        $trabajoExistente = $this->costoFinancieroModel->buscarPorId($idTrabajo);

        if ($trabajoExistente === null) {
            return [
                'exito'   => false,
                'mensaje' => 'El trabajo indicado no existe o no tiene costo financiero calculado todavía.',
            ];
        }

        $errorValidacion = $this->validarReglasDeNegocio($datosFormulario);
        if ($errorValidacion !== null) {
            return ['exito' => false, 'mensaje' => $errorValidacion];
        }

        $actualizado = $this->costoFinancieroModel->actualizar($idTrabajo, $datosFormulario);

        return [
            'exito'   => $actualizado,
            'mensaje' => $actualizado
                ? 'Costo financiero actualizado correctamente.'
                : 'Ocurrió un error al actualizar el costo financiero.',
        ];
    }

    private function validarReglasDeNegocio(array $datos): ?string
    {
        $porcentaje = $datos['porcentaje_financiero'] ?? null;

        if ($porcentaje !== null && $porcentaje !== '' && (!is_numeric($porcentaje) || (float) $porcentaje < 0)) {
            return 'El porcentaje financiero debe ser un número igual o mayor a 0.';
        }

        if (!empty($datos['fecha_factura']) && !empty($datos['fecha_cobro'])) {
            if ($datos['fecha_factura'] > $datos['fecha_cobro']) {
                return 'La fecha de factura no puede ser posterior a la fecha de cobro.';
            }
        }

        return null;
    }
}