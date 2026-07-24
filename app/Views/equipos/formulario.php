<?php
/**
 * Vista: Formulario de Equipo (Registrar / Editar)
 * -----------------------------------------------------
 * array   $trabajos  Lista de trabajos para el selector
 *                     (cada uno: ['id_trabajo' => .., 'codigo_trabajo' => .., 'proyecto' => ..])
 * ?array  $equipo    Datos a editar, o null si es "Nuevo Registro"
 * array   $errores   Mensajes de error de validación
 * -----------------------------------------------------
 */

$esEdicion = !empty($equipo);
$tituloPagina = $esEdicion ? 'Editar Registro de Equipos' : 'Nuevo Registro de Equipos';

$accionFormulario = $esEdicion
    ? '/equipos/actualizar/' . (int) $equipo['id_equipo']
    : '/equipos/guardar';

function valorCampoEquipo(?array $equipo, string $campo, string $porDefecto = ''): string
{
    return htmlspecialchars($equipo[$campo] ?? $porDefecto);
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
            <p class="subtitulo">Registro general de equipos utilizados en los trabajos</p>
        </div>
        <a href="/equipos" class="btn btn-secundario">← Volver al listado</a>
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
                        <?= (isset($equipo['id_trabajo']) && (int) $equipo['id_trabajo'] === (int) $trabajo['id_trabajo']) ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($trabajo['codigo_trabajo']) ?> — <?= htmlspecialchars($trabajo['proyecto']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="campo">
            <label for="cantidad_equipos">N.° Equipos</label>
            <input
                type="number"
                id="cantidad_equipos"
                name="cantidad_equipos"
                min="1"
                value="<?= valorCampoEquipo($equipo, 'cantidad_equipos', '1') ?>"
                required
            >
        </div>

        <div class="campo">
            <label for="contacto">Contacto (Nombre)</label>
            <input
                type="text"
                id="contacto"
                name="contacto"
                value="<?= valorCampoEquipo($equipo, 'contacto') ?>"
                required
            >
        </div>

        <div class="campo">
            <label for="telefono_contacto">Teléfono de Contacto</label>
            <input
                type="text"
                id="telefono_contacto"
                name="telefono_contacto"
                placeholder="Ej: 987 654 321"
                value="<?= valorCampoEquipo($equipo, 'telefono_contacto') ?>"
            >
        </div>
        
        <div class="campo">
            <label for="encargado">Encargado</label>
            <!-- TODO: cuando exista el módulo de Usuarios, reemplazar por
                 un <select> con el listado real (mismo criterio que
                 id_responsable en el formulario de Trabajos). Por ahora
                 queda como texto libre con valor por defecto. -->
            <input
                type="text"
                id="encargado"
                name="encargado"
                value="<?= valorCampoEquipo($equipo, 'encargado', 'Ingrid Castillo') ?>"
                required
            >
        </div>

        <div class="campo">
            <label for="fecha_salida">Fecha Salida</label>
            <input
                type="date"
                id="fecha_salida"
                name="fecha_salida"
                value="<?= valorCampoEquipo($equipo, 'fecha_salida') ?>"
                required
            >
        </div>

        <div class="campo">
            <label for="hora_salida">Hora Salida</label>
            <input
                type="time"
                id="hora_salida"
                name="hora_salida"
                value="<?= valorCampoEquipo($equipo, 'hora_salida') ?>"
                required
            >
        </div>

        <div class="campo">
            <label for="fecha_regreso">Fecha Regreso</label>
            <input
                type="date"
                id="fecha_regreso"
                name="fecha_regreso"
                value="<?= valorCampoEquipo($equipo, 'fecha_regreso') ?>"
            >
        </div>

        <div class="campo">
            <label for="hora_regreso">Hora Regreso</label>
            <input
                type="time"
                id="hora_regreso"
                name="hora_regreso"
                value="<?= valorCampoEquipo($equipo, 'hora_regreso') ?>"
            >
        </div>

        <div class="campo">
            <label for="tiempo">Tiempo</label>
            <input
                type="text"
                id="tiempo"
                name="tiempo"
                placeholder="Ej: 3 días, 5 horas..."
                value="<?= valorCampoEquipo($equipo, 'tiempo') ?>"
            >
        </div>

        <div class="campo">
            <label for="costo">Costo (S/)</label>
            <input
                type="number"
                id="costo"
                name="costo"
                step="0.01"
                min="0"
                value="<?= valorCampoEquipo($equipo, 'costo', '0.00') ?>"
            >
        </div>

        <div class="campo">
            <label for="pago_1">Pago 1 (S/)</label>
            <input
                type="number"
                id="pago_1"
                name="pago_1"
                step="0.01"
                min="0"
                value="<?= valorCampoEquipo($equipo, 'pago_1', '0.00') ?>"
            >
        </div>

        <div class="campo">
            <label for="pago_2">Pago 2 (S/)</label>
            <input
                type="number"
                id="pago_2"
                name="pago_2"
                step="0.01"
                min="0"
                value="<?= valorCampoEquipo($equipo, 'pago_2', '0.00') ?>"
            >
        </div>

        <div class="campo">
            <label for="estado">Estado</label>
            <select id="estado" name="estado">
                <?php foreach (EquipoController::ESTADOS_VALIDOS as $estadoOpcion): ?>
                    <option
                        value="<?= htmlspecialchars($estadoOpcion) ?>"
                        <?= (isset($equipo['estado']) && $equipo['estado'] === $estadoOpcion) ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($estadoOpcion) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="acciones-formulario">
            <button type="submit" class="btn btn-primario">
                <?= $esEdicion ? 'Actualizar Registro' : 'Guardar Registro' ?>
            </button>
            <a href="/equipos" class="btn btn-secundario">Cancelar</a>
        </div>

    </form>

</main>

</body>
</html>