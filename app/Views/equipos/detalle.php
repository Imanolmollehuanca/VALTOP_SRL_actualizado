<?php
/**
 * Vista: Detalle de un registro de Equipos (botón 👁️)
 * -----------------------------------------------------
 * array $equipo  Datos completos del registro, incluyendo
 *                'codigo_trabajo' y 'proyecto' (por el JOIN
 *                hecho en Equipo::listar()/buscarPorId()),
 *                'equipos_utilizados' (array de filas con
 *                tipo_equipo, equipo_marca, serie y cantidad,
 *                desde Equipo::buscarPorId()) y
 *                'historial_cambios' (array de cambios
 *                permanentes: fecha_cambio, cantidad_retirada,
 *                cantidad_nueva, motivo, observacion, usuario,
 *                tipo_equipo_retirado, equipo_marca_retirado,
 *                serie_retirado, tipo_equipo_nuevo,
 *                equipo_marca_nuevo, serie_nuevo).
 *
 * NOTA SOBRE LA SERIE:
 * La serie viene siempre resuelta por id_catalogo_equipo desde
 * el Modelo (JOIN con catalogo_equipos), nunca por nombre. Esta
 * vista solo la imprime tal cual llega en $equipo; no hace
 * consultas ni búsquedas por nombre.
 * -----------------------------------------------------
 */

function claseEstadoEquipoDetalle(string $estado): string
{
    $mapa = [
        'Pendiente'        => 'badge-pendiente',
        'Devuelto'         => 'badge-terminado',
        'Cambio de equipo' => 'badge-cambio',
    ];

    return $mapa[$estado] ?? 'badge-default';
}

function formatearMontoDetalle($monto): string
{
    return number_format((float) $monto, 2);
}

/** Muestra la serie o un guion si no hay dato (nunca deja la celda vacía). */
function textoSerieDetalle(?string $serie): string
{
    return ($serie !== null && trim($serie) !== '') ? htmlspecialchars($serie) : '—';
}

$equiposUtilizados = $equipo['equipos_utilizados'] ?? [];
$historialCambios  = $equipo['historial_cambios'] ?? [];

$totalEquiposUtilizados = 0;
foreach ($equiposUtilizados as $filaEquipo) {
    $totalEquiposUtilizados += (int) $filaEquipo['cantidad'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Equipos - Valtop SRL</title>
    <link rel="stylesheet" href="/assets/css/trabajos.css">
</head>
<body>

<main class="contenido">

    <div class="encabezado-pagina">
        <div>
            <h1>Detalle de Equipos</h1>
            <p class="subtitulo">
                Trabajo: <?= htmlspecialchars($equipo['codigo_trabajo'] ?? '—') ?>
                — <?= htmlspecialchars($equipo['proyecto'] ?? '') ?>
            </p>
        </div>
        <a href="/equipos" class="btn btn-secundario">← Volver al listado</a>
    </div>

    <section class="panel-datos-generales">
        <h2>Datos del Registro</h2>

        <div class="grid-datos">
            <div class="dato">
                <span class="dato-etiqueta">N.° Trabajo</span>
                <span class="dato-valor"><?= htmlspecialchars($equipo['codigo_trabajo'] ?? '—') ?></span>
            </div>

            <div class="dato">
                <span class="dato-etiqueta">N.° Equipos</span>
                <span class="dato-valor"><?= (int) $equipo['cantidad_equipos'] ?></span>
            </div>

            <div class="dato">
                <span class="dato-etiqueta">Contacto</span>
                <span class="dato-valor">
                    <?= htmlspecialchars($equipo['contacto']) ?>
                    <?php if(!empty($equipo['telefono_contacto'])): ?>
                        <br><small><?= htmlspecialchars($equipo['telefono_contacto']) ?></small>
                    <?php endif; ?>
                </span>
            </div>

            <div class="dato">
                <span class="dato-etiqueta">Encargado</span>
                <span class="dato-valor"><?= htmlspecialchars($equipo['encargado']) ?></span>
            </div>

            <div class="dato">
                <span class="dato-etiqueta">Fecha Salida</span>
                <span class="dato-valor"><?= htmlspecialchars($equipo['fecha_salida']) ?></span>
            </div>

            <div class="dato">
                <span class="dato-etiqueta">Hora Salida</span>
                <span class="dato-valor"><?= htmlspecialchars($equipo['hora_salida']) ?></span>
            </div>

            <div class="dato">
                <span class="dato-etiqueta">Fecha Regreso</span>
                <span class="dato-valor"><?= htmlspecialchars($equipo['fecha_regreso'] ?: '—') ?></span>
            </div>

            <div class="dato">
                <span class="dato-etiqueta">Hora Regreso</span>
                <span class="dato-valor"><?= htmlspecialchars($equipo['hora_regreso'] ?: '—') ?></span>
            </div>

            <div class="dato">
                <span class="dato-etiqueta">Tiempo</span>
                <span class="dato-valor"><?= htmlspecialchars($equipo['tiempo'] ?: '—') ?></span>
            </div>

            <div class="dato">
                <span class="dato-etiqueta">Costo (S/)</span>
                <span class="dato-valor"><?= formatearMontoDetalle($equipo['costo']) ?></span>
            </div>

            <div class="dato">
                <span class="dato-etiqueta">Pago 1 (S/)</span>
                <span class="dato-valor"><?= formatearMontoDetalle($equipo['pago_1']) ?></span>
            </div>

            <div class="dato">
                <span class="dato-etiqueta">Pago 2 (S/)</span>
                <span class="dato-valor"><?= formatearMontoDetalle($equipo['pago_2']) ?></span>
            </div>

            <div class="dato">
                <span class="dato-etiqueta">Estado</span>
                <span class="badge <?= claseEstadoEquipoDetalle($equipo['estado']) ?>">
                    <?= htmlspecialchars($equipo['estado']) ?>
                </span>
            </div>
        </div>
    </section>

    <section class="panel-datos-generales">
        <h2>📦 Equipos actualmente utilizados</h2>

        <div class="tabla-wrapper">
            <table class="tabla-trabajos">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Equipo / Marca</th>
                        <th>Serie</th>
                        <th>Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($equiposUtilizados)): ?>
                        <tr>
                            <td colspan="4" class="sin-datos">
                                No se registraron equipos utilizados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($equiposUtilizados as $filaEquipo): ?>
                            <tr>
                                <td><?= htmlspecialchars($filaEquipo['tipo_equipo']) ?></td>
                                <td><?= htmlspecialchars($filaEquipo['equipo_marca']) ?></td>
                                <td><?= textoSerieDetalle($filaEquipo['serie'] ?? null) ?></td>
                                <td><?= (int) $filaEquipo['cantidad'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($equiposUtilizados)): ?>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="dato-etiqueta">TOTAL</td>
                            <td><strong><?= $totalEquiposUtilizados ?></strong></td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </section>

    <section class="panel-datos-generales">
        <h2>🔄 Historial de cambios</h2>

        <div class="tabla-wrapper">
            <table class="tabla-trabajos">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Equipo retirado</th>
                        <th>Serie retirada</th>
                        <th>Cant.</th>
                        <th>Equipo nuevo</th>
                        <th>Serie nueva</th>
                        <th>Cant.</th>
                        <th>Motivo</th>
                        <th>Observación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($historialCambios)): ?>
                        <tr>
                            <td colspan="9" class="sin-datos">
                                No se registraron cambios de equipo.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($historialCambios as $cambio): ?>
                            <tr>
                                <td><?= htmlspecialchars($cambio['fecha_cambio']) ?></td>
                                <td>
                                    <?= htmlspecialchars($cambio['tipo_equipo_retirado']) ?>
                                    — <?= htmlspecialchars($cambio['equipo_marca_retirado']) ?>
                                </td>
                                <td><?= textoSerieDetalle($cambio['serie_retirado'] ?? null) ?></td>
                                <td><?= (int) $cambio['cantidad_retirada'] ?></td>
                                <td>
                                    <?= htmlspecialchars($cambio['tipo_equipo_nuevo']) ?>
                                    — <?= htmlspecialchars($cambio['equipo_marca_nuevo']) ?>
                                </td>
                                <td><?= textoSerieDetalle($cambio['serie_nuevo'] ?? null) ?></td>
                                <td><?= (int) $cambio['cantidad_nueva'] ?></td>
                                <td><?= htmlspecialchars($cambio['motivo']) ?></td>
                                <td><?= htmlspecialchars($cambio['observacion'] ?: '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="acciones-expediente">
            <a href="/equipos/editar/<?= (int) $equipo['id_equipo'] ?>" class="btn btn-primario">✏️ Editar Registro</a>
            <a href="/trabajos/expediente/<?= (int) $equipo['id_trabajo'] ?>" class="btn btn-secundario">📁 Ver Expediente del Trabajo</a>
        </div>
    </section>

</main>

</body>
</html>