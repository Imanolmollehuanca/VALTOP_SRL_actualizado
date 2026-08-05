<?php
/**
 * Vista: Impresión de Reportes (exclusiva para impresión)
 * -----------------------------------------------------
 * Variables esperadas (idénticas a las que ya recibía
 * reportes/listado.php, más 2 nuevas):
 *
 * array  $filas             Trabajos con montos ya calculados
 * array  $resumen           Totales (de ReporteController::resumen())
 * array  $responsables      Para resolver el nombre del filtro
 * array  $filtrosActuales   ['fecha_desde','fecha_hasta','id_responsable','estado']
 * string $nombreUsuario     Usuario que genera el reporte
 *
 * Esta vista NO tiene interfaz de sistema: sin botón "Volver",
 * sin Exportar Excel/PDF, sin formulario de filtros, sin
 * tarjetas de dashboard. Solo el documento listo para imprimir.
 * -----------------------------------------------------
 */

function montoImpresion($valor): string
{
    return number_format((float) $valor, 2);
}

function claseBadgeEstadoImpresion(string $estado): string
{
    $mapa = [
        'Pendiente' => 'badge-pendiente',
        'Terminado' => 'badge-terminado',
        'Cobrado'   => 'badge-cobrado',
        'Fracaso'   => 'badge-fracaso',
    ];

    return $mapa[$estado] ?? 'badge-default';
}

/**
 * Resuelve el nombre del responsable filtrado, o "Todos" si no
 * se filtró por ninguno. Se calcula aquí (no en el Controller)
 * porque es puramente texto para mostrar en el papel.
 */
function nombreResponsableFiltro(array $responsables, $idResponsable): string
{
    if (empty($idResponsable)) {
        return 'Todos';
    }

    foreach ($responsables as $responsable) {
        if ((string) $responsable['id_usuario'] === (string) $idResponsable) {
            return $responsable['nombre_usuario'];
        }
    }

    return 'Todos';
}

/**
 * Resumen General del papel: reutiliza los totales que ya trae
 * $resumen (total_trabajos, total_facturado, capital_invertido,
 * utilidad_total) y agrega el costo_financiero total y los
 * conteos por estado, calculados aquí mismo recorriendo $filas.
 * No requiere ningún método nuevo en Reporte.php ni en
 * ReporteController.php.
 */
$costoFinancieroTotal = 0.0;
$conteoPorEstado = [
    'Pendiente' => 0,
    'Terminado' => 0,
    'Cobrado'   => 0,
    'Fracaso'   => 0,
];

foreach ($filas as $fila) {
    $costoFinancieroTotal += (float) $fila['costo_financiero'];

    if (array_key_exists($fila['estado'], $conteoPorEstado)) {
        $conteoPorEstado[$fila['estado']]++;
    }
}

$periodoTexto = 'Todos';
if (!empty($filtrosActuales['fecha_desde']) || !empty($filtrosActuales['fecha_hasta'])) {
    $desde = !empty($filtrosActuales['fecha_desde'])
        ? date('d/m/Y', strtotime($filtrosActuales['fecha_desde']))
        : '—';
    $hasta = !empty($filtrosActuales['fecha_hasta'])
        ? date('d/m/Y', strtotime($filtrosActuales['fecha_hasta']))
        : '—';
    $periodoTexto = $desde . ' - ' . $hasta;
}

$responsableTexto = nombreResponsableFiltro($responsables, $filtrosActuales['id_responsable'] ?? null);
$estadoTexto = !empty($filtrosActuales['estado']) ? $filtrosActuales['estado'] : 'Todos';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Trabajos - Valtop SRL</title>
    <link rel="stylesheet" href="/assets/css/trabajos.css">
</head>
<body class="cuerpo-impresion">

<div class="impresion-acciones">
    <button type="button" class="btn btn-primario" onclick="window.print()">🖨️ Imprimir</button>
    <button type="button" class="btn btn-secundario" onclick="window.close()">Cerrar</button>
</div>

<main class="pagina-impresion">

    <div class="impresion-encabezado">
        <div class="impresion-marca">
            <div class="impresion-logo">
                <svg viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                    <polygon points="20,4 36,34 4,34" fill="#2563EB"/>
                    <polygon points="20,14 28,30 12,30" fill="#FFFFFF"/>
                </svg>
            </div>
            <div>
                <div class="impresion-empresa">VALTOP SRL</div>
                <div class="impresion-empresa-subtitulo">Sistema de Gestión de Trabajos</div>
            </div>
        </div>

        <div class="impresion-meta">
            <span>Fecha de generación: <strong><?= date('d/m/Y H:i') ?></strong></span>
            <span>Usuario: <strong><?= htmlspecialchars($nombreUsuario) ?></strong></span>
        </div>
    </div>

    <h1 class="impresion-titulo-reporte">REPORTE DE TRABAJOS</h1>

    <div class="impresion-filtros">
        <span>Período: <strong><?= htmlspecialchars($periodoTexto) ?></strong></span>
        <span>Responsable: <strong><?= htmlspecialchars($responsableTexto) ?></strong></span>
        <span>Estado: <strong><?= htmlspecialchars($estadoTexto) ?></strong></span>
    </div>

    <table class="tabla-impresion">
        <thead>
            <tr>
                <th>N° Trabajo</th>
                <th>Cliente</th>
                <th>Proyecto</th>
                <th>Responsable</th>
                <th>Precio Neto (S/)</th>
                <th>Capital Invertido (S/)</th>
                <th>Costo Financiero (S/)</th>
                <th>Utilidad (S/)</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($filas)): ?>
                <tr>
                    <td colspan="9" class="sin-datos">No hay trabajos para los filtros seleccionados.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($filas as $fila): ?>
                    <tr>
                        <td><?= htmlspecialchars($fila['codigo_trabajo']) ?></td>
                        <td><?= htmlspecialchars($fila['nombre_cliente'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($fila['proyecto']) ?></td>
                        <td><?= htmlspecialchars($fila['nombre_responsable'] ?? '—') ?></td>
                        <td class="columna-precio">S/ <?= montoImpresion($fila['precio_neto']) ?></td>
                        <td class="columna-precio">S/ <?= montoImpresion($fila['capital_invertido']) ?></td>
                        <td class="columna-precio">S/ <?= montoImpresion($fila['costo_financiero']) ?></td>
                        <td class="columna-precio">S/ <?= montoImpresion($fila['utilidad']) ?></td>
                        <td>
                            <span class="badge <?= claseBadgeEstadoImpresion($fila['estado']) ?>">
                                <?= htmlspecialchars($fila['estado']) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="impresion-resumen">
        <div class="impresion-resumen-fila">
            <span>Total de Trabajos</span>
            <strong><?= (int) $resumen['total_trabajos'] ?></strong>
        </div>
        <div class="impresion-resumen-fila">
            <span>Total Facturado</span>
            <strong>S/ <?= montoImpresion($resumen['total_facturado']) ?></strong>
        </div>
        <div class="impresion-resumen-fila">
            <span>Capital Invertido</span>
            <strong>S/ <?= montoImpresion($resumen['capital_invertido']) ?></strong>
        </div>
        <div class="impresion-resumen-fila">
            <span>Costo Financiero</span>
            <strong>S/ <?= montoImpresion($costoFinancieroTotal) ?></strong>
        </div>
        <div class="impresion-resumen-fila">
            <span>Utilidad Total</span>
            <strong>S/ <?= montoImpresion($resumen['utilidad_total']) ?></strong>
        </div>
        <div class="impresion-resumen-fila">
            <span>Trabajos Pendientes</span>
            <strong><?= (int) $conteoPorEstado['Pendiente'] ?></strong>
        </div>
        <div class="impresion-resumen-fila">
            <span>Trabajos Terminados</span>
            <strong><?= (int) $conteoPorEstado['Terminado'] ?></strong>
        </div>
        <div class="impresion-resumen-fila">
            <span>Trabajos Cobrados</span>
            <strong><?= (int) $conteoPorEstado['Cobrado'] ?></strong>
        </div>
        <div class="impresion-resumen-fila">
            <span>Trabajos Fracasados</span>
            <strong><?= (int) $conteoPorEstado['Fracaso'] ?></strong>
        </div>
    </div>

    <div class="impresion-leyenda">
        <span class="leyenda-item"><span class="punto badge-pendiente"></span> Pendiente</span>
        <span class="leyenda-item"><span class="punto badge-terminado"></span> Terminado</span>
        <span class="leyenda-item"><span class="punto badge-cobrado"></span> Cobrado</span>
        <span class="leyenda-item"><span class="punto badge-fracaso"></span> Fracaso</span>
    </div>

    <div class="impresion-pie">
        VALTOP SRL — Sistema de Gestión de Trabajos · Página 1 de 1
    </div>

</main>

</body>
</html>