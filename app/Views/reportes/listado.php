<?php
/**
 * Vista: Módulo Reportes (Fase 10)
 * -----------------------------------------------------
 * Variables esperadas:
 *
 * array $filas             Trabajos con montos ya calculados
 * array $resumen           Totales para las tarjetas resumen
 * array $responsables      Para el selector de filtro
 * array $filtrosActuales   ['fecha_desde','fecha_hasta','id_responsable','estado']
 *
 * Vista de solo lectura: no registra, no edita, no elimina.
 * -----------------------------------------------------
 */

function monto($valor): string
{
    return number_format((float) $valor, 2);
}

function claseBadgeEstado(string $estado): string
{
    $mapa = [
        'Pendiente' => 'badge-pendiente',
        'Terminado' => 'badge-terminado',
        'Cobrado'   => 'badge-cobrado',
        'Fracaso'   => 'badge-fracaso',
    ];

    return $mapa[$estado] ?? 'badge-default';
}

function claseBadgeEstadoCobro(string $estadoCobro): string
{
    $mapa = [
        'Cobrado'   => 'badge-cobrado',
        'Pendiente' => 'badge-pendiente',
        'Debe'      => 'badge-debe',
    ];

    return $mapa[$estadoCobro] ?? 'badge-default';
}

$queryFiltros = http_build_query(array_filter($filtrosActuales));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes - Valtop SRL</title>
    <link rel="stylesheet" href="/assets/css/trabajos.css">
</head>
<body>
<main class="contenido">

    <div class="encabezado-pagina">
        <div>
            <h1>Reportes</h1>
            <p class="subtitulo">Centro de reportes e información</p>
        </div>
        <a href="/modulos" class="btn btn-secundario">← Volver a Módulos</a>
    </div>

    <div class="acciones-reporte">
        <a href="/reportes/exportar-excel<?= $queryFiltros ? '?' . $queryFiltros : '' ?>"
           class="btn btn-exito">
            📊 Exportar Excel
        </a>
        <a href="/reportes/exportar-pdf<?= $queryFiltros ? '?' . $queryFiltros : '' ?>"
           class="btn btn-secundario"
           target="_blank">
            📄 Exportar PDF
        </a>
    </div>

    <form action="/reportes" method="GET" class="filtros-reporte">
        <div class="grid-filtros-reporte">
            <div class="campo-filtro-reporte">
                <label for="fecha_desde">Desde</label>
                <input type="date" id="fecha_desde" name="fecha_desde"
                       value="<?= htmlspecialchars($filtrosActuales['fecha_desde']) ?>">
            </div>

            <div class="campo-filtro-reporte">
                <label for="fecha_hasta">Hasta</label>
                <input type="date" id="fecha_hasta" name="fecha_hasta"
                       value="<?= htmlspecialchars($filtrosActuales['fecha_hasta']) ?>">
            </div>

            <div class="campo-filtro-reporte">
                <label for="id_responsable">Responsable</label>
                <select id="id_responsable" name="id_responsable">
                    <option value="">Todos</option>
                    <?php foreach ($responsables as $responsable): ?>
                        <option value="<?= (int) $responsable['id_usuario'] ?>"
                            <?= (string) $filtrosActuales['id_responsable'] === (string) $responsable['id_usuario'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($responsable['nombre_usuario']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="campo-filtro-reporte">
                <label for="estado">Estado</label>
                <select id="estado" name="estado">
                    <option value="">Todos</option>
                    <?php foreach (['Pendiente', 'Terminado', 'Cobrado', 'Fracaso'] as $opcionEstado): ?>
                        <option value="<?= $opcionEstado ?>"
                            <?= $filtrosActuales['estado'] === $opcionEstado ? 'selected' : '' ?>>
                            <?= $opcionEstado ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primario">
                🔍 Generar Reporte
            </button>
        </div>
    </form>

    <div class="tabla-wrapper">
        <table class="tabla-trabajos">
            <thead>
            <tr>
                <th>Nº Trabajo</th>
                <th>Cliente</th>
                <th>Proyecto</th>
                <th>Responsable</th>
                <th>Precio Neto</th>
                <th>Capital Invertido</th>
                <th>Costo Financiero</th>
                <th>Utilidad</th>
                <th>Estado de Cobro</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($filas)): ?>
                <tr>
                    <td colspan="11" class="sin-datos">
                        No se encontraron trabajos con los filtros seleccionados.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($filas as $fila): ?>
                    <tr>
                        <td><?= htmlspecialchars($fila['codigo_trabajo']) ?></td>
                        <td><?= htmlspecialchars($fila['nombre_cliente'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($fila['proyecto']) ?></td>
                        <td><?= htmlspecialchars($fila['nombre_responsable'] ?? '—') ?></td>
                        <td class="columna-precio">S/ <?= monto($fila['precio_neto']) ?></td>
                        <td class="columna-precio">S/ <?= monto($fila['capital_invertido']) ?></td>
                        <td class="columna-precio">S/ <?= monto($fila['costo_financiero']) ?></td>
                        <td class="columna-precio">S/ <?= monto($fila['utilidad']) ?></td>
                        <td>
                            <span class="badge <?= claseBadgeEstadoCobro($fila['estado_cobro']) ?>">
                                <?= $fila['estado_cobro'] ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= claseBadgeEstado($fila['estado']) ?>">
                                <?= htmlspecialchars($fila['estado']) ?>
                            </span>
                        </td>
                        <td class="acciones">
                            <a href="/reportes/ver/<?= (int) $fila['id_trabajo'] ?>" title="Ver detalle">
                                👁️
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="tarjetas-resumen-reporte">
        <div class="tarjeta-resumen tarjeta-resumen-trabajos">
            <span class="tarjeta-resumen-icono">💼</span>
            <div class="tarjeta-resumen-texto">
                <span class="tarjeta-resumen-etiqueta">Total Trabajos</span>
                <span class="tarjeta-resumen-valor"><?= (int) $resumen['total_trabajos'] ?></span>
            </div>
        </div>

        <div class="tarjeta-resumen tarjeta-resumen-facturado">
            <span class="tarjeta-resumen-icono">💰</span>
            <div class="tarjeta-resumen-texto">
                <span class="tarjeta-resumen-etiqueta">Total Facturado</span>
                <span class="tarjeta-resumen-valor">S/ <?= monto($resumen['total_facturado']) ?></span>
            </div>
        </div>

        <div class="tarjeta-resumen tarjeta-resumen-capital">
            <span class="tarjeta-resumen-icono">📦</span>
            <div class="tarjeta-resumen-texto">
                <span class="tarjeta-resumen-etiqueta">Capital Invertido</span>
                <span class="tarjeta-resumen-valor">S/ <?= monto($resumen['capital_invertido']) ?></span>
            </div>
        </div>

        <div class="tarjeta-resumen tarjeta-resumen-utilidad">
            <span class="tarjeta-resumen-icono">📈</span>
            <div class="tarjeta-resumen-texto">
                <span class="tarjeta-resumen-etiqueta">Utilidad Total</span>
                <span class="tarjeta-resumen-valor">S/ <?= monto($resumen['utilidad_total']) ?></span>
            </div>
        </div>

        <div class="tarjeta-resumen tarjeta-resumen-cobrados">
            <span class="tarjeta-resumen-icono">✅</span>
            <div class="tarjeta-resumen-texto">
                <span class="tarjeta-resumen-etiqueta">Trabajos Cobrados</span>
                <span class="tarjeta-resumen-valor"><?= (int) $resumen['trabajos_cobrados'] ?></span>
            </div>
        </div>
    </div>

</main>
</body>
</html>