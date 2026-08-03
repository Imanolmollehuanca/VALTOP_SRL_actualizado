<?php

require_once __DIR__ . '/../Models/Reporte.php';

/**
 * Clase ReporteController
 * -----------------------------------------------------
 * Responsable única: coordinar las acciones del módulo
 * de Reportes con el Modelo Reporte.
 *
 * Módulo de solo lectura: no registra, no edita, no
 * elimina nada. Solo consulta y presenta.
 * -----------------------------------------------------
 */
class ReporteController
{
    private Reporte $reporteModel;

    public function __construct()
    {
        $this->reporteModel = new Reporte();
    }

    public function listar(array $filtros = []): array
    {
        return $this->reporteModel->listar($filtros);
    }

    public function verDetalle(int $idTrabajo): ?array
    {
        return $this->reporteModel->buscarPorId($idTrabajo);
    }

    public function resumen(array $filas): array
    {
        $totalTrabajos    = count($filas);
        $totalFacturado   = 0.0;
        $capitalInvertido = 0.0;
        $utilidadTotal    = 0.0;
        $trabajosCobrados = 0;

        foreach ($filas as $fila) {
            $totalFacturado   += (float) $fila['precio_neto'];
            $capitalInvertido += (float) $fila['capital_invertido'];
            $utilidadTotal    += (float) $fila['utilidad'];

            if ($fila['estado'] === 'Cobrado') {
                $trabajosCobrados++;
            }
        }

        return [
            'total_trabajos'    => $totalTrabajos,
            'total_facturado'   => $totalFacturado,
            'capital_invertido' => $capitalInvertido,
            'utilidad_total'    => $utilidadTotal,
            'trabajos_cobrados' => $trabajosCobrados,
        ];
    }
}