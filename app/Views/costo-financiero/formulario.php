<?php
/**
 * Vista: Formulario Costo Financiero
 * -----------------------------------------------------
 * Variable esperada:
 *
 * array $detalle
 *
 * Permite editar únicamente:
 * - fecha_factura
 * - fecha_cobro
 * - porcentaje_financiero
 *
 * El resto de valores son calculados automáticamente.
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
        Editar Costo Financiero
    </title>
    <link rel="stylesheet"
          href="/assets/css/trabajos.css">

</head>
<body>
<main class="contenido">
    <div class="encabezado-pagina">
        <div>
            <h1>
                Editar Costo Financiero
            </h1>
            <p class="subtitulo">
                Configuración financiera del trabajo
            </p>
        </div>
        <a href="/costo-financiero"
        class="btn btn-secundario">
            ← Volver
        </a>
        </div>
        <form
            action="/costo-financiero/actualizar/<?= (int)$detalle['id_trabajo'] ?>"
            method="POST"
            class="formulario-trabajo">
        <div class="card-formulario">
            <h2>
                <?= htmlspecialchars($detalle['codigo_trabajo']) ?>
                </h2>
                <p>
                    <strong>Cliente:</strong>
                    <?= htmlspecialchars($detalle['nombre_cliente']) ?>
                </p>

                <p>
                    <strong>Proyecto:</strong>
                    <?= htmlspecialchars($detalle['proyecto']) ?>
                </p>

                <hr>
                <div class="grupo-campos">
                    <div class="campo">
                        <label>
                            Capital Invertido
                        </label>
                        <input type="text" readonly value="S/ <?= monto($detalle['capital_invertido']) ?>">
                    
                    </div>

                    <div class="campo">
                        <label>
                            Costo Financiero Actual
                        </label>
                        <input type="text"readonly value="S/ <?= monto($detalle['costo_financiero']) ?>">
                        </div>
                    </div>

                        <div class="grupo-campos">
                            <div class="campo">
                                <label>
                                    Fecha Factura
                                </label>
                                <input type="date"name="fecha_factura" value="<?= htmlspecialchars($detalle['fecha_factura'] ?? '') ?>">
                            </div>

                        <div class="campo">
                            <label>
                                Fecha Cobro
                            </label>
                        <input type="date"name="fecha_cobro"value="<?= htmlspecialchars($detalle['fecha_cobro'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="grupo-campos">
                        <div class="campo">
                            <label>
                                Porcentaje Financiero (%)
                            </label>
                            <input type="number" name="porcentaje_financiero" step="0.01" min="0" value="<?= htmlspecialchars($detalle['porcentaje_financiero']) ?>">
                        </div>
                        <div class="campo">
                            <label>
                                Días Transcurridos
                            </label>
                            <input
                                type="text"
                                readonly
                                value="<?= $detalle['dias'] !== null ? (int)$detalle['dias'] . ' días' : 'Sin calcular' ?>">
                        </div>
                    </div>
                    <hr>
                    <div class="grupo-campos">
                        <div class="campo">
                            <label>
                                Costo Personal
                            </label>
                            <input
                                type="text"
                                readonly
                                value="S/ <?= monto($detalle['costo_personal']) ?>">
                        </div>
                        <div class="campo">
                            <label>
                                Costo Equipos
                            </label>
                            <input type="text" readonly value="S/ <?= monto($detalle['costo_equipos']) ?>">
                        </div>
                    </div>
                    <div class="grupo-campos">
                        <div class="campo">
                            <label>
                                Costo Viáticos
                            </label>
                            <input type="text"readonly value="S/ <?= monto($detalle['costo_viaticos']) ?>">
                        </div>
                        <div class="campo">
                            <label>
                                Costo Materiales
                            </label>
                            <input
                                type="text"
                                readonly
                                value="S/ <?= monto($detalle['costo_materiales']) ?>">
                        </div>
                    </div>
                    <div class="grupo-campos">
                        <div class="campo">
                            <label>
                                Gastos Generales
                            </label>
                            <input
                                type="text"
                                readonly
                                value="S/ <?= monto($detalle['costo_gastos_generales']) ?>">
                        </div>
                    </div>
                    <div class="acciones-formulario">
                        <button
                            type="submit"
                            class="btn btn-primario">
                            💾 Guardar
                        </button>
                        <a
                            href="/costo-financiero"
                            class="btn btn-secundario">
                            Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </main>
    </body>
</html>
