<?php
/**
 * Vista: Detalle del Reporte (solo lectura)
 * -----------------------------------------------------
 * array $fila  Trabajo con todos los montos calculados,
 *              tal como lo devuelve ReporteController::verDetalle().
 * -----------------------------------------------------
 */

function monto($valor): string
{
    return number_format((float) $valor, 2);
}

$mapaBadgeEstado = [
    'Pendiente' => 'badge-pendiente',
    'Terminado' => 'badge-terminado',
    'Cobrado'   => 'badge-cobrado',
    'Fracaso'   => 'badge-fracaso',
];

$mapaBadgeEstadoCobro = [
    'Cobrado'   => 'badge-cobrado',
    'Pendiente' => 'badge-pendiente',
    'Debe'      => 'badge-debe',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle del Reporte - Valtop SRL</title>
    <link rel="stylesheet" href="/assets/css/trabajos.css">
</head>
<body>
<main class="contenido">
    <div class="encabezado-pagina">
        <div>
            <h1>Detalle del Reporte</h1>
            <p class="subtitulo">Información financiera del trabajo</p>
        </div>
        <a href="/reportes" class="btn btn-secundario">← Volver</a>
    </div>

    <div class="card-detalle-financiero">
        <div class="detalle-cabecera">
            <h2><?= htmlspecialchars($fila['codigo_trabajo']) ?></h2>
            <span><?= htmlspecialchars($fila['nombre_cliente'] ?? '—') ?></span>
            <br>
            <strong><?= htmlspecialchars($fila['proyecto']) ?></strong>
        </div>

        <table class="tabla-detalle-financiero">
            <tbody>
                <tr>
                    <td>Responsable</td>
                    <td><?= htmlspecialchars($fila['nombre_responsable'] ?? '—') ?></td>
                </tr>
                <tr>
                    <td>Precio Neto</td>
                    <td class="columna-precio">S/ <?= monto($fila['precio_neto']) ?></td>
                </tr>
                <tr>
                    <td>Costo Equipos</td>
                    <td class="columna-precio">S/ <?= monto($fila['costo_equipos']) ?></td>
                </tr>
                <tr>
                    <td>Costo Viáticos</td>
                    <td class="columna-precio">S/ <?= monto($fila['costo_viaticos']) ?></td>
                </tr>
                <tr>
                    <td>Costo Materiales</td>
                    <td class="columna-precio">S/ <?= monto($fila['costo_materiales']) ?></td>
                </tr>
                <tr class="fila-total">
                    <td><strong>Capital Invertido</strong></td>
                    <td class="columna-precio"><strong>S/ <?= monto($fila['capital_invertido']) ?></strong></td>
                </tr>
                <tr>
                    <td>Fecha Factura</td>
                    <td><?= !empty($fila['fecha_factura']) ? htmlspecialchars($fila['fecha_factura']) : '—' ?></td>
                </tr>
                <tr>
                    <td>Fecha Cobro</td>
                    <td><?= !empty($fila['fecha_cobro']) ? htmlspecialchars($fila['fecha_cobro']) : '—' ?></td>
                </tr>
                <tr>
                    <td>% Financiero</td>
                    <td><?= number_format((float) $fila['porcentaje_financiero'], 2) ?> %</td>
                </tr>
                <tr class="fila-total-financiero">
                    <td><strong>Costo Financiero</strong></td>
                    <td class="columna-precio"><strong>S/ <?= monto($fila['costo_financiero']) ?></strong></td>
                </tr>
                <tr class="fila-total-financiero">
                    <td><strong>Utilidad</strong></td>
                    <td class="columna-precio"><strong>S/ <?= monto($fila['utilidad']) ?></strong></td>
                </tr>
                <tr>
                    <td>Estado de Cobro</td>
                    <td>
                        <span class="badge <?= $mapaBadgeEstadoCobro[$fila['estado_cobro']] ?? 'badge-default' ?>">
                            <?= $fila['estado_cobro'] ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td>Estado</td>
                    <td>
                        <span class="badge <?= $mapaBadgeEstado[$fila['estado']] ?? 'badge-default' ?>">
                            <?= htmlspecialchars($fila['estado']) ?>
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="acciones-formulario">
            <a href="/reportes" class="btn btn-secundario">← Volver</a>
        </div>
    </div>
</main>
</body>
</html>