<?php
/**
 * Vista: Módulo Costo Financiero
 * -----------------------------------------------------
 * array $costos
 * Lista de trabajos con su costo financiero calculado.
 *
 * Cada registro contiene:
 *  - id_trabajo
 *  - codigo_trabajo
 *  - nombre_cliente
 *  - proyecto
 *  - capital_invertido
 *  - fecha_factura
 *  - fecha_cobro
 *  - dias
 *  - porcentaje_financiero
 *  - costo_financiero
 * -----------------------------------------------------
 */

function formatearMonto($monto): string
{
    return number_format((float)$monto, 2);
}

$totalCostoFinanciero = 0;

foreach ($costos as $fila) {
    $totalCostoFinanciero += (float)$fila['costo_financiero'];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <title>
        Costo Financiero - Valtop SRL
    </title>
    <link rel="stylesheet"
          href="/assets/css/trabajos.css">
</head>
<body>
<main class="contenido">
    <div class="encabezado-pagina">
        <div>
            <h1>
                Costo Financiero
            </h1>
            <p class="subtitulo">
                Cálculo financiero automático por trabajo
            </p>
        </div>
        <a href="/modulos"
           class="btn btn-secundario">
            ← Volver a Módulos
        </a>
    </div>
    <div class="barra-acciones">
        <div class="barra-acciones-botones">
            <a href="/costo-financiero/recalcular"
               class="btn btn-primario">
                🔄 Recalcular Costo Financiero
            </a>
        </div>

        <form
            action="/costo-financiero"
            method="GET"
            class="barra-busqueda">
            <input
                type="text"
                name="buscar"
                placeholder="Buscar trabajo...">
            <button
                type="submit"
                class="btn btn-icono">
                🔍
            </button>
        </form>
    </div>
    <div class="tabla-wrapper">
        <table class="tabla-trabajos">

            <thead>

            <tr>
                <th>Código</th>
                <th>Cliente</th>
                <th>Proyecto</th>
                <th>Capital Invertido (S/)</th>
                <th>Fecha Factura</th>
                <th>Fecha Cobro</th>
                <th>Días</th>
                <th>% Financiero</th>
                <th>Costo Financiero (S/)</th>
                <th>Acciones</th>

            </tr>

            </thead>

            <tbody>

            <?php if (empty($costos)): ?>

                <tr>

                    <td colspan="10"
                        class="sin-datos">

                        No existen trabajos para calcular el costo financiero.

                    </td>

                </tr>

            <?php else: ?>
                <?php foreach ($costos as $fila): ?>
                                    <tr>

                    <td>
                        <?= htmlspecialchars($fila['codigo_trabajo']) ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($fila['nombre_cliente']) ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($fila['proyecto']) ?>
                    </td>
                    <td class="columna-precio">

                        S/
                        <?= formatearMonto($fila['capital_invertido']) ?>
                    </td>
                    <td>
                        <?= !empty($fila['fecha_factura'])
                            ? htmlspecialchars($fila['fecha_factura'])
                            : '—' ?>
                    </td>
                    <td>
                        <?= !empty($fila['fecha_cobro'])
                            ? htmlspecialchars($fila['fecha_cobro'])
                            : '—' ?>
                    </td>
                    <td class="texto-centro">
                        <?= $fila['dias'] !== null
                            ? (int)$fila['dias']
                            : '—' ?>
                    </td>
                    <td class="texto-centro">
                        <?= number_format(
                            (float)$fila['porcentaje_financiero'],
                            2
                        ) ?> %
                    </td>
                    <td class="columna-precio">
                        <strong>
                            S/
                            <?= formatearMonto(
                                $fila['costo_financiero']
                            ) ?>
                        </strong>
                    </td>
                    <td class="acciones">
                        <a
                            href="/costo-financiero/ver/<?= (int)$fila['id_trabajo'] ?>"
                            title="Ver detalle">
                            👁️
                        </a>
                        <a
                            href="/costo-financiero/editar/<?= (int)$fila['id_trabajo'] ?>"
                            title="Editar">
                            ✏️
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
        <div class="card-total-financiero">
        <div class="titulo-total">

            TOTAL COSTO FINANCIERO
        </div>
        <div class="monto-total">
            S/ <?= formatearMonto($totalCostoFinanciero) ?>
        </div>

    </div>
</main>
<script>
(function () {

    const botonRecalcular = document.querySelector(
        'a[href="/costo-financiero/recalcular"]'
    );
    if (!botonRecalcular) {
        return;
    }
    botonRecalcular.addEventListener('click', function (e) {

        const confirmado = confirm(
            '¿Deseas recalcular el costo financiero de todos los trabajos?'
        );
        if (!confirmado) {
            e.preventDefault();
        }
    });
})();
</script>
</body>
</html>