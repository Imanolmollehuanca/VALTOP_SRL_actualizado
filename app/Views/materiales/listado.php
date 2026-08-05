<?php
/**
 * Vista: Módulo Materiales — Tabla principal de trabajos
 * -----------------------------------------------------
*array $resumen  Lista de todos los trabajos con su
*                 responsable, estado y costo total
*                de materiales.
 * -----------------------------------------------------
 */

function claseEstadoMaterial(string $estado): string
{
    $mapa = [
        'Pendiente' => 'badge-pendiente',
        'Terminado' => 'badge-terminado',
        'Cobrado'   => 'badge-cobrado',
        'Fracaso'   => 'badge-fracaso',
    ];

    return $mapa[$estado] ?? 'badge-default';
}

function formatearMontoMaterial($monto): string
{
    return number_format((float) $monto, 2);
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Materiales - Valtop SRL</title>
    <link rel="stylesheet" href="/assets/css/trabajos.css">
</head>
<body>

<main class="contenido">

    <div class="encabezado-pagina">
        <div>
            <h1>Materiales</h1>
            <p class="subtitulo">Gestión de materiales por trabajo</p>
        </div>
        <a href="/modulos" class="btn btn-secundario">← Volver a Módulos</a>
    </div>

    <div class="tabla-wrapper">
        <table class="tabla-trabajos">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Proyecto</th>
                    <th>Responsable</th>
                    <th>Estado</th>
                    <th>Costo Materiales (S/)</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($resumen)): ?>
                    <tr>
                        <td colspan="6" class="sin-datos">
                            No hay trabajos registrados todavía.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($resumen as $fila): ?>
                        <tr>
                            <td><?= htmlspecialchars($fila['codigo_trabajo']) ?></td>
                            <td><?= htmlspecialchars($fila['proyecto']) ?></td>
                            <td><?= htmlspecialchars($fila['nombre_responsable'] ?? '—') ?></td>
                            <td>
                                <span class="badge <?= claseEstadoMaterial($fila['estado']) ?>">
                                    <?= htmlspecialchars($fila['estado']) ?>
                                </span>
                            </td>
                            <td class="columna-precio"><?= formatearMontoMaterial($fila['costo_materiales']) ?></td>
                            <td class="acciones">
                                <a href="/materiales/ver/<?= (int) $fila['id_trabajo'] ?>" title="Ver">👁️</a>
                                <a href="/materiales/trabajo/<?= (int) $fila['id_trabajo'] ?>" title="Materiales">📦</a>
                                <a href="/trabajos/editar/<?= (int) $fila['id_trabajo'] ?>" title="Editar">✏️</a>
                                <a href="#"
                                   title="Eliminar materiales de este trabajo"
                                   class="btn-eliminar-materiales"
                                   data-id="<?= (int) $fila['id_trabajo'] ?>">🗑️</a>
                            </td>
                        </tr>

                        <!-- Formulario oculto: elimina TODOS los materiales de este
                             trabajo (no el trabajo en sí) -->
                        <form id="formEliminarMateriales<?= (int) $fila['id_trabajo'] ?>"
                              action="/materiales/eliminar-todos/<?= (int) $fila['id_trabajo'] ?>"
                              method="POST" style="display:none;"></form>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>

<script>
(function () {
    document.querySelectorAll('.btn-eliminar-materiales').forEach(function (boton) {
        boton.addEventListener('click', function (evento) {
            evento.preventDefault();

            const confirmado = confirm('¿Seguro que deseas eliminar TODOS los materiales registrados de este trabajo? El trabajo en sí no se eliminará. Esta acción no se puede deshacer.');
            if (!confirmado) {
                return;
            }

            document.getElementById('formEliminarMateriales' + boton.dataset.id).submit();
        });
    });
})();
</script>

</body>
</html>