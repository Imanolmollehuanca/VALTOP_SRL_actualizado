<?php
/**
 * Vista: Listado de Trabajos
 * -----------------------------------------------------
 * Variables que espera recibir (ya resueltas por el Controlador):
 *
 * array  $trabajos             Lista de trabajos a mostrar
 * array  $responsables         Lista de responsables para el dropdown
 *                               (cada uno: ['id_usuario' => .., 'nombre_usuario' => ..])
 * string $estadoActual         Estado del filtro activo ('Todos' por defecto)
 * ?int   $idResponsableActual  ID del responsable filtrado, o null
 * string $busquedaActual       Texto de búsqueda activo, o ''
 *
 * Esta vista NO consulta la base de datos, NO valida datos.
 * Solo pinta lo que le llega.
 * -----------------------------------------------------
 */

/** Mapa de estado -> clase CSS del badge. */
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Trabajos - Valtop SRL</title>
    <link rel="stylesheet" href="/assets/css/trabajos.css">
</head>
<body>

<main class="contenido">

    <div class="encabezado-pagina">
        <div>
            <h1>Trabajos</h1>
            <p class="subtitulo">Gestión de trabajos / proyectos</p>
        </div>
    </div>

    <div class="barra-acciones">
        <div class="barra-acciones-botones">
            <a href="/trabajos/nuevo" class="btn btn-primario">
                + Nuevo Trabajo
            </a>

            <a href="/modulos" class="btn btn-secundario">
                📂 Módulos
            </a>
        </div>

        <div class="barra-acciones-derecha">
            <form action="/trabajos" method="GET" class="barra-busqueda">
                <input
                    type="text"
                    name="buscar"
                    placeholder="Buscar trabajo..."
                    value="<?= htmlspecialchars($busquedaActual) ?>"
                >
                <button type="submit" class="btn btn-icono">🔍</button>
            </form>

            <div class="menu-superior-contenedor" data-menu-contenedor>
                <button type="button" class="btn btn-secundario btn-menu-superior" data-menu-trigger aria-haspopup="true" aria-expanded="false">
                    ⋮
                </button>
                <div class="menu-desplegable menu-desplegable-superior menu-oculto" data-menu-panel>
                    <a href="/trabajos/exportar?estado=<?= urlencode($estadoActual) ?>&responsable=<?= (int) $idResponsableActual ?>&buscar=<?= urlencode($busquedaActual) ?>" class="menu-item">📤 Exportar</a>
                    <a href="/trabajos/imprimir?estado=<?= urlencode($estadoActual) ?>&responsable=<?= (int) $idResponsableActual ?>&buscar=<?= urlencode($busquedaActual) ?>" class="menu-item">🖨️ Imprimir</a>
                    <a href="/trabajos/papelera" class="menu-item menu-item-peligro">🗑️ Papelera</a>
                </div>
            </div>
        </div>
    </div>

    <div class="barra-filtros">

        <div class="filtro-estados">
            <span class="filtro-etiqueta">Estado:</span>

            <?php
            // Armamos la lista de tabs a partir de la constante del
            // Controlador, agregando "Todos" al inicio manualmente.
            $tabs = array_merge(['Todos'], TrabajoController::ESTADOS_VALIDOS);

            foreach ($tabs as $tab):
                $activo = ($tab === $estadoActual) ? 'tab-activo' : '';
            ?>
                <a href="?estado=<?= urlencode($tab) ?>&responsable=<?= (int) $idResponsableActual ?>&buscar=<?= urlencode($busquedaActual) ?>"
                   class="tab-estado <?= $activo ?> <?= $tab !== 'Todos' ? claseEstado($tab) : '' ?>">
                    <?= htmlspecialchars($tab) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <form action="/trabajos" method="GET" class="filtro-responsable">
            <label for="responsable">Responsable:</label>
            <select name="responsable" id="responsable" onchange="this.form.submit()">
                <option value="0" <?= $idResponsableActual === null ? 'selected' : '' ?>>Todos</option>
                <?php foreach ($responsables as $responsable): ?>
                    <option
                        value="<?= (int) $responsable['id_usuario'] ?>"
                        <?= $idResponsableActual === (int) $responsable['id_usuario'] ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($responsable['nombre_usuario']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" name="estado" value="<?= htmlspecialchars($estadoActual) ?>">
        </form>

    </div>

    <table class="tabla-trabajos">
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
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($trabajos)): ?>
                <tr>
                    <td colspan="9" class="sin-datos">
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
                        <td class="acciones">
                            <a href="/trabajos/expediente/<?= (int) $trabajo['id_trabajo'] ?>" title="Ver expediente" class="btn-accion-fila">👁️</a>

                            <div class="menu-fila-contenedor" data-menu-contenedor>
                                <button type="button" class="btn-accion-fila btn-menu-fila" data-menu-trigger aria-haspopup="true" aria-expanded="false" title="Más acciones">
                                    ⋮
                                </button>
                                <div class="menu-desplegable menu-desplegable-fila menu-oculto" data-menu-panel>
                                    <a href="/trabajos/editar/<?= (int) $trabajo['id_trabajo'] ?>" class="menu-item">✏️ Editar</a>
                                    <form action="/trabajos/eliminar/<?= (int) $trabajo['id_trabajo'] ?>"
                                        method="POST"
                                        class="menu-form-eliminar"
                                        onsubmit="return confirm('¿Desea eliminar el trabajo <?= htmlspecialchars($trabajo['codigo_trabajo']) ?>?');">
                                        <button type="submit" class="menu-item menu-item-peligro">
                                            🗑️ Eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="leyenda-estados">
        <?php foreach (TrabajoController::ESTADOS_VALIDOS as $estado): ?>
            <span class="leyenda-item">
                <span class="punto <?= claseEstado($estado) ?>"></span> <?= htmlspecialchars($estado) ?>
            </span>
        <?php endforeach; ?>
    </div>

    <!-- Modal: Nuevo Cliente (catálogo simple, no es un módulo aparte) -->
    <div id="modalNuevoCliente" class="modal-overlay modal-oculto">
        <div class="modal-caja">
            <div class="modal-encabezado">
                <h2>Nuevo Cliente</h2>
                <button type="button" class="btn-cerrar-modal" id="btnCerrarModalCliente">&times;</button>
            </div>

            <div class="modal-error" id="modalClienteError"></div>
            <div class="modal-exito modal-oculto" id="modalClienteExito"></div>

            <div class="campo">
                <label for="modal_nombre_cliente">Nombre / Razón Social *</label>
                <input type="text" id="modal_nombre_cliente" required>
            </div>

            <div class="campo">
                <label for="modal_ruc">RUC</label>
                <input type="text" id="modal_ruc">
            </div>

            <div class="campo">
                <label for="modal_telefono">Teléfono</label>
                <input type="text" id="modal_telefono">
            </div>

            <div class="campo campo-ancho-completo">
                <label for="modal_correo">Correo</label>
                <input type="email" id="modal_correo">
            </div>

            <div class="campo campo-ancho-completo">
                <label for="modal_observaciones">Observaciones</label>
                <textarea id="modal_observaciones" rows="2"></textarea>
            </div>

            <div class="acciones-formulario">
                <button type="button" class="btn btn-primario" id="btnGuardarModalCliente">
                    Guardar Cliente
                </button>
                <button type="button" class="btn btn-secundario" id="btnCancelarModalCliente">
                    Cancelar
                </button>
            </div>
        </div>
    </div>

</main>

<script>
(function () {
    const modal = document.getElementById('modalNuevoCliente');
    const errorBox = document.getElementById('modalClienteError');
    const exitoBox = document.getElementById('modalClienteExito');

    function abrirModal() {
        errorBox.textContent = '';
        exitoBox.textContent = '';
        exitoBox.classList.add('modal-oculto');
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

    const btnAbrirModalCliente = document.getElementById('btnAbrirModalClienteListado');
    if (btnAbrirModalCliente) {
        btnAbrirModalCliente.addEventListener('click', abrirModal);
    }
    document.getElementById('btnCerrarModalCliente').addEventListener('click', cerrarModal);
    document.getElementById('btnCancelarModalCliente').addEventListener('click', cerrarModal);

    document.getElementById('btnGuardarModalCliente').addEventListener('click', function () {
        const nombre = document.getElementById('modal_nombre_cliente').value.trim();

        errorBox.textContent = '';

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

                exitoBox.textContent = 'Cliente "' + data.nombre_cliente + '" registrado. Ya está disponible en Nuevo Trabajo.';
                exitoBox.classList.remove('modal-oculto');

                setTimeout(cerrarModal, 1400);
            })
            .catch(function () {
                errorBox.textContent = 'Ocurrió un error al conectar con el servidor.';
            });
    });
})();

/**
 * Menús desplegables (⋮) — fila y superior.
 * Delegación de eventos: funciona para todas las filas actuales
 * y para cualquiera que se agregue dinámicamente en el futuro.
 */
(function () {
    function cerrarTodosLosMenus(exceptoPanel) {
        document.querySelectorAll('[data-menu-panel]').forEach(function (panel) {
            if (panel !== exceptoPanel) {
                panel.classList.add('menu-oculto');
                const trigger = panel.previousElementSibling;
                if (trigger && trigger.hasAttribute('data-menu-trigger')) {
                    trigger.setAttribute('aria-expanded', 'false');
                }
            }
        });
    }

    document.addEventListener('click', function (evento) {
        const trigger = evento.target.closest('[data-menu-trigger]');

        if (trigger) {
            const contenedor = trigger.closest('[data-menu-contenedor]');
            const panel = contenedor ? contenedor.querySelector('[data-menu-panel]') : null;

            if (!panel) {
                return;
            }

            const estaAbierto = !panel.classList.contains('menu-oculto');

            cerrarTodosLosMenus(null);

            if (!estaAbierto) {
                panel.classList.remove('menu-oculto');
                trigger.setAttribute('aria-expanded', 'true');
            }

            evento.stopPropagation();
            return;
        }

        // Clic fuera de cualquier menú: cerrar todos.
        if (!evento.target.closest('[data-menu-panel]')) {
            cerrarTodosLosMenus(null);
        }
    });

    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape') {
            cerrarTodosLosMenus(null);
        }
    });
})();
</script>

</body>
</html>