<?php
/**
 * Vista: Listado de Viáticos
 * -----------------------------------------------------
 * array $viaticos
 *      Lista de viáticos registrados.
 *
 * Cada registro contiene:
 *  - id_viatico
 *  - codigo_trabajo
 *  - proyecto_nombre
 *  - fecha
 *  - concepto
 *  - descripcion
 *  - monto
 *  - estado
 * -----------------------------------------------------
 */

function claseEstadoViatico(string $estado): string
{
    $mapa = [
        'Pagado'    => 'badge-terminado',
        'Pendiente' => 'badge-pendiente',
        'Anulado'   => 'badge-default',
    ];

    return $mapa[$estado] ?? 'badge-default';
}

/**
 * Muestra el trabajo en dos líneas:
 * TR-001
 * Levantamiento Topográfico Mina Sur
 */
function trabajoDosLineas(?string $codigo, ?string $proyecto): string
{
    if (empty($codigo)) {
        return '—';
    }

    $html = htmlspecialchars($codigo);

    if (!empty($proyecto)) {
        $html .= '<br><small>' . htmlspecialchars($proyecto) . '</small>';
    }

    return $html;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Viáticos - Valtop SRL</title>

    <link rel="stylesheet" href="/assets/css/trabajos.css">
</head>
<body>

<main class="contenido">

    <div class="encabezado-pagina">
        <div>
            <h1>Viáticos</h1>
            <p class="subtitulo">
                Registro de gastos de viáticos.
            </p>
        </div>

        <a href="/modulos" class="btn btn-secundario">
            ← Volver a Módulos
        </a>
    </div>

    <div class="barra-acciones">

        <div class="barra-acciones-botones">

            <a href="/viaticos/nuevo"
               class="btn btn-primario">
                + Agregar Viático
            </a>

        </div>

    </div>

    <div class="tabla-wrapper">

        <table class="tabla-trabajos">

            <thead>

            <tr>
                <th>Trabajo</th>
                <th>Fecha</th>
                <th>Concepto</th>
                <th>Descripción</th>
                <th>Monto (S/.)</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>

            </thead>

            <tbody>

            <?php if (empty($viaticos)): ?>

                <tr>
                    <td colspan="7" class="sin-datos">
                        No hay viáticos registrados.
                    </td>
                </tr>

            <?php else: ?>

                <?php foreach ($viaticos as $viatico): ?>

                    <tr>

                        <td class="celda-doble">
                            <?= trabajoDosLineas(
                                $viatico['codigo_trabajo'],
                                $viatico['proyecto_nombre']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($viatico['fecha']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($viatico['concepto']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($viatico['descripcion']) ?>
                        </td>

                        <td>
                            S/ <?= number_format((float)$viatico['monto'], 2) ?>
                        </td>

                        <td>

                            <span class="badge <?= claseEstadoViatico($viatico['estado']) ?>">

                                <?= htmlspecialchars($viatico['estado']) ?>

                            </span>

                        </td>

                        <td class="acciones">

                            <a href="/viaticos/editar/<?= (int)$viatico['id_viatico'] ?>"
                               title="Editar">
                                ✏️
                            </a>

                            <a href="#"
                               title="Eliminar"
                               class="btn-eliminar-viatico"
                               data-id="<?= (int)$viatico['id_viatico'] ?>">
                                🗑️
                            </a>

                        </td>

                    </tr>

                    <form
                        id="formEliminarViatico<?= (int)$viatico['id_viatico'] ?>"
                        action="/viaticos/eliminar/<?= (int)$viatico['id_viatico'] ?>"
                        method="POST"
                        style="display:none;">
                    </form>

                <?php endforeach; ?>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</main>

<script>

(function () {

    document
        .querySelectorAll('.btn-eliminar-viatico')
        .forEach(function (boton) {

            boton.addEventListener('click', function (e) {

                e.preventDefault();

                const confirmado = confirm(
                    '¿Seguro que deseas eliminar este viático?'
                );

                if (!confirmado) {
                    return;
                }

                document
                    .getElementById(
                        'formEliminarViatico' + boton.dataset.id
                    )
                    .submit();

            });

        });

})();

</script>

</body>
</html>