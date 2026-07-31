<?php
/**
 * Vista: Detalle del Costo Financiero
 * -----------------------------------------------------
 * Variable esperada:
 *
 * array $detalle
 *
 * Contiene:
 *  - codigo_trabajo
 *  - nombre_cliente
 *  - proyecto
 *  - costo_personal
 *  - costo_equipos
 *  - costo_viaticos
 *  - costo_materiales
 *  - costo_gastos_generales
 *  - capital_invertido
 *  - fecha_factura
 *  - fecha_cobro
 *  - dias
 *  - porcentaje_financiero
 *  - costo_financiero
 * -----------------------------------------------------
 */

function monto($valor): string
{
    return number_format((float)$valor, 2);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>
        Detalle Costo Financiero - Valtop SRL
    </title>
    <link rel="stylesheet"
          href="/assets/css/trabajos.css">
</head>
<body>
<main class="contenido">
    <div class="encabezado-pagina">
        <div>
            <h1>
                Detalle del Costo Financiero
            </h1>
            <p class="subtitulo">
                Información financiera del trabajo
            </p>
        </div>
        <a href="/costo-financiero"
           class="btn btn-secundario">
            ← Volver
        </a>
    </div>
    <div class="card-detalle-financiero">
        <div class="detalle-cabecera">
            <h2>
                <?= htmlspecialchars($detalle['codigo_trabajo']) ?>
            </h2>
            <span>
                <?= htmlspecialchars($detalle['nombre_cliente']) ?>
            </span>
            <br>
            <strong>
                <?= htmlspecialchars($detalle['proyecto']) ?>
            </strong>
        </div>
        <table class="tabla-detalle-financiero">
            <tbody>
                <tr>
                    <td>Costo Personal</td>
                    <td class="columna-precio">
                        S/ <?= monto($detalle['costo_personal']) ?>
                    </td>
                </tr>
                <tr>
                    <td>Costo Equipos</td>
                    <td class="columna-precio">
                        S/ <?= monto($detalle['costo_equipos']) ?>
                    </td>
                </tr>
                <tr>
                    <td>Costo Viáticos</td>
                    <td class="columna-precio">
                        S/ <?= monto($detalle['costo_viaticos']) ?>
                    </td>
                </tr>
                <tr>
                    <td>Costo Materiales</td>
                    <td class="columna-precio">
                        S/ <?= monto($detalle['costo_materiales']) ?>
                    </td>
                </tr>
                                <tr>
                    <td>Costo Gastos Generales</td>
                    <td class="columna-precio">
                        S/ <?= monto($detalle['costo_gastos_generales']) ?>
                    </td>
                </tr>
                <tr class="fila-total">
                    <td>
                        <strong>Capital Invertido</strong>
                    </td>
                    <td class="columna-precio">
                        <strong>
                            S/ <?= monto($detalle['capital_invertido']) ?>
                        </strong>
                    </td>
                </tr>
                <tr>
                    <td>Fecha Factura</td>
                    <td>
                        <?= !empty($detalle['fecha_factura'])
                            ? htmlspecialchars($detalle['fecha_factura'])
                            : '—' ?>
                    </td>
                </tr>
                <tr>
                    <td>Fecha Cobro</td>
                    <td>
                        <?= !empty($detalle['fecha_cobro'])
                            ? htmlspecialchars($detalle['fecha_cobro'])
                            : '—' ?>
                    </td>
                </tr>
                <tr>
                    <td>Días</td>
                    <td>
                        <?= $detalle['dias'] !== null
                            ? (int)$detalle['dias']
                            : '—' ?>
                    </td>
                </tr>
                <tr>
                    <td>Porcentaje Financiero</td>
                    <td>
                        <?= number_format(
                            (float)$detalle['porcentaje_financiero'],
                            2
                        ) ?> %
                    </td>
                </tr>
                <tr class="fila-total-financiero">
                    <td>
                        <strong>Costo Financiero</strong>
                    </td>
                    <td class="columna-precio">
                        <strong>
                            S/ <?= monto($detalle['costo_financiero']) ?>
                        </strong>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="acciones-formulario">
            <a href="/costo-financiero/editar/<?= (int)$detalle['id_trabajo'] ?>"
               class="btn btn-primario">
                ✏ Editar
            </a>
            <a href="/costo-financiero"
               class="btn btn-secundario">
                ← Volver
            </a>
        </div>
    </div>
</main>
</body>
</html>