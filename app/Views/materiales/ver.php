<?php
/**
 * Vista: Materiales — "Ver" (solo lectura de un trabajo)
 * -----------------------------------------------------
 * array $trabajo   Datos del trabajo (ya trae 'nombre_responsable'
 *                   gracias a Trabajo::buscarPorId()).
 * array $personal  Personal asignado a este trabajo, vía
 *                   Tareo::listarPersonalPorTrabajo().
 *
 * Esta pantalla es SOLO informativa: no permite editar nada.
 * -----------------------------------------------------
 */

function formatearMontoVer($monto): string
{
    return number_format((float) $monto, 2);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ver Trabajo - Materiales - Valtop SRL</title>
    <link rel="stylesheet" href="/assets/css/trabajos.css">
</head>
<body>

<main class="contenido">

    <div class="encabezado-pagina">
        <div>
            <h1><?= htmlspecialchars($trabajo['codigo_trabajo']) ?></h1>
            <p class="subtitulo">Información general del trabajo (solo lectura)</p>
        </div>
        <a href="/materiales" class="btn btn-secundario">← Volver a Materiales</a>
    </div>

    <section class="panel-datos-generales">
        <div class="grid-datos">
            <div class="dato">
                <span class="dato-etiqueta">Código</span>
                <span class="dato-valor"><?= htmlspecialchars($trabajo['codigo_trabajo']) ?></span>
            </div>

            <div class="dato">
                <span class="dato-etiqueta">Proyecto</span>
                <span class="dato-valor"><?= htmlspecialchars($trabajo['proyecto']) ?></span>
            </div>

            <div class="dato">
                <span class="dato-etiqueta">Responsable</span>
                <span class="dato-valor"><?= htmlspecialchars($trabajo['nombre_responsable'] ?? '—') ?></span>
            </div>

            <div class="dato">
                <span class="dato-etiqueta">Ubicación</span>
                <span class="dato-valor"><?= htmlspecialchars($trabajo['ubicacion']) ?></span>
            </div>

            <div class="dato">
                <span class="dato-etiqueta">Precio Neto (S/)</span>
                <span class="dato-valor"><?= formatearMontoVer($trabajo['precio_neto']) ?></span>
            </div>

            <div class="dato">
                <span class="dato-etiqueta">Estado</span>
                <span class="dato-valor"><?= htmlspecialchars($trabajo['estado']) ?></span>
            </div>

            <div class="dato">
                <span class="dato-etiqueta">Fecha Inicio</span>
                <span class="dato-valor"><?= htmlspecialchars($trabajo['fecha_inicio']) ?></span>
            </div>

            <div class="dato">
                <span class="dato-etiqueta">Fecha Fin</span>
                <span class="dato-valor"><?= htmlspecialchars($trabajo['fecha_fin']) ?></span>
            </div>

            <div class="dato">
                <span class="dato-etiqueta">Personal</span>
                <span class="dato-valor">
                    <?php if (empty($personal)): ?>
                        —
                    <?php else: ?>
                        <?= htmlspecialchars(implode(', ', array_column($personal, 'nombre_completo'))) ?>
                    <?php endif; ?>
                </span>
            </div>
        </div>
    </section>

</main>

</body>
</html>