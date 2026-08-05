<?php
/**
 * Front Controller / Enrutador
 * -----------------------------------------------------
 * Único punto de entrada de la aplicación.
 * Todas las peticiones HTTP pasan por aquí (vía .htaccess
 * o el servidor embebido de PHP) y este archivo decide
 * qué método del Controller ejecutar y qué vista mostrar.
 *
 * Este archivo NO contiene lógica de negocio ni SQL.
 * Solo enruta.
 * -----------------------------------------------------
 */
require_once __DIR__ . '/app/Controllers/TrabajoController.php';
require_once __DIR__ . '/app/Controllers/ClienteController.php';
require_once __DIR__ . '/app/Controllers/EquipoController.php';
require_once __DIR__ . '/app/Controllers/PersonalController.php';
require_once __DIR__ . '/app/Controllers/TareoController.php';
require_once __DIR__ . '/app/Controllers/ViaticoController.php';
require_once __DIR__ . '/app/Models/Usuario.php';
require_once __DIR__ . '/app/Controllers/MaterialController.php';
require_once __DIR__ . '/app/Controllers/GastoGeneralController.php';
require_once __DIR__ . '/app/Controllers/CostoFinancieroController.php';
require_once __DIR__ . '/app/Controllers/ReporteController.php';

// Quitamos query string y la barra final para comparar rutas limpias.
$rutaSolicitada = strtok($_SERVER['REQUEST_URI'], '?');
$rutaSolicitada = rtrim($rutaSolicitada, '/');
$metodoHttp = $_SERVER['REQUEST_METHOD'];

$trabajoController = new TrabajoController();
$clienteController = new ClienteController();
$equipoController = new EquipoController();
$personalController = new PersonalController();
$tareoController = new TareoController();
$viaticoController = new ViaticoController();
$usuarioModel = new Usuario();
$materialController = new MaterialController();
$gastoGeneralController = new GastoGeneralController();
$costoFinancieroController = new CostoFinancieroController();
$reporteController = new ReporteController();

/**
 * Helper para incluir una vista con variables ya resueltas,
 * sin exponer $this ni contaminar el scope global.
 */
function renderizarVista(string $rutaVista, array $datos = []): void
{
    extract($datos);
    require $rutaVista;
}

// -----------------------------------------------------
// GET /trabajos  (listado con filtros)
// -----------------------------------------------------
if ($metodoHttp === 'GET' && $rutaSolicitada === '/trabajos') {
    $estadoActual        = $_GET['estado'] ?? 'Todos';
    $idResponsableActual = !empty($_GET['responsable']) ? (int) $_GET['responsable'] : null;
    $busquedaActual      = $_GET['buscar'] ?? '';

    $trabajos = $trabajoController->listar($estadoActual, $idResponsableActual, $busquedaActual);

    // TODO: reemplazar por el listado real de responsables
    // (probablemente vendrá de un Modelo Usuario que aún no existe).
    $responsables = $usuarioModel->listar();

    renderizarVista(__DIR__ . '/app/Views/trabajos/listado.php', [
        'trabajos'            => $trabajos,
        'responsables'        => $responsables,
        'estadoActual'        => $estadoActual,
        'idResponsableActual' => $idResponsableActual,
        'busquedaActual'      => $busquedaActual,
    ]);
    exit;
}

// -----------------------------------------------------
// GET /modulos  (pantalla independiente de módulos del sistema)
// -----------------------------------------------------
if ($metodoHttp === 'GET' && $rutaSolicitada === '/modulos') {
    // Cada módulo: 'ruta' => null mientras no exista su propia vista/controlador.
    // Cuando se desarrolle un módulo en fases futuras, solo hay que
    // agregarle su 'ruta' real aquí (ej: '/modulos/personal').
    $modulos = [
        ['nombre' => 'Personal',          'icono' => '👷', 'ruta' => '/personal'],
        ['nombre' => 'Equipos',           'icono' => '🚜', 'ruta' => '/equipos'],
        ['nombre' => 'Tareo',             'icono' => '🕒', 'ruta' => '/tareo'],
        ['nombre' => 'Viáticos',          'icono' => '🧳', 'ruta' => '/viaticos'],
        ['nombre' => 'Materiales',        'icono' => '📦', 'ruta' => '/materiales'],
        ['nombre' => 'Gastos Generales',  'icono' => '🧾', 'ruta' => '/gastos-generales'],
        ['nombre' => 'Costo Financiero',  'icono' => '💰', 'ruta' => '/costo-financiero'],
        ['nombre' => 'Reportes',          'icono' => '📊', 'ruta' => '/reportes'],
    ];

    renderizarVista(__DIR__ . '/app/Views/modulos/listado.php', [
        'modulos' => $modulos,
    ]);
    exit;
}

// -----------------------------------------------------
// GET /trabajos/nuevo  (mostrar formulario vacío)
// -----------------------------------------------------
if ($metodoHttp === 'GET' && $rutaSolicitada === '/trabajos/nuevo') {
    // TODO: reemplazar por el listado real de responsables (usuarios)
    $responsables = $usuarioModel->listar();
    $clientes     = $clienteController->listar();

    renderizarVista(__DIR__ . '/app/Views/trabajos/formulario.php', [
        'responsables' => $responsables,
        'clientes'     => $clientes,
        'trabajo'      => null,
        'errores'      => [],
    ]);
    exit;
}

// -----------------------------------------------------
// POST /trabajos/guardar  (registrar nuevo trabajo)
// -----------------------------------------------------
if ($metodoHttp === 'POST' && $rutaSolicitada === '/trabajos/guardar') {
    $resultado = $trabajoController->registrar($_POST);

    if (!$resultado['exito']) {
        // TODO: reemplazar por el listado real de responsables (usuarios)
        $responsables = $usuarioModel->listar();
        $clientes     = $clienteController->listar();

        renderizarVista(__DIR__ . '/app/Views/trabajos/formulario.php', [
            'responsables' => $responsables,
            'clientes'     => $clientes,
            'trabajo'      => $_POST, // para no perder lo que el usuario ya escribió
            'errores'      => [$resultado['mensaje']],
        ]);
        exit;
    }

    header('Location: /trabajos');
    exit;
}

// -----------------------------------------------------
// GET /trabajos/editar/{id}  (mostrar formulario con datos)
// -----------------------------------------------------
if ($metodoHttp === 'GET' && preg_match('#^/trabajos/editar/(\d+)$#', $rutaSolicitada, $coincidencias)) {
    $idTrabajo = (int) $coincidencias[1];
    $trabajo   = $trabajoController->verDetalle($idTrabajo);

    if ($trabajo === null) {
        http_response_code(404);
        echo 'Trabajo no encontrado.';
        exit;
    }

    // TODO: reemplazar por el listado real de responsables (usuarios)
    $responsables = $usuarioModel->listar();
    $clientes     = $clienteController->listar();

    renderizarVista(__DIR__ . '/app/Views/trabajos/formulario.php', [
        'responsables' => $responsables,
        'clientes'     => $clientes,
        'trabajo'      => $trabajo,
        'errores'      => [],
    ]);
    exit;
}

// -----------------------------------------------------
// POST /trabajos/actualizar/{id}  (guardar cambios de edición)
// -----------------------------------------------------
if ($metodoHttp === 'POST' && preg_match('#^/trabajos/actualizar/(\d+)$#', $rutaSolicitada, $coincidencias)) {
    $idTrabajo = (int) $coincidencias[1];
    $resultado = $trabajoController->actualizar($idTrabajo, $_POST);

    if (!$resultado['exito']) {
        $trabajo = $trabajoController->verDetalle($idTrabajo);

        // TODO: reemplazar por el listado real de responsables (usuarios)
        $responsables = $usuarioModel->listar();
        $clientes     = $clienteController->listar();

        renderizarVista(__DIR__ . '/app/Views/trabajos/formulario.php', [
            'responsables' => $responsables,
            'clientes'     => $clientes,
            'trabajo'      => $trabajo,
            'errores'      => [$resultado['mensaje']],
        ]);
        exit;
    }

    header('Location: /trabajos/expediente/' . $idTrabajo);
    exit;
}

// -----------------------------------------------------
// POST /trabajos/cambiar-estado/{id}  (cambio rápido de estado)
// -----------------------------------------------------
if ($metodoHttp === 'POST' && preg_match('#^/trabajos/cambiar-estado/(\d+)$#', $rutaSolicitada, $coincidencias)) {
    $idTrabajo = (int) $coincidencias[1];
    $trabajoController->cambiarEstado($idTrabajo, $_POST['estado'] ?? '');

    header('Location: /trabajos/editar/' . $idTrabajo);
    exit;
}

// -----------------------------------------------------
// POST /trabajos/eliminar/{id}
// -----------------------------------------------------
if (
    $metodoHttp === 'POST'
    && preg_match('#^/trabajos/eliminar/(\d+)$#', $rutaSolicitada, $coincidencias)
) {

    $idTrabajo = (int) $coincidencias[1];

    $resultado = $trabajoController->eliminar($idTrabajo);

    header(
        'Location: /trabajos?mensaje=' .
        urlencode($resultado['mensaje']) .
        '&tipo=' .
        ($resultado['exito'] ? 'success' : 'error')
    );

    exit;
}

// -----------------------------------------------------
// GET /trabajos/papelera  (listado de trabajos eliminados
// lógicamente, con opción de restaurarlos)
// -----------------------------------------------------
if ($metodoHttp === 'GET' && $rutaSolicitada === '/trabajos/papelera') {
    $trabajos = $trabajoController->listarEliminados();

    renderizarVista(__DIR__ . '/app/Views/trabajos/papelera.php', [
        'trabajos' => $trabajos,
    ]);
    exit;
}

// -----------------------------------------------------
// POST /trabajos/restaurar/{id}  (restaura un trabajo desde
// la Papelera y vuelve a aparecer en el listado principal)
// -----------------------------------------------------
if (
    $metodoHttp === 'POST'
    && preg_match('#^/trabajos/restaurar/(\d+)$#', $rutaSolicitada, $coincidencias)
) {

    $idTrabajo = (int) $coincidencias[1];

    $resultado = $trabajoController->restaurar($idTrabajo);

    header(
        'Location: /trabajos/papelera?mensaje=' .
        urlencode($resultado['mensaje']) .
        '&tipo=' .
        ($resultado['exito'] ? 'success' : 'error')
    );

    exit;
}

// -----------------------------------------------------
// GET /trabajos/exportar-excel  (CSV compatible con Excel,
// respeta los mismos filtros que el listado: estado,
// responsable y búsqueda)
// -----------------------------------------------------
if ($metodoHttp === 'GET' && $rutaSolicitada === '/trabajos/exportar-excel') {
    $estadoActual        = $_GET['estado'] ?? 'Todos';
    $idResponsableActual = !empty($_GET['responsable']) ? (int) $_GET['responsable'] : null;
    $busquedaActual      = $_GET['buscar'] ?? '';

    $trabajos = $trabajoController->listar($estadoActual, $idResponsableActual, $busquedaActual);

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="trabajos_valtop.csv"');

    $salida = fopen('php://output', 'w');
    // BOM UTF-8: para que Excel reconozca acentos correctamente
    fwrite($salida, "\xEF\xBB\xBF");

    fputcsv($salida, [
        'N° Trabajo', 'Cliente', 'Proyecto', 'Responsable',
        'Precio Neto', 'Estado', 'Fecha Inicio', 'Fecha Fin',
    ]);

    foreach ($trabajos as $trabajo) {
        fputcsv($salida, [
            $trabajo['codigo_trabajo'],
            $trabajo['nombre_cliente'] ?? '',
            $trabajo['proyecto'],
            $trabajo['nombre_responsable'] ?? '',
            number_format((float) $trabajo['precio_neto'], 2, '.', ''),
            $trabajo['estado'],
            $trabajo['fecha_inicio'],
            $trabajo['fecha_fin'],
        ]);
    }

    fclose($salida);
    exit;
}

// -----------------------------------------------------
// GET /trabajos/imprimir
// -----------------------------------------------------
if ($metodoHttp === 'GET' && $rutaSolicitada === '/trabajos/imprimir') {

    $idTrabajo = !empty($_GET['id']) ? (int) $_GET['id'] : null;

    $estadoActual = $_GET['estado'] ?? 'Todos';

    $idResponsableActual = !empty($_GET['responsable'])
        ? (int) $_GET['responsable']
        : null;

    $busquedaActual = $_GET['buscar'] ?? '';

    if ($idTrabajo !== null) {

        $trabajo = $trabajoController->verDetalle($idTrabajo);

        $trabajos = $trabajo ? [$trabajo] : [];

    } else {

        $trabajos = $trabajoController->listar(
            $estadoActual,
            $idResponsableActual,
            $busquedaActual
        );

    }

    $responsables = $usuarioModel->listar();

    // Cambia esto si ya manejas sesiones
    $nombreUsuario = 'Administrador';

    renderizarVista(__DIR__ . '/app/Views/trabajos/imprimir.php', [
        'trabajos'             => $trabajos,
        'estadoActual'         => $estadoActual,
        'idResponsableActual'  => $idResponsableActual,
        'busquedaActual'       => $busquedaActual,
        'responsables'         => $usuarioModel->listar(),
        'nombreUsuario'        => 'Administrador',
    ]);

    exit;
}

// -----------------------------------------------------
// GET /trabajos/expediente/{id}  (ver ficha completa)
// -----------------------------------------------------
if ($metodoHttp === 'GET' && preg_match('#^/trabajos/expediente/(\d+)$#', $rutaSolicitada, $coincidencias)) {
    $idTrabajo = (int) $coincidencias[1];
    $trabajo   = $trabajoController->verDetalle($idTrabajo);

    if ($trabajo === null) {
        http_response_code(404);
        echo 'Trabajo no encontrado.';
        exit;
    }

    renderizarVista(__DIR__ . '/app/Views/trabajos/expediente.php', [
        'trabajo' => $trabajo,
    ]);
    exit;
}

// -----------------------------------------------------
// POST /clientes/guardar  (registrar cliente vía AJAX desde el modal
// "+ Nuevo Cliente". No es un módulo aparte: solo alimenta el
// catálogo simple de clientes usado en el formulario de Trabajos)
// -----------------------------------------------------
if ($metodoHttp === 'POST' && $rutaSolicitada === '/clientes/guardar') {
    $resultado = $clienteController->registrar($_POST);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success'        => $resultado['exito'],
        'message'        => $resultado['mensaje'],
        'id'             => $resultado['id_cliente'] ?? null,
        'nombre_cliente' => $resultado['nombre_cliente'] ?? null,
    ]);
    exit;
}

// -----------------------------------------------------
// GET /equipos  (listado general de registros de equipos)
// -----------------------------------------------------
if ($metodoHttp === 'GET' && $rutaSolicitada === '/equipos') {
    $estadoActual = $_GET['estado'] ?? 'Todos';
    $equipos      = $equipoController->listar($estadoActual);

    renderizarVista(__DIR__ . '/app/Views/equipos/listado.php', [
        'equipos'     => $equipos,
        'estadoActual'=> $estadoActual,
    ]);
    exit;
}

// -----------------------------------------------------
// GET /equipos/nuevo  (mostrar formulario vacío)
// -----------------------------------------------------
if ($metodoHttp === 'GET' && $rutaSolicitada === '/equipos/nuevo') {
    $trabajos = $trabajoController->listar();

    renderizarVista(__DIR__ . '/app/Views/equipos/formulario.php', [
        'trabajos' => $trabajos,
        'equipo'   => null,
        'errores'  => [],
    ]);
    exit;
}

// -----------------------------------------------------
// POST /equipos/guardar  (registrar nuevo registro de equipos)
// -----------------------------------------------------
if ($metodoHttp === 'POST' && $rutaSolicitada === '/equipos/guardar') {
    $resultado = $equipoController->registrar($_POST);

    if (!$resultado['exito']) {
        $trabajos = $trabajoController->listar();

        renderizarVista(__DIR__ . '/app/Views/equipos/formulario.php', [
            'trabajos' => $trabajos,
            'equipo'   => $_POST,
            'errores'  => [$resultado['mensaje']],
        ]);
        exit;
    }

    header('Location: /equipos');
    exit;
}

// -----------------------------------------------------
// GET /equipos/editar/{id}  (mostrar formulario con datos)
// -----------------------------------------------------
if ($metodoHttp === 'GET' && preg_match('#^/equipos/editar/(\d+)$#', $rutaSolicitada, $coincidencias)) {
    $idEquipo = (int) $coincidencias[1];
    $equipo   = $equipoController->verDetalle($idEquipo);

    if ($equipo === null) {
        http_response_code(404);
        echo 'Registro de equipos no encontrado.';
        exit;
    }

    $trabajos = $trabajoController->listar();

    renderizarVista(__DIR__ . '/app/Views/equipos/formulario.php', [
        'trabajos' => $trabajos,
        'equipo'   => $equipo,
        'errores'  => [],
    ]);
    exit;
}

// -----------------------------------------------------
// POST /equipos/actualizar/{id}  (guardar cambios de edición)
// -----------------------------------------------------
if ($metodoHttp === 'POST' && preg_match('#^/equipos/actualizar/(\d+)$#', $rutaSolicitada, $coincidencias)) {
    $idEquipo  = (int) $coincidencias[1];
    $resultado = $equipoController->actualizar($idEquipo, $_POST);

    if (!$resultado['exito']) {
        $equipo   = $equipoController->verDetalle($idEquipo);
        $trabajos = $trabajoController->listar();

        renderizarVista(__DIR__ . '/app/Views/equipos/formulario.php', [
            'trabajos' => $trabajos,
            'equipo'   => $equipo,
            'errores'  => [$resultado['mensaje']],
        ]);
        exit;
    }

    header('Location: /equipos');
    exit;
}

// -----------------------------------------------------
// GET /equipos/ver/{id}  (detalle de un registro, botón 👁️)
// -----------------------------------------------------
if ($metodoHttp === 'GET' && preg_match('#^/equipos/ver/(\d+)$#', $rutaSolicitada, $coincidencias)) {
    $idEquipo = (int) $coincidencias[1];
    $equipo   = $equipoController->verDetalle($idEquipo);

    if ($equipo === null) {
        http_response_code(404);
        echo 'Registro de equipos no encontrado.';
        exit;
    }

    renderizarVista(__DIR__ . '/app/Views/equipos/detalle.php', [
        'equipo' => $equipo,
    ]);
    exit;
}

// -----------------------------------------------------
// POST /equipos/eliminar/{id}  (eliminar registro, botón 🗑️)
// -----------------------------------------------------
if ($metodoHttp === 'POST' && preg_match('#^/equipos/eliminar/(\d+)$#', $rutaSolicitada, $coincidencias)) {
    $idEquipo = (int) $coincidencias[1];
    $equipoController->eliminar($idEquipo);

    header('Location: /equipos');
    exit;
}

// -----------------------------------------------------
// GET /personal
// -----------------------------------------------------

if ($metodoHttp === 'GET' && $rutaSolicitada === '/personal') {
    $estadoActual = $_GET['estado'] ?? 'Todos';
    $personal     = $personalController->listar($estadoActual);

    renderizarVista(__DIR__ . '/app/Views/personal/listado.php', [
        'personal'    => $personal,
        'estadoActual'=> $estadoActual,
    ]);
    exit;
}

// -----------------------------------------------------
// GET /personal/nuevo
// -----------------------------------------------------

if ($metodoHttp === 'GET' && $rutaSolicitada === '/personal/nuevo') {
    $responsables = $usuarioModel->listar();

    renderizarVista(__DIR__ . '/app/Views/personal/formulario.php', [
        'responsables' => $responsables,
        'trabajador'   => null,
        'errores'      => [],
    ]);
    exit;
}

// -----------------------------------------------------
// POST /personal/guardar
// -----------------------------------------------------

if ($metodoHttp === 'POST' && $rutaSolicitada === '/personal/guardar') {
    $resultado = $personalController->registrar($_POST);

    if (!$resultado['exito']) {
        $responsables = $usuarioModel->listar();

        renderizarVista(__DIR__ . '/app/Views/personal/formulario.php', [
            'responsables' => $responsables,
            'trabajador'   => $_POST,
            'errores'      => [$resultado['mensaje']],
        ]);
        exit;
    }

    header('Location: /personal');
    exit;
}

// -----------------------------------------------------
// GET /personal/editar/{id}
// -----------------------------------------------------

if ($metodoHttp === 'GET' && preg_match('#^/personal/editar/(\d+)$#', $rutaSolicitada, $coincidencias)) {
    $idPersonal = (int) $coincidencias[1];
    $trabajador = $personalController->verDetalle($idPersonal);

    if ($trabajador === null) {
        http_response_code(404);
        echo 'Registro de personal no encontrado.';
        exit;
    }

    $responsables = $usuarioModel->listar();

    renderizarVista(__DIR__ . '/app/Views/personal/formulario.php', [
        'responsables' => $responsables,
        'trabajador'   => $trabajador,
        'errores'      => [],
    ]);
    exit;
}

// -----------------------------------------------------
// POST /personal/actualizar/{id}
// -----------------------------------------------------

if ($metodoHttp === 'POST' && preg_match('#^/personal/actualizar/(\d+)$#', $rutaSolicitada, $coincidencias)) {
    $idPersonal = (int) $coincidencias[1];
    $resultado  = $personalController->actualizar($idPersonal, $_POST);

    if (!$resultado['exito']) {
        $trabajador   = $personalController->verDetalle($idPersonal);
        $responsables = $usuarioModel->listar();

        renderizarVista(__DIR__ . '/app/Views/personal/formulario.php', [
            'responsables' => $responsables,
            'trabajador'   => $trabajador,
            'errores'      => [$resultado['mensaje']],
        ]);
        exit;
    }

    header('Location: /personal');
    exit;
}

// -----------------------------------------------------
// POST /personal/eliminar/{id}
// -----------------------------------------------------

if ($metodoHttp === 'POST' && preg_match('#^/personal/eliminar/(\d+)$#', $rutaSolicitada, $coincidencias)) {
    $idPersonal = (int) $coincidencias[1];
    $personalController->eliminar($idPersonal);

    header('Location: /personal');
    exit;
}

// -----------------------------------------------------
// GET /tareo  (listado)
// -----------------------------------------------------
if ($metodoHttp === 'GET' && $rutaSolicitada === '/tareo') {
    $registros = $tareoController->listar();

    renderizarVista(__DIR__ . '/app/Views/tareo/listado.php', [
        'registros' => $registros,
    ]);
    exit;
}

// -----------------------------------------------------
// GET /tareo/nuevo  (mostrar formulario vacío)
// -----------------------------------------------------
if ($metodoHttp === 'GET' && $rutaSolicitada === '/tareo/nuevo') {
    $trabajos = $trabajoController->listar();
    // Solo personal Activo puede tarearse; ajusta este filtro si
    // más adelante quieres permitir también personal en otro estado.
    $personal = $personalController->listar('Activo');

    renderizarVista(__DIR__ . '/app/Views/tareo/formulario.php', [
        'trabajos' => $trabajos,
        'personal' => $personal,
        'tareo'    => null,
        'errores'  => [],
    ]);
    exit;
}

// -----------------------------------------------------
// POST /tareo/guardar  (registrar nuevo tareo)
// -----------------------------------------------------
if ($metodoHttp === 'POST' && $rutaSolicitada === '/tareo/guardar') {
    $resultado = $tareoController->registrar($_POST);

    if (!$resultado['exito']) {
        $trabajos = $trabajoController->listar();
        $personal = $personalController->listar('Activo');

        renderizarVista(__DIR__ . '/app/Views/tareo/formulario.php', [
            'trabajos' => $trabajos,
            'personal' => $personal,
            'tareo'    => $_POST,
            'errores'  => [$resultado['mensaje']],
        ]);
        exit;
    }

    header('Location: /tareo');
    exit;
}

// -----------------------------------------------------
// GET /tareo/editar/{id}  (mostrar formulario con datos)
// -----------------------------------------------------
if ($metodoHttp === 'GET' && preg_match('#^/tareo/editar/(\d+)$#', $rutaSolicitada, $coincidencias)) {
    $idTareo = (int) $coincidencias[1];
    $tareo   = $tareoController->verDetalle($idTareo);

    if ($tareo === null) {
        http_response_code(404);
        echo 'Registro de tareo no encontrado.';
        exit;
    }

    $trabajos = $trabajoController->listar();
    $personal = $personalController->listar('Activo');

    renderizarVista(__DIR__ . '/app/Views/tareo/formulario.php', [
        'trabajos' => $trabajos,
        'personal' => $personal,
        'tareo'    => $tareo,
        'errores'  => [],
    ]);
    exit;
}

// -----------------------------------------------------
// POST /tareo/actualizar/{id}  (guardar cambios de edición)
// -----------------------------------------------------
if ($metodoHttp === 'POST' && preg_match('#^/tareo/actualizar/(\d+)$#', $rutaSolicitada, $coincidencias)) {
    $idTareo   = (int) $coincidencias[1];
    $resultado = $tareoController->actualizar($idTareo, $_POST);

    if (!$resultado['exito']) {
        $tareo    = $tareoController->verDetalle($idTareo);
        $trabajos = $trabajoController->listar();
        $personal = $personalController->listar('Activo');

        renderizarVista(__DIR__ . '/app/Views/tareo/formulario.php', [
            'trabajos' => $trabajos,
            'personal' => $personal,
            'tareo'    => $tareo,
            'errores'  => [$resultado['mensaje']],
        ]);
        exit;
    }

    header('Location: /tareo');
    exit;
}

// -----------------------------------------------------
// POST /tareo/eliminar/{id}
// -----------------------------------------------------
if ($metodoHttp === 'POST' && preg_match('#^/tareo/eliminar/(\d+)$#', $rutaSolicitada, $coincidencias)) {
    $idTareo = (int) $coincidencias[1];
    $tareoController->eliminar($idTareo);

    header('Location: /tareo');
    exit;
}

// -----------------------------------------------------
// GET /viaticos  (listado general de gastos de viáticos)
// -----------------------------------------------------
if ($metodoHttp === 'GET' && $rutaSolicitada === '/viaticos') {
    $viaticos = $viaticoController->listar();

    renderizarVista(__DIR__ . '/app/Views/viaticos/listado.php', [
        'viaticos' => $viaticos,
    ]);
    exit;
}

// -----------------------------------------------------
// GET /viaticos/nuevo  (mostrar formulario vacío)
// -----------------------------------------------------
if ($metodoHttp === 'GET' && $rutaSolicitada === '/viaticos/nuevo') {
    renderizarVista(__DIR__ . '/app/Views/viaticos/formulario.php', [
        'viatico' => null,
        'errores' => [],
    ]);
    exit;
}

// -----------------------------------------------------
// POST /viaticos/guardar  (registrar nuevo gasto de viático)
// -----------------------------------------------------
if ($metodoHttp === 'POST' && $rutaSolicitada === '/viaticos/guardar') {
    $resultado = $viaticoController->registrar($_POST);

    if (!$resultado['exito']) {
        renderizarVista(__DIR__ . '/app/Views/viaticos/formulario.php', [
            'viatico' => $_POST, // para no perder lo que el usuario ya escribió
            'errores' => [$resultado['mensaje']],
        ]);
        exit;
    }

    header('Location: /viaticos');
    exit;
}

// -----------------------------------------------------
// GET /viaticos/editar/{id}  (mostrar formulario con datos)
// -----------------------------------------------------
if ($metodoHttp === 'GET' && preg_match('#^/viaticos/editar/(\d+)$#', $rutaSolicitada, $coincidencias)) {
    $idViatico = (int) $coincidencias[1];
    $viatico   = $viaticoController->verDetalleConTrabajo($idViatico);

    if ($viatico === null) {
        http_response_code(404);
        echo 'Registro de viático no encontrado.';
        exit;
    }

    renderizarVista(__DIR__ . '/app/Views/viaticos/formulario.php', [
        'viatico' => $viatico,
        'errores' => [],
    ]);
    exit;
}

// -----------------------------------------------------
// POST /viaticos/actualizar/{id}  (guardar cambios de edición)
// -----------------------------------------------------
if ($metodoHttp === 'POST' && preg_match('#^/viaticos/actualizar/(\d+)$#', $rutaSolicitada, $coincidencias)) {
    $idViatico = (int) $coincidencias[1];
    $resultado = $viaticoController->actualizar($idViatico, $_POST);

    if (!$resultado['exito']) {
        $viatico = $viaticoController->verDetalleConTrabajo($idViatico);

        renderizarVista(__DIR__ . '/app/Views/viaticos/formulario.php', [
            'viatico' => $viatico,
            'errores' => [$resultado['mensaje']],
        ]);
        exit;
    }

    header('Location: /viaticos');
    exit;
}

// -----------------------------------------------------
// POST /viaticos/eliminar/{id}  (eliminar registro, botón 🗑️)
// -----------------------------------------------------
if ($metodoHttp === 'POST' && preg_match('#^/viaticos/eliminar/(\d+)$#', $rutaSolicitada, $coincidencias)) {
    $idViatico = (int) $coincidencias[1];
    $viaticoController->eliminar($idViatico);

    header('Location: /viaticos');
    exit;
}

// -----------------------------------------------------
// GET /trabajos/buscar-autocompletado
// -----------------------------------------------------

if ($metodoHttp === 'GET' && $rutaSolicitada === '/trabajos/buscar-autocompletado') {

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode(
        $trabajoController->buscarAutocompletado($_GET['q'] ?? '')
    );

    exit;
}

// -----------------------------------------------------
// GET /materiales  (tabla principal: todos los trabajos)
// -----------------------------------------------------
if ($metodoHttp === 'GET' && $rutaSolicitada === '/materiales') {
    $resumen = $materialController->listarResumen();

    renderizarVista(__DIR__ . '/app/Views/materiales/listado.php', [
        'resumen' => $resumen,
    ]);
    exit;
}

// -----------------------------------------------------
// GET /materiales/ver/{id}  (solo lectura del trabajo)
// -----------------------------------------------------
if ($metodoHttp === 'GET' && preg_match('#^/materiales/ver/(\d+)$#', $rutaSolicitada, $coincidencias)) {
    $idTrabajo = (int) $coincidencias[1];
    $trabajo   = $trabajoController->verDetalle($idTrabajo);

    if ($trabajo === null) {
        http_response_code(404);
        echo 'Trabajo no encontrado.';
        exit;
    }

    $personal = $tareoController->listarPersonalPorTrabajo($idTrabajo);

    renderizarVista(__DIR__ . '/app/Views/materiales/ver.php', [
        'trabajo'  => $trabajo,
        'personal' => $personal,
    ]);
    exit;
}

// -----------------------------------------------------
// GET /materiales/trabajo/{id}  (materiales de un trabajo)
// -----------------------------------------------------
if ($metodoHttp === 'GET' && preg_match('#^/materiales/trabajo/(\d+)$#', $rutaSolicitada, $coincidencias)) {
    $idTrabajo = (int) $coincidencias[1];
    $trabajo   = $trabajoController->verDetalle($idTrabajo);

    if ($trabajo === null) {
        http_response_code(404);
        echo 'Trabajo no encontrado.';
        exit;
    }

    $materiales = $materialController->listarMaterialesDeTrabajo($idTrabajo);
    $catalogo   = $materialController->listarCatalogo();
    $costoTotal = $materialController->costoTotalDeTrabajo($idTrabajo);

    renderizarVista(__DIR__ . '/app/Views/materiales/detalle.php', [
        'trabajo'    => $trabajo,
        'materiales' => $materiales,
        'catalogo'   => $catalogo,
        'costoTotal' => $costoTotal,
        'errores'    => [],
    ]);
    exit;
}

// -----------------------------------------------------
// POST /materiales/trabajo/{id}/guardar  (agregar material)
// -----------------------------------------------------
if ($metodoHttp === 'POST' && preg_match('#^/materiales/trabajo/(\d+)/guardar$#', $rutaSolicitada, $coincidencias)) {
    $idTrabajo = (int) $coincidencias[1];
    $datos = $_POST;

    // El campo Material es texto libre con autocompletado: se busca
    // o se crea en el catálogo antes de guardar el detalle.
    $idMaterial = $materialController->obtenerOCrearIdMaterialPorNombre($datos['nombre_material'] ?? '');

    if ($idMaterial === false) {
        $trabajo    = $trabajoController->verDetalle($idTrabajo);
        $materiales = $materialController->listarMaterialesDeTrabajo($idTrabajo);
        $catalogo   = $materialController->listarCatalogo();
        $costoTotal = $materialController->costoTotalDeTrabajo($idTrabajo);

        renderizarVista(__DIR__ . '/app/Views/materiales/detalle.php', [
            'trabajo'    => $trabajo,
            'materiales' => $materiales,
            'catalogo'   => $catalogo,
            'costoTotal' => $costoTotal,
            'errores'    => ['El nombre del material es obligatorio.'],
        ]);
        exit;
    }

    $datos['id_material'] = $idMaterial;

    $resultado = $materialController->registrar($idTrabajo, $datos);

    if (!$resultado['exito']) {
        $trabajo    = $trabajoController->verDetalle($idTrabajo);
        $materiales = $materialController->listarMaterialesDeTrabajo($idTrabajo);
        $catalogo   = $materialController->listarCatalogo();
        $costoTotal = $materialController->costoTotalDeTrabajo($idTrabajo);

        renderizarVista(__DIR__ . '/app/Views/materiales/detalle.php', [
            'trabajo'    => $trabajo,
            'materiales' => $materiales,
            'catalogo'   => $catalogo,
            'costoTotal' => $costoTotal,
            'errores'    => [$resultado['mensaje']],
        ]);
        exit;
    }

    header('Location: /materiales/trabajo/' . $idTrabajo);
    exit;
}

// -----------------------------------------------------
// POST /materiales/trabajo/{id}/actualizar/{idDetalle}
// -----------------------------------------------------
if ($metodoHttp === 'POST' && preg_match('#^/materiales/trabajo/(\d+)/actualizar/(\d+)$#', $rutaSolicitada, $coincidencias)) {
    $idTrabajo         = (int) $coincidencias[1];
    $idTrabajoMaterial = (int) $coincidencias[2];
    $datos = $_POST;

    $idMaterial = $materialController->obtenerOCrearIdMaterialPorNombre($datos['nombre_material'] ?? '');

    if ($idMaterial === false) {
        $trabajo    = $trabajoController->verDetalle($idTrabajo);
        $materiales = $materialController->listarMaterialesDeTrabajo($idTrabajo);
        $catalogo   = $materialController->listarCatalogo();
        $costoTotal = $materialController->costoTotalDeTrabajo($idTrabajo);

        renderizarVista(__DIR__ . '/app/Views/materiales/detalle.php', [
            'trabajo'    => $trabajo,
            'materiales' => $materiales,
            'catalogo'   => $catalogo,
            'costoTotal' => $costoTotal,
            'errores'    => ['El nombre del material es obligatorio.'],
        ]);
        exit;
    }

    $datos['id_material'] = $idMaterial;

    $resultado = $materialController->actualizar($idTrabajoMaterial, $datos);

    if (!$resultado['exito']) {
        $trabajo    = $trabajoController->verDetalle($idTrabajo);
        $materiales = $materialController->listarMaterialesDeTrabajo($idTrabajo);
        $catalogo   = $materialController->listarCatalogo();
        $costoTotal = $materialController->costoTotalDeTrabajo($idTrabajo);

        renderizarVista(__DIR__ . '/app/Views/materiales/detalle.php', [
            'trabajo'    => $trabajo,
            'materiales' => $materiales,
            'catalogo'   => $catalogo,
            'costoTotal' => $costoTotal,
            'errores'    => [$resultado['mensaje']],
        ]);
        exit;
    }

    header('Location: /materiales/trabajo/' . $idTrabajo);
    exit;
}

// -----------------------------------------------------
// POST /materiales/trabajo/{id}/eliminar/{idDetalle}
// -----------------------------------------------------
if ($metodoHttp === 'POST' && preg_match('#^/materiales/trabajo/(\d+)/eliminar/(\d+)$#', $rutaSolicitada, $coincidencias)) {
    $idTrabajo         = (int) $coincidencias[1];
    $idTrabajoMaterial = (int) $coincidencias[2];

    $materialController->eliminar($idTrabajoMaterial);

    header('Location: /materiales/trabajo/' . $idTrabajo);
    exit;
}

// -----------------------------------------------------
// POST /materiales/eliminar-todos/{idTrabajo}
// -----------------------------------------------------
if ($metodoHttp === 'POST' && preg_match('#^/materiales/eliminar-todos/(\d+)$#', $rutaSolicitada, $coincidencias)) {
    $idTrabajo = (int) $coincidencias[1];

    $materialController->eliminarTodosDeTrabajo($idTrabajo);

    header('Location: /materiales');
    exit;
}

// -----------------------------------------------------
// GET /gastos-generales  (listado, con búsqueda opcional)
// -----------------------------------------------------
if ($metodoHttp === 'GET' && $rutaSolicitada === '/gastos-generales') {
    $busquedaActual = $_GET['buscar'] ?? '';
    $gastos = $gastoGeneralController->listar($busquedaActual);
    $total  = $gastoGeneralController->total();

    renderizarVista(__DIR__ . '/app/Views/gastos-generales/listado.php', [
        'gastos'         => $gastos,
        'total'          => $total,
        'busquedaActual' => $busquedaActual,
        'errores'        => [],
        'gastoFallido'   => null,
    ]);
    exit;
}

// -----------------------------------------------------
// POST /gastos-generales/guardar  (registrar nuevo gasto)
// -----------------------------------------------------
if ($metodoHttp === 'POST' && $rutaSolicitada === '/gastos-generales/guardar') {
    $resultado = $gastoGeneralController->registrar($_POST);

    if (!$resultado['exito']) {
        $gastos = $gastoGeneralController->listar();
        $total  = $gastoGeneralController->total();

        renderizarVista(__DIR__ . '/app/Views/gastos-generales/listado.php', [
            'gastos'         => $gastos,
            'total'          => $total,
            'busquedaActual' => '',
            'errores'        => [$resultado['mensaje']],
            'gastoFallido'   => $_POST,
        ]);
        exit;
    }

    header('Location: /gastos-generales');
    exit;
}

// -----------------------------------------------------
// POST /gastos-generales/actualizar/{id}
// -----------------------------------------------------
if ($metodoHttp === 'POST' && preg_match('#^/gastos-generales/actualizar/(\d+)$#', $rutaSolicitada, $coincidencias)) {
    $idGasto   = (int) $coincidencias[1];
    $resultado = $gastoGeneralController->actualizar($idGasto, $_POST);

    if (!$resultado['exito']) {
        $gastos = $gastoGeneralController->listar();
        $total  = $gastoGeneralController->total();

        $gastoFallido = $_POST;
        $gastoFallido['id_gasto'] = $idGasto;

        renderizarVista(__DIR__ . '/app/Views/gastos-generales/listado.php', [
            'gastos'         => $gastos,
            'total'          => $total,
            'busquedaActual' => '',
            'errores'        => [$resultado['mensaje']],
            'gastoFallido'   => $gastoFallido,
        ]);
        exit;
    }

    header('Location: /gastos-generales');
    exit;
}

// -----------------------------------------------------
// POST /gastos-generales/eliminar/{id}
// -----------------------------------------------------
if ($metodoHttp === 'POST' && preg_match('#^/gastos-generales/eliminar/(\d+)$#', $rutaSolicitada, $coincidencias)) {
    $idGasto = (int) $coincidencias[1];

    $gastoGeneralController->eliminar($idGasto);

    header('Location: /gastos-generales');
    exit;
}

// -----------------------------------------------------
// POST /gastos-generales/vaciar  (elimina TODOS los registros)
// -----------------------------------------------------
if ($metodoHttp === 'POST' && $rutaSolicitada === '/gastos-generales/vaciar') {
    $gastoGeneralController->vaciarLista();

    header('Location: /gastos-generales');
    exit;
}

// -----------------------------------------------------
// GET /costo-financiero  (listado: todos los trabajos con su
// costo financiero calculado dinámicamente a partir de los
// demás módulos. No registra trabajos nuevos.)
// -----------------------------------------------------
if ($metodoHttp === 'GET' && $rutaSolicitada === '/costo-financiero') {
    $costos = $costoFinancieroController->listar();

    renderizarVista(__DIR__ . '/app/Views/costo-financiero/listado.php', [
        'costos' => $costos,
    ]);
    exit;
}

// -----------------------------------------------------
// GET /costo-financiero/recalcular  (fuerza el recálculo de
// todos los trabajos y vuelve al listado)
// -----------------------------------------------------
if ($metodoHttp === 'GET' && $rutaSolicitada === '/costo-financiero/recalcular') {
    $costoFinancieroController->recalcular();

    header('Location: /costo-financiero');
    exit;
}

// -----------------------------------------------------
// GET /costo-financiero/ver/{id}  (detalle de solo lectura, botón 👁️)
// -----------------------------------------------------
if ($metodoHttp === 'GET' && preg_match('#^/costo-financiero/ver/(\d+)$#', $rutaSolicitada, $coincidencias)) {
    $idTrabajo = (int) $coincidencias[1];
    $detalle   = $costoFinancieroController->verDetalle($idTrabajo);

    if ($detalle === null) {
        http_response_code(404);
        echo 'Trabajo no encontrado.';
        exit;
    }

    renderizarVista(__DIR__ . '/app/Views/costo-financiero/detalle.php', [
        'detalle' => $detalle,
    ]);
    exit;
}

// -----------------------------------------------------
// GET /costo-financiero/editar/{id}  (mostrar formulario con
// los datos calculados y los campos editables)
// -----------------------------------------------------
if ($metodoHttp === 'GET' && preg_match('#^/costo-financiero/editar/(\d+)$#', $rutaSolicitada, $coincidencias)) {
    $idTrabajo = (int) $coincidencias[1];
    $detalle   = $costoFinancieroController->verDetalle($idTrabajo);

    if ($detalle === null) {
        http_response_code(404);
        echo 'Trabajo no encontrado.';
        exit;
    }

    renderizarVista(__DIR__ . '/app/Views/costo-financiero/formulario.php', [
        'detalle' => $detalle,
        'errores' => [],
    ]);
    exit;
}

// -----------------------------------------------------
// POST /costo-financiero/actualizar/{id}  (guardar fecha
// factura, fecha cobro y % financiero)
// -----------------------------------------------------
if ($metodoHttp === 'POST' && preg_match('#^/costo-financiero/actualizar/(\d+)$#', $rutaSolicitada, $coincidencias)) {
    $idTrabajo = (int) $coincidencias[1];
    $resultado = $costoFinancieroController->actualizar($idTrabajo, $_POST);

    if (!$resultado['exito']) {
        $detalle = $costoFinancieroController->verDetalle($idTrabajo);

        renderizarVista(__DIR__ . '/app/Views/costo-financiero/formulario.php', [
            'detalle' => $detalle,
            'errores' => [$resultado['mensaje']],
        ]);
        exit;
    }

    header('Location: /costo-financiero');
    exit;
}

// -----------------------------------------------------
// GET /reportes  (pantalla principal, con filtros)
// -----------------------------------------------------
if ($metodoHttp === 'GET' && $rutaSolicitada === '/reportes') {
    $filtrosActuales = [
        'fecha_desde'    => $_GET['fecha_desde'] ?? '',
        'fecha_hasta'    => $_GET['fecha_hasta'] ?? '',
        'id_responsable' => $_GET['id_responsable'] ?? '',
        'estado'         => $_GET['estado'] ?? '',
    ];

    $filas       = $reporteController->listar($filtrosActuales);
    $resumen     = $reporteController->resumen($filas);
    $responsables = $usuarioModel->listar();

    renderizarVista(__DIR__ . '/app/Views/reportes/listado.php', [
        'filas'            => $filas,
        'resumen'          => $resumen,
        'responsables'     => $responsables,
        'filtrosActuales'  => $filtrosActuales,
    ]);
    exit;
}

// -----------------------------------------------------
// GET /reportes/ver/{id}  (detalle de solo lectura)
// -----------------------------------------------------
if ($metodoHttp === 'GET' && preg_match('#^/reportes/ver/(\d+)$#', $rutaSolicitada, $coincidencias)) {
    $idTrabajo = (int) $coincidencias[1];
    $fila = $reporteController->verDetalle($idTrabajo);

    if ($fila === null) {
        http_response_code(404);
        echo 'Trabajo no encontrado.';
        exit;
    }

    renderizarVista(__DIR__ . '/app/Views/reportes/ver.php', [
        'fila' => $fila,
    ]);
    exit;
}

// -----------------------------------------------------
// GET /reportes/exportar-excel  (CSV compatible con Excel)
// -----------------------------------------------------
if ($metodoHttp === 'GET' && $rutaSolicitada === '/reportes/exportar-excel') {
    $filtrosActuales = [
        'fecha_desde'    => $_GET['fecha_desde'] ?? '',
        'fecha_hasta'    => $_GET['fecha_hasta'] ?? '',
        'id_responsable' => $_GET['id_responsable'] ?? '',
        'estado'         => $_GET['estado'] ?? '',
    ];

    $filas = $reporteController->listar($filtrosActuales);

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="reporte_valtop.csv"');

    $salida = fopen('php://output', 'w');
    // BOM UTF-8: para que Excel reconozca acentos correctamente
    fwrite($salida, "\xEF\xBB\xBF");

    fputcsv($salida, [
        'Código', 'Cliente', 'Proyecto', 'Responsable', 'Precio Neto',
        'Capital Invertido', 'Costo Financiero', 'Utilidad', 'Estado', 'Estado de Cobro',
    ]);

    foreach ($filas as $fila) {
        fputcsv($salida, [
            $fila['codigo_trabajo'],
            $fila['nombre_cliente'] ?? '',
            $fila['proyecto'],
            $fila['nombre_responsable'] ?? '',
            number_format((float) $fila['precio_neto'], 2, '.', ''),
            number_format((float) $fila['capital_invertido'], 2, '.', ''),
            number_format((float) $fila['costo_financiero'], 2, '.', ''),
            number_format((float) $fila['utilidad'], 2, '.', ''),
            $fila['estado'],
            $fila['estado_cobro'],
        ]);
    }

    fclose($salida);
    exit;
}

// -----------------------------------------------------
// GET /reportes/exportar-pdf  (vista imprimible, "Guardar como PDF"
// desde el navegador — sin depender de una librería externa)
// -----------------------------------------------------
if ($metodoHttp === 'GET' && $rutaSolicitada === '/reportes/exportar-pdf') {
    $filtrosActuales = [
        'fecha_desde'    => $_GET['fecha_desde'] ?? '',
        'fecha_hasta'    => $_GET['fecha_hasta'] ?? '',
        'id_responsable' => $_GET['id_responsable'] ?? '',
        'estado'         => $_GET['estado'] ?? '',
    ];

    $filas   = $reporteController->listar($filtrosActuales);
    $resumen = $reporteController->resumen($filas);

    renderizarVista(__DIR__ . '/app/Views/reportes/listado.php', [
        'filas'           => $filas,
        'resumen'         => $resumen,
        'responsables'    => $usuarioModel->listar(),
        'filtrosActuales' => $filtrosActuales,
    ]);

    echo '<script>window.print();</script>';
    exit;
}

// -----------------------------------------------------
// Ninguna ruta coincidió
// -----------------------------------------------------
http_response_code(404);
echo 'Página no encontrada.';