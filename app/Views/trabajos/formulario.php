<?php
/**
 * Vista: Formulario de Trabajo (Registrar / Editar)
 * -----------------------------------------------------
 * Variables que espera recibir (ya resueltas por el Controlador):
 *
 * array   $responsables   Lista de responsables para el dropdown
 *                          (cada uno: ['id_usuario' => .., 'nombre_usuario' => ..])
 * array   $clientes       Lista de clientes para el dropdown
 *                          (cada uno: ['id_cliente' => .., 'nombre_cliente' => ..])
 * ?array  $trabajo        Datos del trabajo a editar, o null si es "Nuevo Trabajo"
 * array   $errores        Lista de mensajes de error de validación (vacío si no hay)
 *
 * Esta vista NO consulta la base de datos, NO valida datos.
 * Solo pinta lo que le llega y decide si es modo "crear" o "editar"
 * según si $trabajo existe o no.
 *
 * IMPORTANTE: los nombres de los campos del <form> coinciden
 * exactamente con lo que espera TrabajoController::registrar()
 * y TrabajoController::actualizar():
 * id_cliente, proyecto, descripcion, ubicacion, id_responsable,
 * precio_neto, fecha_inicio, fecha_fin.
 * -----------------------------------------------------
 */

$esEdicion = !empty($trabajo);

$tituloPagina = $esEdicion ? 'Editar Trabajo' : 'Nuevo Trabajo';

$accionFormulario = $esEdicion
    ? '/trabajos/actualizar/' . (int) $trabajo['id_trabajo']
    : '/trabajos/guardar';

function valorCampo(?array $trabajo, string $campo): string
{
    return htmlspecialchars($trabajo[$campo] ?? '');
}
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
            <p class="subtitulo">Gestión de trabajos / proyectos</p>
        </div>
        <a href="/trabajos" class="btn btn-secundario">← Volver al listado</a>
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

    <form action="<?= $accionFormulario ?>" method="POST" class="form-trabajo">

        <?php if ($esEdicion): ?>
            <input type="hidden" name="id_trabajo" value="<?= (int) $trabajo['id_trabajo'] ?>">
        <?php endif; ?>

        <div class="campo">
            <label for="cliente">Cliente</label>

            <input
                type="text"
                id="cliente"
                name="cliente"
                list="listaClientes"
                value="<?= htmlspecialchars($trabajo['nombre_cliente'] ?? '') ?>"
                autocomplete="off"
                required
            >
<!-- Modal: Nuevo Cliente -->
            <datalist id="listaClientes">
                <?php foreach ($clientes as $cliente): ?>
                    <option value="<?= htmlspecialchars($cliente['nombre_cliente']) ?>">
                <?php endforeach; ?>
            </datalist>
        </div>

        <div class="campo">
            <label for="proyecto">Proyecto</label>
            <input
                type="text"
                id="proyecto"
                name="proyecto"
                value="<?= valorCampo($trabajo, 'proyecto') ?>"
                required
            >
        </div>

        <div class="campo campo-ancho-completo">
            <label for="descripcion">Descripción</label>
            <textarea
                id="descripcion"
                name="descripcion"
                rows="3"
                required
            ><?= valorCampo($trabajo, 'descripcion') ?></textarea>
        </div>

        <div class="campo">
            <label for="ubicacion">Ubicación</label>
            <input
                type="text"
                id="ubicacion"
                name="ubicacion"
                value="<?= valorCampo($trabajo, 'ubicacion') ?>"
                required
            >
        </div>

        <div class="campo">
            <label for="id_responsable">Responsable</label>
            <select id="id_responsable" name="id_responsable" required>
                <option value="">-- Seleccione --</option>
                <?php foreach ($responsables as $responsable): ?>
                    <option
                        value="<?= (int) $responsable['id_usuario'] ?>"
                        <?= (isset($trabajo['id_responsable']) && (int) $trabajo['id_responsable'] === (int) $responsable['id_usuario']) ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($responsable['nombre_usuario']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="campo">
            <label for="precio_neto">Precio Neto (S/)</label>
            <input
                type="number"
                id="precio_neto"
                name="precio_neto"
                step="0.01"
                min="0"
                value="<?= valorCampo($trabajo, 'precio_neto') ?>"
                required
            >
        </div>

        <?php if ($esEdicion): ?>

            <div class="campo campo-estado-independiente">
                <label for="estado">Estado</label>
                <form action="/trabajos/cambiar-estado/<?= (int) $trabajo['id_trabajo'] ?>" method="POST" class="form-inline-estado">
                    <select id="estado" name="estado" onchange="this.form.submit()">
                        <?php foreach (TrabajoController::ESTADOS_VALIDOS as $estado): ?>
                            <option
                                value="<?= htmlspecialchars($estado) ?>"
                                <?= ($trabajo['estado'] === $estado) ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($estado) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        <?php endif; ?>

        <div class="campo">
            <label for="fecha_inicio">Fecha Inicio</label>
            <input
                type="date"
                id="fecha_inicio"
                name="fecha_inicio"
                value="<?= valorCampo($trabajo, 'fecha_inicio') ?>"
                required
            >
        </div>

        <div class="campo">
            <label for="fecha_fin">Fecha Fin</label>
            <input
                type="date"
                id="fecha_fin"
                name="fecha_fin"
                value="<?= valorCampo($trabajo, 'fecha_fin') ?>"
                required
            >
        </div>

        <div class="acciones-formulario">
            <button type="submit" class="btn btn-primario">
                <?= $esEdicion ? 'Actualizar Trabajo' : 'Guardar Trabajo' ?>
            </button>
            <a href="/trabajos" class="btn btn-secundario">Cancelar</a>
        </div>

    </form>
</main>

<script>
(function () {
    const modal = document.getElementById('modalNuevoCliente');
    const selectCliente = document.getElementById('id_cliente');
    const errorBox = document.getElementById('modalClienteError');

    function abrirModal() {
        errorBox.textContent = '';
        modal.classList.remove('modal-oculto');
    }

    function cerrarModal() {
        modal.classList.add('modal-oculto');
        document.getElementById('modal_nombre_cliente').value = '';
        document.getElementById('modal_ruc').value = '';
        document.getElementById('modal_telefono').value = '';
        document.getElementById('modal_correo').value = '';
        document.getElementById('modal_observaciones').value = '';
        errorBox.textContent = '';
    }

    document.getElementById('btnAbrirModalCliente').addEventListener('click', abrirModal);
    document.getElementById('btnCerrarModalCliente').addEventListener('click', cerrarModal);
    document.getElementById('btnCancelarModalCliente').addEventListener('click', cerrarModal);

    document.getElementById('btnGuardarModalCliente').addEventListener('click', function () {
        const nombre = document.getElementById('modal_nombre_cliente').value.trim();

        if (nombre === '') {
            errorBox.textContent = 'El nombre del cliente es obligatorio.';
            return;
        }

        const datos = new URLSearchParams();
        datos.append('nombre_cliente', nombre);
        datos.append('ruc', document.getElementById('modal_ruc').value.trim());
        datos.append('telefono', document.getElementById('modal_telefono').value.trim());
        datos.append('correo', document.getElementById('modal_correo').value.trim());
        datos.append('observaciones', document.getElementById('modal_observaciones').value.trim());

        fetch('/clientes/guardar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: datos
        })
            .then(function (respuesta) { return respuesta.json(); })
            .then(function (data) {
                if (!data.success) {
                    errorBox.textContent = data.message || 'No se pudo guardar el cliente.';
                    return;
                }

                const nuevaOpcion = document.createElement('option');
                nuevaOpcion.value = data.id;
                nuevaOpcion.textContent = data.nombre_cliente;
                nuevaOpcion.selected = true;
                selectCliente.appendChild(nuevaOpcion);

                cerrarModal();
            })
            .catch(function () {
                errorBox.textContent = 'Ocurrió un error al conectar con el servidor.';
            });
    });
})();
</script>

</body>
</html>