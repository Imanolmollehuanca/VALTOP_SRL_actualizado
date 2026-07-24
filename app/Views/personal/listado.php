<?php
/**
 * Vista: Listado de Personal
 * -----------------------------------------------------
 * array  $personal     Lista de trabajadores, cada uno ya trae
 *                        'responsable_nombre' gracias al JOIN hecho
 *                        en Personal::listarPorEstado().
 * string $estadoActual Estado del filtro activo ('Todos' por defecto).
 * -----------------------------------------------------
 */

function claseEstadoPersonal(string $estado): string
{
    $mapa = [
        'Activo'     => 'badge-terminado',
        'Inactivo'   => 'badge-default',
        'Vacaciones' => 'badge-pendiente',
    ];

    return $mapa[$estado] ?? 'badge-default';
}

/** Junta dos valores de texto en dos líneas dentro de la misma celda. */
function celdaDobleLinea(?string $lineaUno, ?string $lineaDos): string
{
    if (empty($lineaUno)) {
        return '—';
    }

    $html = htmlspecialchars($lineaUno);

    if (!empty($lineaDos)) {
        $html .= '<br><small>' . htmlspecialchars($lineaDos) . '</small>';
    }

    return $html;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Personal - Valtop SRL</title>
    <link rel="stylesheet" href="/assets/css/trabajos.css">
</head>
<body>

<main class="contenido">

    <div class="encabezado-pagina">
        <div>
            <h1>Personal</h1>
            <p class="subtitulo">Registro del personal de la empresa.</p>
        </div>
        <a href="/modulos" class="btn btn-secundario">← Volver a Módulos</a>
    </div>

    <div class="barra-acciones">
        <div class="barra-acciones-botones">
            <a href="/personal/nuevo" class="btn btn-primario">
                + Agregar Personal
            </a>
        </div>
    </div>

    <div class="tabla-wrapper">
        <table class="tabla-trabajos">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Teléfono</th>
                    <th>Personal</th>
                    <th>Responsable</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($personal)): ?>
                    <tr>
                        <td colspan="6" class="sin-datos">
                            No hay personal registrado que coincida con el filtro aplicado.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($personal as $trabajador): ?>
                        <tr>
                            <td><?= htmlspecialchars($trabajador['nombre_completo']) ?></td>
                            <td class="celda-doble">
                                <?= celdaDobleLinea($trabajador['telefono_principal'], $trabajador['telefono_secundario']) ?>
                            </td>
                            <td class="celda-doble">
                                <?= celdaDobleLinea($trabajador['cargo'], $trabajador['telefono_contacto']) ?>
                            </td>
                            <td><?= htmlspecialchars($trabajador['responsable_nombre'] ?? '—') ?></td>
                            <td>
                                <span class="badge <?= claseEstadoPersonal($trabajador['estado']) ?>">
                                    <?= htmlspecialchars($trabajador['estado']) ?>
                                </span>
                            </td>
                            <td class="acciones">
                                <a href="/personal/editar/<?= (int) $trabajador['id_personal'] ?>" title="Editar">✏️</a>
                                <a href="#"
                                   title="Eliminar"
                                   class="btn-eliminar-personal"
                                   data-id="<?= (int) $trabajador['id_personal'] ?>">🗑️</a>
                            </td>
                        </tr>

                        <!-- Formulario oculto para eliminar este registro por POST -->
                        <form id="formEliminarPersonal<?= (int) $trabajador['id_personal'] ?>"
                              action="/personal/eliminar/<?= (int) $trabajador['id_personal'] ?>"
                              method="POST" style="display:none;"></form>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>

<script>
(function () {
    document.querySelectorAll('.btn-eliminar-personal').forEach(function (boton) {
        boton.addEventListener('click', function (evento) {
            evento.preventDefault();

            const confirmado = confirm('¿Seguro que deseas eliminar este registro de personal? Esta acción no se puede deshacer.');
            if (!confirmado) {
                return;
            }

            document.getElementById('formEliminarPersonal' + boton.dataset.id).submit();
        });
    });
})();
</script>

</body>
</html>