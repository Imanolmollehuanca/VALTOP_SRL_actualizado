<?php
/**
 * Vista: Listado de Equipos
 * -----------------------------------------------------
 * array  $equipos       Lista de registros de equipos, cada uno
 *                        ya trae 'codigo_trabajo' y 'proyecto'
 *                        gracias al JOIN hecho en Equipo::listarPorEstado().
 * string $estadoActual  Estado del filtro activo ('Todos' por defecto).
 *
 * Esta vista NO consulta la base de datos, NO valida datos.
 * NO muestra series ni el detalle individual de equipos: eso
 * solo se ve en /equipos/ver/{id} (detalle.php). Aquí, en
 * "N.° Equipos", solo se imprime la cantidad total ya calculada
 * en cantidad_equipos.
 * -----------------------------------------------------
 */

function claseEstadoEquipo(string $estado): string
{
    $mapa = [
        'Pendiente'         => 'badge-pendiente',
        'Devuelto'          => 'badge-terminado',
        'Cambio de equipo'  => 'badge-cambio',
    ];

    return $mapa[$estado] ?? 'badge-default';
}

function formatearMontoEquipo($monto): string
{
    return number_format((float) $monto, 2);
}

/** Junta fecha y hora en dos líneas dentro de la misma celda. */
function celdaFechaHora(?string $fecha, ?string $hora): string
{
    if (empty($fecha)) {
        return '—';
    }

    $html = htmlspecialchars($fecha);

    if (!empty($hora)) {
        $html .= '<br><small>' . htmlspecialchars(substr($hora, 0, 5)) . '</small>';
    }

    return $html;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Equipos - Valtop SRL</title>
    <link rel="stylesheet" href="/assets/css/trabajos.css">
</head>
<body>

<main class="contenido">

    <div class="encabezado-pagina">
        <div>
            <h1>Equipos</h1>
            <p class="subtitulo">Registro general de equipos utilizados en los trabajos</p>
        </div>
        <a href="/modulos" class="btn btn-secundario">← Volver a Módulos</a>
    </div>

    <div class="barra-acciones">
        <div class="barra-acciones-botones">
            <a href="/equipos/nuevo" class="btn btn-primario">
                + Nuevo Registro
            </a>
        </div>
    </div>

    <div class="barra-filtros">
        <div class="filtro-estados">
            <span class="filtro-etiqueta">Estado:</span>

            <?php
            $tabsEquipos = array_merge(['Todos'], EquipoController::ESTADOS_VALIDOS);

            foreach ($tabsEquipos as $tab):
                $activo = ($tab === $estadoActual) ? 'tab-activo' : '';
            ?>
                <a href="?estado=<?= urlencode($tab) ?>"
                   class="tab-estado <?= $activo ?> <?= $tab !== 'Todos' ? claseEstadoEquipo($tab) : '' ?>">
                    <?= htmlspecialchars($tab) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="tabla-wrapper">
        <table class="tabla-trabajos">
            <thead>
                <tr>
                    <th>N.° Trabajo</th>
                    <th>N.° Equipos</th>
                    <th>Contacto</th>
                    <th>Encargado</th>
                    <th>Salida</th>
                    <th>Regreso</th>
                    <th>Tiempo</th>
                    <th>Costo</th>
                    <th>Pago 1</th>
                    <th>Pago 2</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($equipos)): ?>
                    <tr>
                        <td colspan="12" class="sin-datos">
                            No hay registros de equipos que coincidan con el filtro aplicado.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($equipos as $equipo): ?>
                        <tr>
                            <td><?= htmlspecialchars($equipo['codigo_trabajo'] ?? '—') ?></td>
                            <td><?= (int) $equipo['cantidad_equipos'] ?></td>
                            <td class="celda-doble">
                                <?= htmlspecialchars($equipo['contacto']) ?>
                                <?php if (!empty($equipo['telefono_contacto'])): ?>
                                    <br><small><?= htmlspecialchars($equipo['telefono_contacto']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($equipo['encargado']) ?></td>
                            <td class="celda-doble">
                                <?= celdaFechaHora($equipo['fecha_salida'], $equipo['hora_salida']) ?>
                            </td>
                            <td class="celda-doble">
                                <?= celdaFechaHora($equipo['fecha_regreso'], $equipo['hora_regreso']) ?>
                            </td>
                            <td><?= htmlspecialchars($equipo['tiempo'] ?: '—') ?></td>
                            <td class="columna-precio"><?= formatearMontoEquipo($equipo['costo']) ?></td>
                            <td class="columna-precio"><?= formatearMontoEquipo($equipo['pago_1']) ?></td>
                            <td class="columna-precio"><?= formatearMontoEquipo($equipo['pago_2']) ?></td>
                            <td>
                                <span class="badge <?= claseEstadoEquipo($equipo['estado']) ?>">
                                    <?= htmlspecialchars($equipo['estado']) ?>
                                </span>
                            </td>
                            <td class="acciones">
                                <a href="/equipos/ver/<?= (int) $equipo['id_equipo'] ?>" title="Ver detalle">👁️</a>
                                <a href="/equipos/editar/<?= (int) $equipo['id_equipo'] ?>" title="Editar">✏️</a>
                                <a href="#"
                                   title="Eliminar"
                                   class="btn-eliminar-equipo"
                                   data-id="<?= (int) $equipo['id_equipo'] ?>">🗑️</a>
                            </td>
                        </tr>

                        <!-- Formulario oculto para eliminar este registro por POST -->
                        <form id="formEliminarEquipo<?= (int) $equipo['id_equipo'] ?>"
                              action="/equipos/eliminar/<?= (int) $equipo['id_equipo'] ?>"
                              method="POST" style="display:none;"></form>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="leyenda-estados">
        <?php foreach (EquipoController::ESTADOS_VALIDOS as $estado): ?>
            <span class="leyenda-item">
                <span class="punto <?= claseEstadoEquipo($estado) ?>"></span> <?= htmlspecialchars($estado) ?>
            </span>
        <?php endforeach; ?>
    </div>

</main>

<script>
(function () {
    document.querySelectorAll('.btn-eliminar-equipo').forEach(function (boton) {
        boton.addEventListener('click', function (evento) {
            evento.preventDefault();

            const confirmado = confirm('¿Seguro que deseas eliminar este registro de equipos? Esta acción no se puede deshacer.');
            if (!confirmado) {
                return;
            }

            document.getElementById('formEliminarEquipo' + boton.dataset.id).submit();
        });
    });
})();
</script>

</body>
</html>