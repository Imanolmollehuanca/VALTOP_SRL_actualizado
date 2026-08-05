<?php
/**
 * Vista: Papelera de Trabajos
 * -----------------------------------------------------
 * Variables que espera recibir (ya resueltas por el Controlador):
 *
 * array $trabajos   Lista de trabajos eliminados lógicamente
 *                    (eliminado_en IS NOT NULL), del más
 *                    reciente al más antiguo.
 *
 * Esta vista NO consulta la base de datos, NO valida datos.
 * Solo pinta lo que le llega.
 * -----------------------------------------------------
 */

/** Mapa de estado -> clase CSS del badge. (Mismo mapa que listado.php) */
function claseEstado(string $estado): string
{
    $mapa = [
        'Pendiente' => 'badge-pendiente',
        'Terminado' => 'badge-terminado',
        'Cobrado'   => 'badge-cobrado',
        'Fracaso'   => 'badge-fracaso',
    ];

    return $mapa[$estado] ?? 'badge-default';
}

/** Formatea un número como precio en soles: 2500.00 -> 2,500.00 */
function formatearPrecio(float $monto): string
{
    return number_format($monto, 2);
}

/** Formatea la fecha/hora de eliminación: 2026-08-04 10:30:00 -> 04/08/2026 10:30 */
function formatearFechaEliminado(string $fechaHora): string
{
    $timestamp = strtotime($fechaHora);
    return $timestamp ? date('d/m/Y H:i', $timestamp) : $fechaHora;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Papelera - Trabajos - Valtop SRL</title>
    <link rel="stylesheet" href="/assets/css/trabajos.css">
</head>
<body>

<main class="contenido">

    <div class="encabezado-pagina">
        <div>
            <h1>Papelera</h1>
            <p class="subtitulo">Historial de trabajos eliminados</p>
        </div>
    </div>

    <div class="barra-acciones">
        <div class="barra-acciones-botones">
            <a href="/trabajos" class="btn btn-secundario">
                ← Volver a Trabajos
            </a>
        </div>
    </div>

    <table class="tabla-trabajos">
        <thead>
            <tr>
                <th>N° Trabajo</th>
                <th>Cliente</th>
                <th>Proyecto</th>
                <th>Responsable</th>
                <th>Precio Neto (S/)</th>
                <th>Estado</th>
                <th>Eliminado el</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($trabajos)): ?>
                <tr>
                    <td colspan="8" class="sin-datos">
                        La Papelera está vacía.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($trabajos as $trabajo): ?>
                    <tr>
                        <td><?= htmlspecialchars($trabajo['codigo_trabajo']) ?></td>
                        <td><?= htmlspecialchars($trabajo['nombre_cliente'] ?? '') ?></td>
                        <td><?= htmlspecialchars($trabajo['proyecto']) ?></td>
                        <td><?= htmlspecialchars($trabajo['nombre_responsable'] ?? '') ?></td>
                        <td class="columna-precio">
                            <?= formatearPrecio((float) $trabajo['precio_neto']) ?>
                        </td>
                        <td>
                            <span class="badge <?= claseEstado($trabajo['estado']) ?>">
                                <?= htmlspecialchars($trabajo['estado']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars(formatearFechaEliminado($trabajo['eliminado_en'])) ?></td>
                        <td class="acciones">
                            <form action="/trabajos/restaurar/<?= (int) $trabajo['id_trabajo'] ?>"
                                method="POST"
                                style="display:inline"
                                onsubmit="return confirm('¿Desea restaurar el trabajo <?= htmlspecialchars($trabajo['codigo_trabajo']) ?>?');">

                                <button type="submit" class="btn btn-exito" title="Restaurar">
                                    ♻️ Restaurar
                                </button>

                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

</main>

</body>
</html>