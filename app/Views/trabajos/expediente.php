<?php
/**
 * Vista: Expediente del Trabajo
 * -----------------------------------------------------
 * Variables que espera recibir (ya resueltas por el Controlador):
 *
 * array $trabajo   Datos completos del trabajo a mostrar
 *                   (incluye nombre_responsable, nombre_cliente, etc.)
 *
 * Esta vista NO consulta la base de datos, NO valida datos.
 * Solo pinta lo que le llega.
 *
 * Los módulos de Fase 2 (Personal, Equipos, Viáticos, Materiales,
 * Gastos Generales, Costo Financiero, Reportes) se muestran aquí
 * SOLO como bloques visuales vacíos. Todavía no tienen lógica,
 * ni consultas, ni datos: eso se desarrollará en fases posteriores.
 * -----------------------------------------------------
 */

/** Mapa de estado -> clase CSS del badge (igual que en listado.php). */
function claseEstadoExpediente(string $estado): string
{
    $mapa = [
        'Pendiente' => 'badge-pendiente',
        'Terminado' => 'badge-terminado',
        'Cobrado'   => 'badge-cobrado',
        'Fracaso'   => 'badge-fracaso',
    ];

    return $mapa[$estado] ?? 'badge-default';
}

function formatearPrecioExpediente(float $monto): string
{
    return number_format($monto, 2);
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Expediente #<?= htmlspecialchars($trabajo['codigo_trabajo']) ?> - Valtop SRL</title>
    <link rel="stylesheet" href="/assets/css/trabajos.css">
</head>
<body>

<main class="contenido">

    <div class="encabezado-pagina">
        <div>
            <h1>Expediente: <?= htmlspecialchars($trabajo['codigo_trabajo']) ?></h1>
            <p class="subtitulo"><?= htmlspecialchars($trabajo['proyecto']) ?></p>
        </div>
        <a href="/trabajos" class="btn btn-secundario">← Volver al listado</a>
    </div>

    <section class="panel-datos-generales">
        <h2>Datos Generales</h2>

        <div class="grid-datos">
            <div class="dato">
                <span class="dato-etiqueta">Cliente</span>
                <span class="dato-valor"><?= htmlspecialchars($trabajo['nombre_cliente']) ?></span>
            </div>

            <div class="dato">
                <span class="dato-etiqueta">Responsable</span>
                <span class="dato-valor"><?= htmlspecialchars($trabajo['nombre_responsable']) ?></span>
            </div>

            <div class="dato">
                <span class="dato-etiqueta">Precio Neto (S/)</span>
                <span class="dato-valor"><?= formatearPrecioExpediente((float) $trabajo['precio_neto']) ?></span>
            </div>

            <div class="dato">
                <span class="dato-etiqueta">Estado</span>
                <span class="badge <?= claseEstadoExpediente($trabajo['estado']) ?>">
                    <?= htmlspecialchars($trabajo['estado']) ?>
                </span>
            </div>

            <div class="dato">
                <span class="dato-etiqueta">Fecha Inicio</span>
                <span class="dato-valor"><?= htmlspecialchars($trabajo['fecha_inicio']) ?></span>
            </div>

            <div class="dato">
                <span class="dato-etiqueta">Fecha Fin</span>
                <span class="dato-valor"><?= htmlspecialchars($trabajo['fecha_fin'] ?: '—') ?></span>
            </div>
        </div>

        <div class="acciones-expediente">
            <a href="/trabajos/editar/<?= (int) $trabajo['id_trabajo'] ?>" class="btn btn-primario">✏️ Editar Trabajo</a>
            <a href="/trabajos/imprimir/<?= (int) $trabajo['id_trabajo'] ?>" class="btn btn-secundario">🖨️ Imprimir</a>
        </div>
    </section>

    <section class="panel-descripcion">
        <h2>Descripción</h2>

        <?php if (!empty(trim((string) ($trabajo['descripcion'] ?? '')))): ?>
            <p class="texto-descripcion">
                <?= nl2br(htmlspecialchars($trabajo['descripcion'])) ?>
            </p>
        <?php else: ?>
            <p class="texto-descripcion texto-descripcion-vacio">Sin descripción registrada.</p>
        <?php endif; ?>
    </section>

</main>

</body>
</html>