<?php
/**
 * Vista: Gastos Generales
 * -----------------------------------------------------
 * array   $gastos         Lista de gastos (ya filtrada si hay búsqueda).
 * float   $total          Suma total, calculada con SQL.
 * string  $busquedaActual Texto de búsqueda activo, o ''.
 * array   $errores        Mensajes de error (si un guardado falló).
 * ?array  $gastoFallido   Datos del intento fallido, para reabrir el
 *                          modal ya prellenado (o null si no aplica).
 * -----------------------------------------------------
 */

function formatearMontoGasto($monto): string
{
    return number_format((float) $monto, 2);
}

function formatearFechaGasto(string $fecha): string
{
    return date('d/m/Y', strtotime($fecha));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gastos Generales - Valtop SRL</title>
    <link rel="stylesheet" href="/assets/css/trabajos.css">
</head>
<body>

<main class="contenido">

    <div class="encabezado-pagina">
        <div>
            <h1>Gastos Generales</h1>
            <p class="subtitulo">Gestión de gastos generales</p>
        </div>
        <a href="/modulos" class="btn btn-secundario">← Volver a Módulos</a>
    </div>

    <?php if (!empty($errores)): ?>
        <div class="alerta alerta-error">
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="barra-acciones">
        <div class="barra-acciones-botones">
            <button type="button" class="btn btn-primario" id="btnAbrirModalGasto">
                + Nuevo Gasto
            </button>
            <button type="button" class="btn btn-peligro" id="btnVaciarLista">
                🗑️ Vaciar Lista
            </button>
        </div>

        <div class="barra-acciones-botones">
            <form action="/gastos-generales" method="GET" class="barra-busqueda">
                <input
                    type="text"
                    name="buscar"
                    placeholder="Buscar..."
                    value="<?= htmlspecialchars($busquedaActual) ?>"
                >
                <button type="submit" class="btn btn-icono">🔍</button>
            </form>
            <button type="submit" form="formFiltrar" class="btn btn-secundario">🔽 Filtrar</button>
            <a href="/gastos-generales" class="btn btn-secundario" title="Actualizar">🔄</a>
        </div>
    </div>
    <!-- El botón "Filtrar" reutiliza el mismo formulario de búsqueda
         (no hay criterios de filtro adicionales definidos todavía) -->
    <form id="formFiltrar" action="/gastos-generales" method="GET" style="display:none;">
        <input type="hidden" name="buscar" value="<?= htmlspecialchars($busquedaActual) ?>">
    </form>

    <div class="tabla-wrapper">
        <table class="tabla-trabajos">
            <thead>
                <tr>
                    <th>Concepto</th>
                    <th>Fecha</th>
                    <th>Monto (S/)</th>
                    <th>Observación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($gastos)): ?>
                    <tr>
                        <td colspan="5" class="sin-datos">
                            No hay gastos generales registrados todavía.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($gastos as $gasto): ?>
                        <tr>
                            <td><?= htmlspecialchars($gasto['concepto']) ?></td>
                            <td><?= formatearFechaGasto($gasto['fecha']) ?></td>
                            <td class="columna-precio"><?= formatearMontoGasto($gasto['monto']) ?></td>
                            <td><?= htmlspecialchars($gasto['observacion'] ?? '—') ?></td>
                            <td class="acciones">
                                <a
                                    href="#"
                                    class="btn-editar-gasto"
                                    title="Editar"
                                    data-id="<?= (int) $gasto['id_gasto'] ?>"
                                    data-concepto="<?= htmlspecialchars($gasto['concepto'], ENT_QUOTES) ?>"
                                    data-fecha="<?= htmlspecialchars($gasto['fecha'], ENT_QUOTES) ?>"
                                    data-monto="<?= htmlspecialchars((string) $gasto['monto'], ENT_QUOTES) ?>"
                                    data-observacion="<?= htmlspecialchars((string) $gasto['observacion'], ENT_QUOTES) ?>"
                                >✏️</a>
                                <a href="#"
                                   title="Eliminar"
                                   class="btn-eliminar-gasto"
                                   data-id="<?= (int) $gasto['id_gasto'] ?>">🗑️</a>
                            </td>
                        </tr>

                        <form id="formEliminarGasto<?= (int) $gasto['id_gasto'] ?>"
                              action="/gastos-generales/eliminar/<?= (int) $gasto['id_gasto'] ?>"
                              method="POST" style="display:none;"></form>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="panel-total-gastos">
        <span class="total-gastos-etiqueta">TOTAL GASTOS GENERALES</span>
        <span class="total-gastos-monto">Bs <?= formatearMontoGasto($total) ?></span>
    </div>

    <!-- Formulario oculto: vacía toda la lista -->
    <form id="formVaciarLista" action="/gastos-generales/vaciar" method="POST" style="display:none;"></form>

    <!-- Modal: Agregar / Editar Gasto -->
    <div id="modalGasto" class="modal-overlay modal-oculto">
        <div class="modal-caja">
            <div class="modal-encabezado">
                <h2 id="modalGastoTitulo">Nuevo Gasto</h2>
                <button type="button" class="btn-cerrar-modal" id="btnCerrarModalGasto">&times;</button>
            </div>

            <form id="formGasto" method="POST" action="/gastos-generales/guardar" class="form-trabajo">

                <div class="campo campo-ancho-completo">
                    <label for="concepto">Concepto</label>
                    <input
                        type="text"
                        id="concepto"
                        name="concepto"
                        placeholder="Ej: Agua, Luz, Alquiler..."
                        value="<?= htmlspecialchars($gastoFallido['concepto'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="campo">
                    <label for="monto">Monto (S/)</label>
                    <input
                        type="number"
                        id="monto"
                        name="monto"
                        step="0.01"
                        min="0"
                        value="<?= htmlspecialchars($gastoFallido['monto'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="campo">
                    <label for="fecha">Fecha</label>
                    <input
                        type="date"
                        id="fecha"
                        name="fecha"
                        value="<?= htmlspecialchars($gastoFallido['fecha'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="campo campo-ancho-completo">
                    <label for="observacion">Observación</label>
                    <textarea
                        id="observacion"
                        name="observacion"
                        rows="3"
                    ><?= htmlspecialchars($gastoFallido['observacion'] ?? '') ?></textarea>
                </div>

                <div class="acciones-formulario">
                    <button type="submit" class="btn btn-primario">Guardar</button>
                    <button type="button" class="btn btn-secundario" id="btnCancelarModalGasto">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

</main>

<script>
(function () {
    const modal = document.getElementById('modalGasto');
    const form = document.getElementById('formGasto');
    const titulo = document.getElementById('modalGastoTitulo');

    function abrirModalNuevo() {
        form.reset();
        form.action = '/gastos-generales/guardar';
        titulo.textContent = 'Nuevo Gasto';
        modal.classList.remove('modal-oculto');
    }

    function abrirModalEditar(datos) {
        form.reset();
        form.action = '/gastos-generales/actualizar/' + datos.id;
        titulo.textContent = 'Editar Gasto';

        form.concepto.value = datos.concepto || '';
        form.fecha.value = datos.fecha || '';
        form.monto.value = datos.monto || '';
        form.observacion.value = datos.observacion || '';

        modal.classList.remove('modal-oculto');
    }

    function cerrarModal() {
        modal.classList.add('modal-oculto');
    }

    document.getElementById('btnAbrirModalGasto').addEventListener('click', abrirModalNuevo);
    document.getElementById('btnCerrarModalGasto').addEventListener('click', cerrarModal);
    document.getElementById('btnCancelarModalGasto').addEventListener('click', cerrarModal);

    document.querySelectorAll('.btn-editar-gasto').forEach(function (enlace) {
        enlace.addEventListener('click', function (evento) {
            evento.preventDefault();
            abrirModalEditar(enlace.dataset);
        });
    });

    document.querySelectorAll('.btn-eliminar-gasto').forEach(function (boton) {
        boton.addEventListener('click', function (evento) {
            evento.preventDefault();

            const confirmado = confirm('¿Seguro que deseas eliminar este gasto? Esta acción no se puede deshacer.');
            if (!confirmado) {
                return;
            }

            document.getElementById('formEliminarGasto' + boton.dataset.id).submit();
        });
    });

    document.getElementById('btnVaciarLista').addEventListener('click', function () {
        const confirmado = confirm('¿Está seguro de eliminar toda la lista de gastos generales? Esta acción no se puede deshacer.');
        if (!confirmado) {
            return;
        }

        document.getElementById('formVaciarLista').submit();
    });

    <?php if (!empty($errores) && !empty($gastoFallido)): ?>
        <?php if (!empty($gastoFallido['id_gasto'])): ?>
            abrirModalEditar({
                id: <?= (int) $gastoFallido['id_gasto'] ?>,
                concepto: <?= json_encode($gastoFallido['concepto'] ?? '') ?>,
                fecha: <?= json_encode($gastoFallido['fecha'] ?? '') ?>,
                monto: <?= json_encode($gastoFallido['monto'] ?? '') ?>,
                observacion: <?= json_encode($gastoFallido['observacion'] ?? '') ?>
            });
        <?php else: ?>
            modal.classList.remove('modal-oculto');
        <?php endif; ?>
    <?php endif; ?>
})();
</script>

</body>
</html>