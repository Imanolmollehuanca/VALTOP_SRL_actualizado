<?php
/**
 * Vista: Materiales de un Trabajo (detalle)
 * -----------------------------------------------------
 * array  $trabajo    Datos básicos del trabajo (código, proyecto).
 * array  $materiales Materiales de este trabajo, cada uno ya trae
 *                     'nombre_material' y 'subtotal' calculado por
 *                     SQL en MaterialTrabajo::listarPorTrabajo().
 * array  $catalogo   Catálogo completo, para el <datalist>.
 * float  $costoTotal Suma de subtotales, calculada con SQL en
 *                     MaterialTrabajo::costoTotalPorTrabajo().
 * array  $errores    Mensajes de error de validación.
 * -----------------------------------------------------
 */

function formatearMontoDetalle($monto): string
{
    return number_format((float) $monto, 2);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Materiales de <?= htmlspecialchars($trabajo['codigo_trabajo']) ?> - Valtop SRL</title>
    <link rel="stylesheet" href="/assets/css/trabajos.css">
</head>
<body>

<main class="contenido">

    <div class="encabezado-pagina">
        <div>
            <h1>Materiales — <?= htmlspecialchars($trabajo['codigo_trabajo']) ?></h1>
            <p class="subtitulo"><?= htmlspecialchars($trabajo['proyecto']) ?></p>
        </div>
        <a href="/materiales" class="btn btn-secundario">← Volver a Materiales</a>
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
            <button type="button" class="btn btn-primario" id="btnAbrirModalMaterial">
                + Agregar Material
            </button>
        </div>
    </div>

    <div class="tabla-wrapper">
        <table class="tabla-trabajos">
            <thead>
                <tr>
                    <th>Material</th>
                    <th>Cantidad</th>
                    <th>Unidad</th>
                    <th>Precio Unitario (S/)</th>
                    <th>Subtotal (S/)</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($materiales)): ?>
                    <tr>
                        <td colspan="6" class="sin-datos">
                            Todavía no hay materiales registrados para este trabajo.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($materiales as $material): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($material['nombre_material']) ?></strong></td>
                            <td><?= htmlspecialchars($material['cantidad']) ?></td>
                            <td><?= htmlspecialchars($material['unidad']) ?></td>
                            <td class="columna-precio"><?= formatearMontoDetalle($material['precio_unitario']) ?></td>
                            <td class="columna-precio"><?= formatearMontoDetalle($material['subtotal']) ?></td>
                            <td class="acciones">                                
                                <a
                                    href="#"
                                    class="btn-editar-material"
                                    title="Editar"
                                    data-id="<?= (int) $material['id_trabajo_material'] ?>"
                                    data-nombre_material="<?= htmlspecialchars($material['nombre_material'], ENT_QUOTES) ?>"
                                    data-cantidad="<?= htmlspecialchars((string) $material['cantidad'], ENT_QUOTES) ?>"
                                    data-unidad="<?= htmlspecialchars($material['unidad'], ENT_QUOTES) ?>"
                                    data-precio_unitario="<?= htmlspecialchars((string) $material['precio_unitario'], ENT_QUOTES) ?>"
                                >✏️</a>
                                <a href="#"
                                   title="Eliminar"
                                   class="btn-eliminar-material"
                                   data-id="<?= (int) $material['id_trabajo_material'] ?>">🗑️</a>
                            </td>
                        </tr>

                        <form id="formEliminarMaterial<?= (int) $material['id_trabajo_material'] ?>"
                              action="/materiales/trabajo/<?= (int) $trabajo['id_trabajo'] ?>/eliminar/<?= (int) $material['id_trabajo_material'] ?>"
                              method="POST" style="display:none;"></form>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <?php if (!empty($materiales)): ?>
                <tfoot>
                    <tr>
                        <td colspan="4" style="text-align:right;"><strong>Costo Total Materiales (S/)</strong></td>
                        <td class="columna-precio"><strong><?= formatearMontoDetalle($costoTotal) ?></strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>

    <!-- Modal: Agregar / Editar Material -->
    <div id="modalMaterial" class="modal-overlay modal-oculto">
        <div class="modal-caja">
            <div class="modal-encabezado">
                <h2 id="modalMaterialTitulo">Agregar Material</h2>
                <button type="button" class="btn-cerrar-modal" id="btnCerrarModalMaterial">&times;</button>
            </div>

            <form id="formMaterial" method="POST" action="/materiales/trabajo/<?= (int) $trabajo['id_trabajo'] ?>/guardar" class="form-trabajo">

                <div class="campo campo-ancho-completo">
                    <label for="nombre_material">Material</label>
                    <input
                        type="text"
                        id="nombre_material"
                        name="nombre_material"
                        list="lista_materiales"
                        placeholder="Escribe el nombre del material..."
                        autocomplete="off"
                        required
                    >
                    <datalist id="lista_materiales">
                        <?php foreach ($catalogo as $materialCatalogo): ?>
                            <option value="<?= htmlspecialchars($materialCatalogo['nombre_material']) ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>

                <div class="campo">
                    <label for="cantidad">Cantidad</label>
                    <input type="number" id="cantidad" name="cantidad" step="0.01" min="0.01" required>
                </div>

                <div class="campo">
                    <label for="unidad">Unidad</label>
                    <input type="text" id="unidad" name="unidad" placeholder="Ej: Bolsa, Kg, Galón..." required>
                </div>

                <div class="campo campo-ancho-completo">
                    <label for="precio_unitario">Precio Unitario (S/)</label>
                    <input type="number" id="precio_unitario" name="precio_unitario" step="0.01" min="0" required>
                </div>

                <div class="acciones-formulario">
                    <button type="submit" class="btn btn-primario">Guardar Material</button>
                    <button type="button" class="btn btn-secundario" id="btnCancelarModalMaterial">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

</main>

<script>
(function () {
    const modal = document.getElementById('modalMaterial');
    const form = document.getElementById('formMaterial');
    const titulo = document.getElementById('modalMaterialTitulo');

    const urlGuardar = '/materiales/trabajo/<?= (int) $trabajo['id_trabajo'] ?>/guardar';

    function abrirModalNuevo() {
        form.reset();
        form.action = urlGuardar;
        titulo.textContent = 'Agregar Material';
        modal.classList.remove('modal-oculto');
    }

    function abrirModalEditar(datos) {
        form.reset();
        form.action = '/materiales/trabajo/<?= (int) $trabajo['id_trabajo'] ?>/actualizar/' + datos.id;
        titulo.textContent = 'Editar Material';

        form.nombre_material.value = datos.nombre_material || '';
        form.cantidad.value = datos.cantidad || '';
        form.unidad.value = datos.unidad || '';
        form.precio_unitario.value = datos.precio_unitario || '';

        modal.classList.remove('modal-oculto');
    }

    function cerrarModal() {
        modal.classList.add('modal-oculto');
    }

    document.getElementById('btnAbrirModalMaterial').addEventListener('click', abrirModalNuevo);
    document.getElementById('btnCerrarModalMaterial').addEventListener('click', cerrarModal);
    document.getElementById('btnCancelarModalMaterial').addEventListener('click', cerrarModal);

    document.querySelectorAll('.btn-editar-material').forEach(function (enlace) {
        enlace.addEventListener('click', function (evento) {
            evento.preventDefault();
            abrirModalEditar(enlace.dataset);
        });
    });

    document.querySelectorAll('.btn-eliminar-material').forEach(function (boton) {
        boton.addEventListener('click', function (evento) {
            evento.preventDefault();

            const confirmado = confirm('¿Seguro que deseas eliminar este material? Esta acción no se puede deshacer.');
            if (!confirmado) {
                return;
            }

            document.getElementById('formEliminarMaterial' + boton.dataset.id).submit();
        });
    });
})();
</script>

</body>
</html>