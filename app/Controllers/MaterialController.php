<?php

require_once __DIR__ . '/../Models/Material.php';
require_once __DIR__ . '/../Models/MaterialTrabajo.php';

/**
 * Clase MaterialController
 * -----------------------------------------------------
 * Responsable única: coordinar las acciones del módulo
 * Materiales entre los Modelos Material (catálogo) y
 * MaterialTrabajo (detalle por trabajo).
 *
 * No contiene SQL. No contiene HTML.
 * -----------------------------------------------------
 */
class MaterialController
{
    private Material $materialModel;
    private MaterialTrabajo $materialTrabajoModel;

    public function __construct()
    {
        $this->materialModel = new Material();
        $this->materialTrabajoModel = new MaterialTrabajo();
    }

    public function listarResumen(): array
    {
        return $this->materialTrabajoModel->listarResumenPorTrabajo();
    }

    public function listarCatalogo(): array
    {
        return $this->materialModel->listar();
    }

    public function listarMaterialesDeTrabajo(int $idTrabajo): array
    {
        return $this->materialTrabajoModel->listarPorTrabajo($idTrabajo);
    }

    public function costoTotalDeTrabajo(int $idTrabajo): float
    {
        return $this->materialTrabajoModel->costoTotalPorTrabajo($idTrabajo);
    }

    public function verDetalle(int $idTrabajoMaterial): ?array
    {
        return $this->materialTrabajoModel->buscarPorId($idTrabajoMaterial);
    }

    public function obtenerOCrearIdMaterialPorNombre(string $nombreMaterial): int|false
    {
        $nombre = trim($nombreMaterial);

        if ($nombre === '') {
            return false;
        }

        $existente = $this->materialModel->buscarPorNombre($nombre);

        if ($existente !== null) {
            return (int) $existente['id_material'];
        }

        return $this->materialModel->crear($nombre);
    }

    public function registrar(int $idTrabajo, array $datosFormulario): array
    {
        $camposObligatorios = ['id_material', 'cantidad', 'unidad', 'precio_unitario'];

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

        $datosFormulario['id_trabajo'] = $idTrabajo;

        $creado = $this->materialTrabajoModel->crear($datosFormulario);

        return [
            'exito'   => $creado,
            'mensaje' => $creado
                ? 'Material registrado correctamente.'
                : 'Ocurrió un error al registrar el material.',
        ];
    }

    public function actualizar(int $idTrabajoMaterial, array $datosFormulario): array
    {
        $registroExistente = $this->materialTrabajoModel->buscarPorId($idTrabajoMaterial);

        if ($registroExistente === null) {
            return [
                'exito'   => false,
                'mensaje' => 'El registro de material que intentas editar no existe.',
            ];
        }

        $errorValidacion = $this->validarReglasDeNegocio($datosFormulario);
        if ($errorValidacion !== null) {
            return ['exito' => false, 'mensaje' => $errorValidacion];
        }

        $actualizado = $this->materialTrabajoModel->actualizar($idTrabajoMaterial, $datosFormulario);

        return [
            'exito'   => $actualizado,
            'mensaje' => $actualizado
                ? 'Material actualizado correctamente.'
                : 'Ocurrió un error al actualizar el material.',
        ];
    }

    public function eliminar(int $idTrabajoMaterial): array
    {
        $eliminado = $this->materialTrabajoModel->eliminar($idTrabajoMaterial);

        return [
            'exito'   => $eliminado,
            'mensaje' => $eliminado
                ? 'Material eliminado correctamente.'
                : 'Ocurrió un error al eliminar el material.',
        ];
    }

    public function eliminarTodosDeTrabajo(int $idTrabajo): array
    {
        $eliminado = $this->materialTrabajoModel->eliminarTodosDeTrabajo($idTrabajo);

        return [
            'exito'   => $eliminado,
            'mensaje' => $eliminado
                ? 'Se eliminaron todos los materiales de este trabajo.'
                : 'Ocurrió un error al eliminar los materiales del trabajo.',
        ];
    }

    private function validarReglasDeNegocio(array $datos): ?string
    {
        if (!is_numeric($datos['cantidad']) || (float) $datos['cantidad'] <= 0) {
            return 'La cantidad debe ser un número mayor a 0.';
        }

        if (!is_numeric($datos['precio_unitario']) || (float) $datos['precio_unitario'] < 0) {
            return 'El precio unitario debe ser un número igual o mayor a 0.';
        }

        return null;
    }
}