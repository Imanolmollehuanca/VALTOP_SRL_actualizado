<?php
/**
 * Vista: Impresión de Trabajos
 * -----------------------------------------------------
 * Variables que espera recibir (ya resueltas por el Controlador):
 *
 * array   $trabajos             Lista de trabajos a imprimir (mismo formato que en listado.php)
 * array   $responsables         Lista de responsables, para resolver el nombre del filtro activo
 *                                 (cada uno: ['id_usuario' => .., 'nombre_usuario' => ..])
 * string  $estadoActual         Estado del filtro activo ('Todos' por defecto)
 * ?int    $idResponsableActual  ID del responsable filtrado, o null
 * string  $busquedaActual       Texto de búsqueda activo, o ''
 * string  $nombreUsuario        Nombre del usuario que generó la impresión
 *
 * Esta vista NO consulta la base de datos, NO valida datos, NO modifica nada.
 * Los totales y conteos por estado se calculan aquí mismo a partir de $trabajos,
 * sin necesidad de métodos adicionales en el Modelo o el Controlador.
 * -----------------------------------------------------
 */

/** Mapa de estado -> clase CSS del badge (idéntico al de listado.php). */
function claseEstado(string $estado): string
{
    $mapa = [
        'Pendiente' => 'badge-pendiente',
        'Terminado' => 'badge-terminado',
        'Cobrado'   => 'badge-cobrado',
        'Fracaso'   => 'badge-fracaso',
    ];

    return $mapa[$estado] ?? 'badge-default';
}

/** Formatea un número como precio en soles: 2500.00 -> 2,500.00 */
function formatearPrecio(float $monto): string
{
    return number_format($monto, 2);
}

/** Nombre del responsable filtrado, o 'Todos' si no hay filtro. */
function nombreResponsableFiltro(?int $idResponsableActual, array $responsables): string
{
    if ($idResponsableActual === null) {
        return 'Todos';
    }

    foreach ($responsables as $responsable) {
        if ((int) $responsable['id_usuario'] === $idResponsableActual) {
            return $responsable['nombre_usuario'];
        }
    }

    return 'Todos';
}

// --- Totales calculados a partir de la lista recibida ---
$totalTrabajos    = count($trabajos);
$totalPrecioNeto  = 0.0;
$totalesPorEstado = [];

foreach (TrabajoController::ESTADOS_VALIDOS as $estado) {
    $totalesPorEstado[$estado] = 0;
}

foreach ($trabajos as $trabajo) {
    $totalPrecioNeto += (float) $trabajo['precio_neto'];

    $estado = $trabajo['estado'];
    if (isset($totalesPorEstado[$estado])) {
        $totalesPorEstado[$estado]++;
    }
}

$fechaImpresion = date('d/m/Y H:i');
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
    <a href="/trabajos" class="btn btn-secundario">← Volver</a>
    <button type="button" class="btn btn-primario" onclick="window.print()">🖨️ Imprimir</button>
</div>

<main class="pagina-impresion">

    <header class="impresion-encabezado">
        <div class="impresion-marca">
            <span class="impresion-logo" aria-hidden="true">
                <svg viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="20" cy="20" r="20" fill="#2563EB"/>
                    <path d="M11 12 L20 29 L29 12 L24 12 L20 20 L16 12 Z" fill="#FFFFFF"/>
                </svg>
            </span>
            <div>
                <div class="impresion-empresa">VALTOP SRL</div>
                <div class="impresion-empresa-subtitulo">Sistema de Gestión</div>
            </div>
        </div>

        <div class="impresion-meta">
            <div>Fecha de impresión: <strong><?= htmlspecialchars($fechaImpresion) ?></strong></div>
            <div>Usuario: <strong><?= htmlspecialchars($nombreUsuario) ?></strong></div>
        </div>
    </header>

    <h1 class="impresion-titulo-reporte">REPORTE DE TRABAJOS</h1>

    <div class="impresion-filtros">
        <span>Estado: <strong><?= htmlspecialchars($estadoActual) ?></strong></span>
        <span>Responsable: <strong><?= htmlspecialchars(nombreResponsableFiltro($idResponsableActual, $responsables)) ?></strong></span>
        <?php if ($busquedaActual !== ''): ?>
            <span>Búsqueda: <strong><?= htmlspecialchars($busquedaActual) ?></strong></span>
        <?php endif; ?>
    </div>

    <table class="tabla-impresion">
        <thead>
            <tr>
                <th>N° Trabajo</th>
                <th>Cliente</th>
                <th>Proyecto</th>
                <th>Responsable</th>
                <th>Precio Neto (S/)</th>
                <th>Estado</th>
                <th>Fecha Inicio</th>
                <th>Fecha Fin</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($trabajos)): ?>
                <tr>
                    <td colspan="8" class="sin-datos">
                        No hay trabajos que coincidan con los filtros aplicados.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($trabajos as $trabajo): ?>
                    <tr>
                        <td><?= htmlspecialchars($trabajo['codigo_trabajo']) ?></td>
                        <td><?= htmlspecialchars($trabajo['nombre_cliente']) ?></td>
                        <td><?= htmlspecialchars($trabajo['proyecto']) ?></td>
                        <td><?= htmlspecialchars($trabajo['nombre_responsable']) ?></td>
                        <td class="columna-precio">
                            <?= formatearPrecio((float) $trabajo['precio_neto']) ?>
                        </td>
                        <td>
                            <span class="badge <?= claseEstado($trabajo['estado']) ?>">
                                <?= htmlspecialchars($trabajo['estado']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($trabajo['fecha_inicio']) ?></td>
                        <td><?= htmlspecialchars($trabajo['fecha_fin']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <section class="impresion-resumen">
        <div class="impresion-resumen-fila">
            <span>Total Trabajos:</span>
            <strong><?= $totalTrabajos ?></strong>
        </div>
        <div class="impresion-resumen-fila">
            <span>Total Precio Neto:</span>
            <strong>S/. <?= formatearPrecio($totalPrecioNeto) ?></strong>
        </div>
        <?php foreach ($totalesPorEstado as $estado => $cantidad): ?>
            <div class="impresion-resumen-fila">
                <span>Trabajos <?= htmlspecialchars($estado) ?>:</span>
                <strong><?= $cantidad ?></strong>
            </div>
        <?php endforeach; ?>
    </section>

    <div class="impresion-leyenda">
        <?php foreach (TrabajoController::ESTADOS_VALIDOS as $estado): ?>
            <span class="leyenda-item">
                <span class="punto <?= claseEstado($estado) ?>"></span> <?= htmlspecialchars($estado) ?>
            </span>
        <?php endforeach; ?>
    </div>

    <footer class="impresion-pie">
        Página 1 de 1
    </footer>

</main>

</body>
</html>