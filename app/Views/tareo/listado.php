<?php
/**
 * Vista: Listado de Tareo
 * -----------------------------------------------------
 * array $registros  Lista de registros de tareo, cada uno ya trae
 *                    'codigo_trabajo', 'nombre_trabajador' y
 *                    'nombre_responsable' gracias al JOIN hecho
 *                    en Tareo::listar().
 * -----------------------------------------------------
 */

/** Mapa de actividad (valor interno en BD) -> clase CSS del badge. */
function claseActividad(string $actividad): string
{
    $mapa = [
        'Campo'      => 'badge-terminado',
        'Dibujo'     => 'badge-cobrado',
        'Falto'      => 'badge-fracaso',
        'Vacaciones' => 'badge-pendiente',
    ];

    return $mapa[$actividad] ?? 'badge-default';
}

function etiquetaActividad(string $actividad): string
{
    $mapa = [
        'Campo'      => 'Campo',
        'Dibujo'     => 'Dibujo (D)',
        'Falto'      => 'Faltó',
        'Vacaciones' => 'Vacaciones',
    ];

    return $mapa[$actividad] ?? $actividad;
}

function formatearFechaTareo(string $fecha): string
{
    return date('d/m/Y', strtotime($fecha));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tareo - Valtop SRL</title>
    <link rel="stylesheet" href="/assets/css/trabajos.css">
</head>
<body>

<main class="contenido">

    <div class="encabezado-pagina">
        <div>
            <h1>Tareo</h1>
            <p class="subtitulo">Registro diario del personal por trabajo.</p>
        </div>
        <a href="/modulos" class="btn btn-secundario">← Volver a Módulos</a>
    </div>

    <div class="barra-acciones">
        <div class="barra-acciones-botones">
            <a href="/tareo/nuevo" class="btn btn-primario">
                + Registrar Tareo
            </a>
        </div>
    </div>

    <div class="tabla-wrapper">
        <table class="tabla-trabajos">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Trabajo</th>
                    <th>Trabajador</th>
                    <th>Actividad</th>
                    <th>Responsable</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($registros)): ?>
                    <tr>
                        <td colspan="6" class="sin-datos">
                            No hay registros de tareo todavía.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($registros as $registro): ?>
                        <tr>
                            <td><?= formatearFechaTareo($registro['fecha']) ?></td>
                            <td><?= htmlspecialchars($registro['codigo_trabajo']) ?></td>
                            <td><?= htmlspecialchars($registro['nombre_trabajador']) ?></td>
                            <td>
                                <span class="badge <?= claseActividad($registro['actividad']) ?>">
                                    <?= htmlspecialchars(etiquetaActividad($registro['actividad'])) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($registro['nombre_responsable'] ?? '—') ?></td>
                            <td class="acciones">
                                <a href="/tareo/editar/<?= (int) $registro['id_tareo'] ?>" title="Editar">✏️</a>
                                <a href="#"
                                   title="Eliminar"
                                   class="btn-eliminar-tareo"
                                   data-id="<?= (int) $registro['id_tareo'] ?>">🗑️</a>
                            </td>
                        </tr>

                        <!-- Formulario oculto para eliminar este registro por POST -->
                        <form id="formEliminarTareo<?= (int) $registro['id_tareo'] ?>"
                              action="/tareo/eliminar/<?= (int) $registro['id_tareo'] ?>"
                              method="POST" style="display:none;"></form>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>

<script>
(function () {
    document.querySelectorAll('.btn-eliminar-tareo').forEach(function (boton) {
        boton.addEventListener('click', function (evento) {
            evento.preventDefault();

            const confirmado = confirm('¿Seguro que deseas eliminar este registro de tareo? Esta acción no se puede deshacer.');
            if (!confirmado) {
                return;
            }

            document.getElementById('formEliminarTareo' + boton.dataset.id).submit();
        });
    });
})();
</script>

</body>
</html>