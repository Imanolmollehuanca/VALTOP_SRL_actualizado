<?php
/**
 * Vista: Detalle de un registro de Equipos (botón 👁️)
 * -----------------------------------------------------
 * array $equipo  Datos completos del registro, incluyendo
 *                'codigo_trabajo' y 'proyecto' (por el JOIN
 *                hecho en Equipo::listar()/buscarPorId()).
 * -----------------------------------------------------
 */

function claseEstadoEquipoDetalle(string $estado): string
{
    $mapa = [
        'Pendiente'        => 'badge-pendiente',
        'Devuelto'         => 'badge-terminado',
        'Cambio de equipo' => 'badge-cambio',
    ];

    return $mapa[$estado] ?? 'badge-default';
}

function formatearMontoDetalle($monto): string
{
    return number_format((float) $monto, 2);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Equipos - Valtop SRL</title>
    <link rel="stylesheet" href="/assets/css/trabajos.css">
</head>
<body>

<main class="contenido">

    <div class="encabezado-pagina">
        <div>
            <h1>Detalle de Equipos</h1>
            <p class="subtitulo">
                Trabajo: <?= htmlspecialchars($equipo['codigo_trabajo'] ?? '—') ?>
                — <?= htmlspecialchars($equipo['proyecto'] ?? '') ?>
            </p>
        </div>
        <a href="/equipos" class="btn btn-secundario">← Volver al listado</a>
    </div>

    <section class="panel-datos-generales">
        <h2>Datos del Registro</h2>

        <div class="grid-datos">
            <div class="dato">
                <span class="dato-etiqueta">N.° Trabajo</span>
                <span class="dato-valor"><?= htmlspecialchars($equipo['codigo_trabajo'] ?? '—') ?></span>
            </div>

            <div class="dato">
                <span class="dato-etiqueta">N.° Equipos</span>
                <span class="dato-valor"><?= (int) $equipo['cantidad_equipos'] ?></span>
            </div>

            <div class="dato">
                <span class="dato-etiqueta">Contacto</span>
                <span class="dato-valor">
                    <?= htmlspecialchars($equipo['contacto']) ?>
                    <?php if(!empty($equipo['telefono_contacto'])): ?>
                        <br><small><?= htmlspecialchars($equipo['telefono_contacto']) ?></small>
                    <?php endif; ?>
                </span>
            </div>

            <div class="dato">
                <span class="dato-etiqueta">Encargado</span>
                <span class="dato-valor"><?= htmlspecialchars($equipo['encargado']) ?></span>
            </div>

            <div class="dato">
                <span class="dato-etiqueta">Fecha Salida</span>
                <span class="dato-valor"><?= htmlspecialchars($equipo['fecha_salida']) ?></span>
            </div>

            <div class="dato">
                <span class="dato-etiqueta">Hora Salida</span>
                <span class="dato-valor"><?= htmlspecialchars($equipo['hora_salida']) ?></span>
            </div>

            <div class="dato">
                <span class="dato-etiqueta">Fecha Regreso</span>
                <span class="dato-valor"><?= htmlspecialchars($equipo['fecha_regreso'] ?: '—') ?></span>
            </div>

            <div class="dato">
                <span class="dato-etiqueta">Hora Regreso</span>
                <span class="dato-valor"><?= htmlspecialchars($equipo['hora_regreso'] ?: '—') ?></span>
            </div>

            <div class="dato">
                <span class="dato-etiqueta">Tiempo</span>
                <span class="dato-valor"><?= htmlspecialchars($equipo['tiempo'] ?: '—') ?></span>
            </div>

            <div class="dato">
                <span class="dato-etiqueta">Costo (S/)</span>
                <span class="dato-valor"><?= formatearMontoDetalle($equipo['costo']) ?></span>
            </div>

            <div class="dato">
                <span class="dato-etiqueta">Pago 1 (S/)</span>
                <span class="dato-valor"><?= formatearMontoDetalle($equipo['pago_1']) ?></span>
            </div>

            <div class="dato">
                <span class="dato-etiqueta">Pago 2 (S/)</span>
                <span class="dato-valor"><?= formatearMontoDetalle($equipo['pago_2']) ?></span>
            </div>

            <div class="dato">
                <span class="dato-etiqueta">Estado</span>
                <span class="badge <?= claseEstadoEquipoDetalle($equipo['estado']) ?>">
                    <?= htmlspecialchars($equipo['estado']) ?>
                </span>
            </div>
        </div>

        <div class="acciones-expediente">
            <a href="/equipos/editar/<?= (int) $equipo['id_equipo'] ?>" class="btn btn-primario">✏️ Editar Registro</a>
            <a href="/trabajos/expediente/<?= (int) $equipo['id_trabajo'] ?>" class="btn btn-secundario">📁 Ver Expediente del Trabajo</a>
        </div>
    </section>

</main>

</body>
</html>