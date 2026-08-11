<?php
/**
 * Vista: Formulario de Equipo (Registrar / Editar)
 * -----------------------------------------------------
 * array   $trabajos        Lista de trabajos para el selector
 *                           (cada uno: ['id_trabajo' => .., 'codigo_trabajo' => .., 'proyecto' => ..])
 * array   $catalogoEquipos Catálogo completo de equipos, desde
 *                           EquipoController::obtenerCatalogo()
 *                           (cada uno: ['id_catalogo_equipo' => .., 'tipo_equipo' => .., 'equipo_marca' => ..])
 * ?array  $equipo          Datos a editar, o null si es "Nuevo Registro".
 *                           Cuando es edición, trae 'equipos_utilizados'
 *                           (array de filas ya usadas: id_catalogo_equipo,
 *                           cantidad, tipo_equipo, equipo_marca).
 * array   $errores         Mensajes de error de validación
 * -----------------------------------------------------
 */

$esEdicion = !empty($equipo);
$tituloPagina = $esEdicion ? 'Editar Registro de Equipos' : 'Nuevo Registro de Equipos';

$accionFormulario = $esEdicion
    ? '/equipos/actualizar/' . (int) $equipo['id_equipo']
    : '/equipos/guardar';

function valorCampoEquipo(?array $equipo, string $campo, string $porDefecto = ''): string
{
    return htmlspecialchars($equipo[$campo] ?? $porDefecto);
}

// Tipos de equipo únicos, en el orden en que aparecen en el catálogo,
// para poblar el primer selector de cada fila (Tipo de equipo).
$tiposEquipo = [];
foreach ($catalogoEquipos as $itemCatalogo) {
    if (!in_array($itemCatalogo['tipo_equipo'], $tiposEquipo, true)) {
        $tiposEquipo[] = $itemCatalogo['tipo_equipo'];
    }
}

// Filas iniciales de equipos utilizados (edición) o una fila vacía (nuevo registro).
$filasIniciales = $esEdicion && !empty($equipo['equipos_utilizados'])
    ? $equipo['equipos_utilizados']
    : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $tituloPagina ?> - Valtop SRL</title>
    <link rel="stylesheet" href="/assets/css/trabajos.css">
</head>
<body>

<main class="contenido">

    <div class="encabezado-pagina">
        <div>
            <h1><?= $tituloPagina ?></h1>
            <p class="subtitulo">Registro general de equipos utilizados en los trabajos</p>
        </div>
        <a href="/equipos" class="btn btn-secundario">← Volver al listado</a>
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

    <form action="<?= $accionFormulario ?>" method="POST" class="form-trabajo" id="formEquipos">

        <div class="campo">
            <label for="id_trabajo">Trabajo</label>
            <select id="id_trabajo" name="id_trabajo" required>
                <option value="">-- Seleccione --</option>
                <?php foreach ($trabajos as $trabajo): ?>
                    <option
                        value="<?= (int) $trabajo['id_trabajo'] ?>"
                        <?= (isset($equipo['id_trabajo']) && (int) $equipo['id_trabajo'] === (int) $trabajo['id_trabajo']) ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($trabajo['codigo_trabajo']) ?> — <?= htmlspecialchars($trabajo['proyecto']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="campo">
            <label for="cantidad_equipos_total">N.° Equipos</label>
            <input
                type="text"
                id="cantidad_equipos_total"
                readonly
                value="<?= $esEdicion ? (int) $equipo['cantidad_equipos'] : 0 ?>"
            >
            <!-- Este campo es solo informativo: se recalcula en vivo desde
                 las filas de "Equipos utilizados" y no se envía al servidor
                 (no tiene atributo "name"). El total real lo calcula el
                 modelo a partir de equipos_detalle. -->
        </div>

        <div class="campo">
            <label for="contacto">Contacto (Nombre)</label>
            <input
                type="text"
                id="contacto"
                name="contacto"
                value="<?= valorCampoEquipo($equipo, 'contacto') ?>"
                required
            >
        </div>

        <div class="campo">
            <label for="telefono_contacto">Teléfono de Contacto</label>
            <input
                type="text"
                id="telefono_contacto"
                name="telefono_contacto"
                placeholder="Ej: 987 654 321"
                value="<?= valorCampoEquipo($equipo, 'telefono_contacto') ?>"
            >
        </div>

        <div class="campo">
            <label for="encargado">Encargado</label>
            <!-- TODO: cuando exista el módulo de Usuarios, reemplazar por
                 un <select> con el listado real (mismo criterio que
                 id_responsable en el formulario de Trabajos). Por ahora
                 queda como texto libre con valor por defecto. -->
            <input
                type="text"
                id="encargado"
                name="encargado"
                value="<?= valorCampoEquipo($equipo, 'encargado', 'Ingrid Castillo') ?>"
                required
            >
        </div>

        <div class="campo">
            <label for="fecha_salida">Fecha Salida</label>
            <input
                type="date"
                id="fecha_salida"
                name="fecha_salida"
                value="<?= valorCampoEquipo($equipo, 'fecha_salida') ?>"
                required
            >
        </div>

        <div class="campo">
            <label for="hora_salida">Hora Salida</label>
            <input
                type="time"
                id="hora_salida"
                name="hora_salida"
                value="<?= valorCampoEquipo($equipo, 'hora_salida') ?>"
                required
            >
        </div>

        <div class="campo">
            <label for="fecha_regreso">Fecha Regreso</label>
            <input
                type="date"
                id="fecha_regreso"
                name="fecha_regreso"
                value="<?= valorCampoEquipo($equipo, 'fecha_regreso') ?>"
            >
        </div>

        <div class="campo">
            <label for="hora_regreso">Hora Regreso</label>
            <input
                type="time"
                id="hora_regreso"
                name="hora_regreso"
                value="<?= valorCampoEquipo($equipo, 'hora_regreso') ?>"
            >
        </div>

        <div class="campo">
            <label for="tiempo">Tiempo</label>
            <input
                type="text"
                id="tiempo"
                name="tiempo"
                placeholder="Ej: 3 días, 5 horas..."
                value="<?= valorCampoEquipo($equipo, 'tiempo') ?>"
            >
        </div>

        <div class="campo">
            <label for="costo">Costo (S/)</label>
            <input
                type="number"
                id="costo"
                name="costo"
                step="0.01"
                min="0"
                value="<?= valorCampoEquipo($equipo, 'costo', '0.00') ?>"
            >
        </div>

        <div class="campo">
            <label for="pago_1">Pago 1 (S/)</label>
            <input
                type="number"
                id="pago_1"
                name="pago_1"
                step="0.01"
                min="0"
                value="<?= valorCampoEquipo($equipo, 'pago_1', '0.00') ?>"
            >
        </div>

        <div class="campo">
            <label for="pago_2">Pago 2 (S/)</label>
            <input
                type="number"
                id="pago_2"
                name="pago_2"
                step="0.01"
                min="0"
                value="<?= valorCampoEquipo($equipo, 'pago_2', '0.00') ?>"
            >
        </div>

        <div class="campo">
            <label for="estado">Estado</label>
            <select id="estado" name="estado">
                <?php foreach (EquipoController::ESTADOS_VALIDOS as $estadoOpcion): ?>
                    <option
                        value="<?= htmlspecialchars($estadoOpcion) ?>"
                        <?= (isset($equipo['estado']) && $equipo['estado'] === $estadoOpcion) ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($estadoOpcion) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="campo campo-ancho-completo">
            <label>Equipos Utilizados</label>

            <div class="tabla-wrapper">
                <table class="tabla-trabajos" id="tablaEquiposUtilizados">
                    <thead>
                        <tr>
                            <th>Tipo de equipo</th>
                            <th>Equipo / Marca</th>
                            <th>Cantidad</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="filasEquiposUtilizados">
                        <!-- Las filas se generan por JavaScript, tanto las
                             iniciales (si es edición) como las que el
                             usuario agregue con "+ Agregar equipo". -->
                    </tbody>
                </table>
            </div>

            <div class="acciones-formulario">
                <button type="button" id="btnAgregarEquipo" class="btn btn-secundario">
                    + Agregar equipo
                </button>
            </div>
        </div>

        <div class="acciones-formulario">
            <button type="submit" class="btn btn-primario">
                <?= $esEdicion ? 'Actualizar Registro' : 'Guardar Registro' ?>
            </button>
            <a href="/equipos" class="btn btn-secundario">Cancelar</a>
        </div>

    </form>

</main>

<script>
(function () {
    const catalogoEquipos = <?= json_encode($catalogoEquipos, JSON_UNESCAPED_UNICODE) ?>;
    const tiposEquipo = <?= json_encode($tiposEquipo, JSON_UNESCAPED_UNICODE) ?>;
    const filasIniciales = <?= json_encode($filasIniciales, JSON_UNESCAPED_UNICODE) ?>;

    const cuerpoTabla = document.getElementById('filasEquiposUtilizados');
    const totalInput = document.getElementById('cantidad_equipos_total');
    let contadorFilas = 0;

    function opcionesTipoEquipo(tipoSeleccionado) {
        let html = '<option value="">-- Tipo --</option>';
        tiposEquipo.forEach(function (tipo) {
            const seleccionado = tipo === tipoSeleccionado ? 'selected' : '';
            html += '<option value="' + tipo + '" ' + seleccionado + '>' + tipo + '</option>';
        });
        return html;
    }

    function opcionesEquipoMarca(tipoSeleccionado, idCatalogoSeleccionado) {
        let html = '<option value="">-- Equipo / Marca --</option>';
        catalogoEquipos
            .filter(function (item) { return item.tipo_equipo === tipoSeleccionado; })
            .forEach(function (item) {
                const seleccionado = String(item.id_catalogo_equipo) === String(idCatalogoSeleccionado) ? 'selected' : '';
                html += '<option value="' + item.id_catalogo_equipo + '" ' + seleccionado + '>' + item.equipo_marca + '</option>';
            });
        return html;
    }

    function agregarFila(datosFila) {
        datosFila = datosFila || {};
        const indice = contadorFilas++;

        const fila = document.createElement('tr');
        fila.innerHTML =
            '<td>' +
                '<select class="select-tipo-equipo">' + opcionesTipoEquipo(datosFila.tipo_equipo || '') + '</select>' +
            '</td>' +
            '<td>' +
                '<select name="equipos_utilizados[' + indice + '][id_catalogo_equipo]" class="select-equipo-marca">' +
                    opcionesEquipoMarca(datosFila.tipo_equipo || '', datosFila.id_catalogo_equipo || '') +
                '</select>' +
            '</td>' +
            '<td>' +
                '<input type="number" min="1" name="equipos_utilizados[' + indice + '][cantidad]" ' +
                    'value="' + (datosFila.cantidad || 1) + '" class="input-cantidad-equipo">' +
            '</td>' +
            '<td class="acciones">' +
                '<a href="#" title="Quitar" class="btn-quitar-equipo">🗑️</a>' +
            '</td>';

        cuerpoTabla.appendChild(fila);

        const selectTipo = fila.querySelector('.select-tipo-equipo');
        const selectMarca = fila.querySelector('.select-equipo-marca');
        const inputCantidad = fila.querySelector('.input-cantidad-equipo');
        const botonQuitar = fila.querySelector('.btn-quitar-equipo');

        selectTipo.addEventListener('change', function () {
            selectMarca.innerHTML = opcionesEquipoMarca(selectTipo.value, '');
            recalcularTotal();
        });

        selectMarca.addEventListener('change', recalcularTotal);
        inputCantidad.addEventListener('input', recalcularTotal);

        botonQuitar.addEventListener('click', function (evento) {
            evento.preventDefault();
            fila.remove();
            recalcularTotal();
        });
    }

    function recalcularTotal() {
        let total = 0;
        document.querySelectorAll('.input-cantidad-equipo').forEach(function (input) {
            total += parseInt(input.value, 10) || 0;
        });
        totalInput.value = total;
    }

    document.getElementById('btnAgregarEquipo').addEventListener('click', function () {
        agregarFila();
    });

    // Carga inicial: filas existentes (edición) o una fila vacía (nuevo registro).
    if (filasIniciales.length > 0) {
        filasIniciales.forEach(function (fila) {
            agregarFila(fila);
        });
    } else {
        agregarFila();
    }

    recalcularTotal();

    // Evita enviar filas totalmente vacías si el usuario agregó una fila
    // de más con "+ Agregar equipo" y no la completó.
    document.getElementById('formEquipos').addEventListener('submit', function () {
        document.querySelectorAll('#filasEquiposUtilizados tr').forEach(function (fila) {
            const marca = fila.querySelector('.select-equipo-marca');
            const cantidad = fila.querySelector('.input-cantidad-equipo');
            if (marca.value === '' && (cantidad.value === '' || cantidad.value === '0')) {
                fila.remove();
            }
        });
    });
})();
</script>

</body>
</html>