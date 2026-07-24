<?php
/**
 * Vista: Formulario de Tareo (Registrar / Editar)
 * -----------------------------------------------------
 * array   $trabajos  Lista de trabajos para el selector
 *                     (cada uno: ['id_trabajo' => .., 'codigo_trabajo' => .., 'proyecto' => ..])
 * array   $personal  Lista de trabajadores para el selector
 *                     (cada uno: ['id_personal' => .., 'nombre_completo' => ..])
 * ?array  $tareo     Datos a editar, o null si es "Nuevo Registro"
 * array   $errores   Mensajes de error de validación
 * -----------------------------------------------------
 */

$esEdicion = !empty($tareo);
$tituloPagina = $esEdicion ? 'Editar Tareo' : 'Registrar Tareo';

$accionFormulario = $esEdicion
    ? '/tareo/actualizar/' . (int) $tareo['id_tareo']
    : '/tareo/guardar';

function valorCampoTareo(?array $tareo, string $campo, string $porDefecto = ''): string
{
    return htmlspecialchars($tareo[$campo] ?? $porDefecto);
}

function etiquetaActividadFormulario(string $actividad): string
{
    $mapa = [
        'Campo'      => 'Campo',
        'Dibujo'     => 'Dibujo (D)',
        'Falto'      => 'Faltó',
        'Vacaciones' => 'Vacaciones',
    ];

    return $mapa[$actividad] ?? $actividad;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $tituloPagina ?> - Valtop SRL</title>
    <link rel="stylesheet" href="/assets/css/trabajos.css">
</head>
<body>

<main class="contenido">

    <div class="encabezado-pagina">
        <div>
            <h1><?= $tituloPagina ?></h1>
            <p class="subtitulo">Registro diario del personal por trabajo.</p>
        </div>
        <a href="/tareo" class="btn btn-secundario">← Volver al listado</a>
    </div>

    <?php if (!empty($errores)): ?>
        <div class="alerta alerta-error">
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?= $accionFormulario ?>" method="POST" class="form-trabajo">

        <div class="campo">
            <label for="id_trabajo">Trabajo</label>
            <select id="id_trabajo" name="id_trabajo" required>
                <option value="">-- Seleccione --</option>
                <?php foreach ($trabajos as $trabajo): ?>
                    <option
                        value="<?= (int) $trabajo['id_trabajo'] ?>"
                        <?= (isset($tareo['id_trabajo']) && (int) $tareo['id_trabajo'] === (int) $trabajo['id_trabajo']) ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($trabajo['codigo_trabajo']) ?> — <?= htmlspecialchars($trabajo['proyecto']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="campo">
            <label for="fecha">Fecha</label>
            <input
                type="date"
                id="fecha"
                name="fecha"
                value="<?= valorCampoTareo($tareo, 'fecha') ?>"
                required
            >
        </div>

        <div class="campo">
            <label for="id_personal">Trabajador</label>
            <select id="id_personal" name="id_personal" required>
                <option value="">-- Seleccione --</option>
                <?php foreach ($personal as $trabajador): ?>
                    <option
                        value="<?= (int) $trabajador['id_personal'] ?>"
                        <?= (isset($tareo['id_personal']) && (int) $tareo['id_personal'] === (int) $trabajador['id_personal']) ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($trabajador['nombre_completo']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="campo">
            <label for="actividad">Actividad</label>
            <select id="actividad" name="actividad" required>
                <option value="">-- Seleccione --</option>
                <?php foreach (TareoController::ACTIVIDADES_VALIDAS as $actividadOpcion): ?>
                    <option
                        value="<?= htmlspecialchars($actividadOpcion) ?>"
                        <?= (isset($tareo['actividad']) && $tareo['actividad'] === $actividadOpcion) ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars(etiquetaActividadFormulario($actividadOpcion)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="campo campo-ancho-completo">
            <label for="observaciones">Observaciones (opcional)</label>
            <textarea
                id="observaciones"
                name="observaciones"
                rows="3"
            ><?= valorCampoTareo($tareo, 'observaciones') ?></textarea>
        </div>

        <div class="acciones-formulario">
            <button type="submit" class="btn btn-primario">
                <?= $esEdicion ? 'Actualizar Tareo' : 'Guardar' ?>
            </button>
            <a href="/tareo" class="btn btn-secundario">Cancelar</a>
        </div>

    </form>

</main>

</body>
</html>