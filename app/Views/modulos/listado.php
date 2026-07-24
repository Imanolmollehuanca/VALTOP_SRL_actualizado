<?php
/**
 * Vista: Módulos del Sistema
 * -----------------------------------------------------
 * Pantalla independiente (/modulos) que agrupa los módulos
 * de Fase 2 que antes vivían dentro del Expediente de Trabajo.
 *
 * Esta vista NO consulta la base de datos, NO valida datos.
 * Solo pinta la lista de módulos que recibe.
 *
 * array $modulos  Lista de módulos a mostrar.
 *                  Cada uno: ['nombre' => .., 'icono' => .., 'ruta' => .. o null]
 *                  Si 'ruta' es null, la tarjeta se muestra deshabilitada
 *                  con la etiqueta "Próximamente". Si tiene 'ruta',
 *                  la tarjeta será clickeable (para cuando el módulo exista).
 * -----------------------------------------------------
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Módulos - Valtop SRL</title>
    <link rel="stylesheet" href="/assets/css/trabajos.css">
</head>
<body>

<main class="contenido">

    <div class="encabezado-pagina">
        <div>
            <h1>Módulos</h1>
            <p class="subtitulo">Módulos generales del sistema</p>
        </div>
        <a href="/trabajos" class="btn btn-secundario">← Volver a Trabajos</a>
    </div>

    <section class="panel-modulos-futuros">
        <h2>Módulos disponibles</h2>
        <p class="subtitulo-modulos">
            Estos módulos se irán habilitando en las próximas fases del sistema.
        </p>

        <div class="grid-modulos">
            <?php foreach ($modulos as $modulo): ?>
                <?php if (!empty($modulo['ruta'])): ?>
                    <a href="<?= htmlspecialchars($modulo['ruta']) ?>" class="tarjeta-modulo">
                        <span class="modulo-icono"><?= $modulo['icono'] ?></span>
                        <span class="modulo-nombre"><?= htmlspecialchars($modulo['nombre']) ?></span>
                        <span class="modulo-estado">Disponible</span>
                    </a>
                <?php else: ?>
                    <div class="tarjeta-modulo tarjeta-modulo-deshabilitada">
                        <span class="modulo-icono"><?= $modulo['icono'] ?></span>
                        <span class="modulo-nombre"><?= htmlspecialchars($modulo['nombre']) ?></span>
                        <span class="modulo-estado">Próximamente</span>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </section>

</main>

</body>
</html>