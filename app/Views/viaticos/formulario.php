<?php
/**
 * Vista: Formulario de Viáticos
 *
 * Variables recibidas:
 * $viatico (opcional cuando es edición)
 */

$esEdicion = isset($viatico);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?= $esEdicion ? 'Editar Viático' : 'Agregar Viático' ?></title>
    <link rel="stylesheet" href="/assets/css/trabajos.css">
</head>

<body>

<main class="contenido">

    <div class="encabezado-pagina">
        <div>
            <h1><?= $esEdicion ? 'Editar Viático' : 'Agregar Viático' ?></h1>
            <p class="subtitulo">Registro de gastos de viáticos.</p>
        </div>

        <a href="/viaticos" class="btn btn-secundario">
            ← Volver
        </a>
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

    <form
        method="POST"
        action="<?= $esEdicion
            ? '/viaticos/actualizar/' . (int)$viatico['id_viatico']
            : '/viaticos/guardar'; ?>"
        class="form-trabajo"
    >

        <!-- TRABAJO -->
        <div class="campo">
            <label for="buscarTrabajo">Trabajo</label>

            <input
                type="hidden"
                id="id_trabajo"
                name="id_trabajo"
                value="<?= $viatico['id_trabajo'] ?? '' ?>"
            >

            <div style="position: relative;">
                <input
                    type="text"
                    id="buscarTrabajo"
                    autocomplete="off"
                    placeholder="Escribir trabajo..."
                    value="<?= htmlspecialchars($viatico['trabajo_texto'] ?? '') ?>"
                    required
                >

                <div id="listaTrabajos" class="autocomplete-lista"></div>
            </div>
        </div>

        <!-- FECHA -->
        <div class="campo">
            <label for="fecha">Fecha</label>

            <input
                type="date"
                id="fecha"
                name="fecha"
                required
                value="<?= $viatico['fecha'] ?? date('Y-m-d') ?>"
            >
        </div>

        <!-- CONCEPTO -->
        <div class="campo">
            <label for="concepto">Concepto</label>

            <select id="concepto" name="concepto" required>
                <?php
                $conceptos = [
                    'Alimentación',
                    'Hospedaje',
                    'Agua',
                    'Movilidad',
                    'Peajes',
                    'Combustible',
                    'Pasajes',
                    'Otros'
                ];

                foreach ($conceptos as $concepto):
                ?>
                    <option
                        value="<?= $concepto ?>"
                        <?= (($viatico['concepto'] ?? '') === $concepto) ? 'selected' : '' ?>
                    >
                        <?= $concepto ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- DESCRIPCIÓN -->
        <div class="campo">
            <label for="descripcion">Descripción</label>

            <input
                type="text"
                id="descripcion"
                name="descripcion"
                value="<?= htmlspecialchars($viatico['descripcion'] ?? '') ?>"
            >
        </div>

        <!-- MONTO -->
        <div class="campo">
            <label for="monto">Monto (S/.)</label>

            <input
                type="number"
                step="0.01"
                min="0"
                id="monto"
                name="monto"
                required
                value="<?= $viatico['monto'] ?? '' ?>"
            >
        </div>

        <!-- ESTADO -->
        <div class="campo">
            <label for="estado">Estado</label>

            <select id="estado" name="estado">
                <?php
                $estados = ['Pendiente', 'Pagado', 'Anulado'];

                foreach ($estados as $estado):
                ?>
                    <option
                        value="<?= $estado ?>"
                        <?= (($viatico['estado'] ?? '') === $estado) ? 'selected' : '' ?>
                    >
                        <?= $estado ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- OBSERVACIONES -->
        <div class="campo campo-ancho-completo">
            <label for="observaciones">Observaciones</label>

            <textarea
                id="observaciones"
                name="observaciones"
                rows="4"
            ><?= htmlspecialchars($viatico['observaciones'] ?? '') ?></textarea>
        </div>

        <!-- BOTONES -->
        <div class="acciones-formulario">

            <button class="btn btn-primario" type="submit">
                Guardar
            </button>

            <a href="/viaticos" class="btn btn-secundario">
                Cancelar
            </a>

        </div>

    </form>

</main>

<script>
const txtTrabajo = document.getElementById('buscarTrabajo');
const lista = document.getElementById('listaTrabajos');
const idTrabajo = document.getElementById('id_trabajo');

txtTrabajo.addEventListener('input', async function () {

    const texto = this.value.trim();

    if (texto.length < 2) {
        lista.innerHTML = '';
        return;
    }

    const respuesta = await fetch(
        '/trabajos/buscar-autocompletado?q=' +
        encodeURIComponent(texto)
    );

    const datos = await respuesta.json();

    lista.innerHTML = '';

    datos.forEach(function (trabajo) {

        const item = document.createElement('div');
        item.className = 'autocomplete-item';

        item.innerHTML =
            '<strong>' + trabajo.codigo + '</strong><br>' +
            '<small>' + trabajo.proyecto + '</small>';

        item.onclick = function () {

            txtTrabajo.value =
                trabajo.codigo + ' | ' + trabajo.proyecto;

            idTrabajo.value = trabajo.id;
            lista.innerHTML = '';

        };

        lista.appendChild(item);

    });

});

document.addEventListener('click', function (e) {
    if (!lista.contains(e.target) && e.target !== txtTrabajo) {
        lista.innerHTML = '';
    }
});
</script>

</body>
</html>