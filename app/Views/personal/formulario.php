<?php
/**
 * Vista: Formulario de Personal (Agregar / Editar)
 * -----------------------------------------------------
 * array   $responsables  Lista de usuarios para el selector "Responsable"
 * ?array  $trabajador    Datos a editar, o null si es "Agregar Personal"
 * array   $errores       Mensajes de error de validación
 * -----------------------------------------------------
 */

$esEdicion = !empty($trabajador);
$tituloPagina = $esEdicion ? 'Editar Personal' : 'Agregar Personal';

$accionFormulario = $esEdicion
    ? '/personal/actualizar/' . (int) $trabajador['id_personal']
    : '/personal/guardar';

function valorCampoPersonal(?array $trabajador, string $campo, string $porDefecto = ''): string
{
    return htmlspecialchars($trabajador[$campo] ?? $porDefecto);
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
            <p class="subtitulo">Registro del personal de la empresa.</p>
        </div>
        <a href="/personal" class="btn btn-secundario">← Volver al listado</a>
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

        <div class="campo campo-ancho-completo">
            <h3>Datos personales</h3>
        </div>

        <div class="campo">
            <label for="nombre_completo">Nombre completo</label>
            <input type="text" id="nombre_completo" name="nombre_completo"
                   value="<?= valorCampoPersonal($trabajador, 'nombre_completo') ?>" required>
        </div>

        <div class="campo">
            <label for="telefono_principal">Teléfono principal</label>
            <input type="text" id="telefono_principal" name="telefono_principal"
                   placeholder="Ej: 987 654 321"
                   value="<?= valorCampoPersonal($trabajador, 'telefono_principal') ?>" required>
        </div>

        <div class="campo">
            <label for="telefono_secundario">Teléfono secundario</label>
            <input type="text" id="telefono_secundario" name="telefono_secundario"
                   placeholder="Ej: 985 324 512"
                   value="<?= valorCampoPersonal($trabajador, 'telefono_secundario') ?>">
        </div>

        <div class="campo">
            <label for="telefono_contacto">Número de contacto</label>
            <input type="text" id="telefono_contacto" name="telefono_contacto"
                   placeholder="Ej: 967 856 732"
                   value="<?= valorCampoPersonal($trabajador, 'telefono_contacto') ?>">
        </div>

        <div class="campo campo-ancho-completo">
            <h3>Datos laborales</h3>
        </div>

        <div class="campo">
            <label for="cargo">Personal</label>
            <select id="cargo" name="cargo" required>
                <option value="">-- Seleccione --</option>
                <?php foreach (PersonalController::CARGOS_VALIDOS as $cargoOpcion): ?>
                    <option value="<?= htmlspecialchars($cargoOpcion) ?>"
                        <?= (isset($trabajador['cargo']) && $trabajador['cargo'] === $cargoOpcion) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cargoOpcion) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="campo">
            <label for="id_responsable">Responsable</label>
            <select id="id_responsable" name="id_responsable" required>
                <option value="">-- Seleccione --</option>
                <?php foreach ($responsables as $responsable): ?>
                    <option value="<?= (int) $responsable['id_usuario'] ?>"
                        <?= (isset($trabajador['id_responsable']) && (int) $trabajador['id_responsable'] === (int) $responsable['id_usuario']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($responsable['nombre_usuario']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="campo">
            <label for="estado">Estado</label>
            <select id="estado" name="estado">
                <?php foreach (PersonalController::ESTADOS_VALIDOS as $estadoOpcion): ?>
                    <option value="<?= htmlspecialchars($estadoOpcion) ?>"
                        <?= (isset($trabajador['estado']) && $trabajador['estado'] === $estadoOpcion) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($estadoOpcion) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="campo campo-ancho-completo">
            <label for="observaciones">Observaciones (opcional)</label>
            <textarea id="observaciones" name="observaciones"><?= valorCampoPersonal($trabajador, 'observaciones') ?></textarea>
        </div>

        <div class="acciones-formulario">
            <button type="submit" class="btn btn-primario">Guardar Personal</button>
            <a href="/personal" class="btn btn-secundario">Cancelar</a>
        </div>

    </form>

</main>

</body>
</html>