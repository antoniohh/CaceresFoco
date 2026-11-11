<?php
/**
 * ==============================================================================
 * PANEL DE ADMINISTRACIÓN - CONCURSO FOTOGRÁFICO "CÁCERES EN EL FOCO"
 * 40º Aniversario de Cáceres como Patrimonio de la Humanidad
 *
 * @author    Antonio Horrillo <ahorrillo@hoy.es>
 * @copyright (c) 2026 Corporación de Medios de Extremadura, S.A. (Grupo Vocento)
 *            HOY Diario de Extremadura · Avda. del Diario Hoy, s/n, 06008 Badajoz
 *            CIF: A-78865441 · Todos los derechos reservados.
 * @link      https://www.hoy.es
 * @link      https://servicios.hoy.es/caceresenelfoco/
 *
 * Mono-archivo autocontenido (Backend PHP + Frontend HTML/CSS/JS)
 * Compatible estrictamente con PHP 5.3+
 * ==============================================================================
 */

// 1. ZONA HORARIA OFICIAL (España Peninsular)
date_default_timezone_set('Europe/Madrid');

// Encabezados de seguridad y exclusión de indexación en buscadores
header('X-Robots-Tag: noindex, nofollow, noarchive, nosnippet');
header('X-Content-Type-Options: nosniff');

// 2. CONFIGURACIÓN DE ACCESO SEGURO
// Hash SHA-256 de la contraseña (nunca se almacena en texto plano).
// Déjalo en blanco ('') para desactivar la autenticación.
define('ADMIN_PASSWORD_HASH', '685b57cd813a152685d180fd51ae6db261ca86dc380b614fb04c18bd9020d5db');

// Función de comparación segura resistente a ataques de temporización (compatible con PHP 5.3+)
function cf_hash_equals($known_string, $user_string) {
    if (function_exists('hash_equals')) {
        return hash_equals($known_string, $user_string);
    }
    $kLen = strlen($known_string);
    $uLen = strlen($user_string);
    if ($kLen !== $uLen) {
        return false;
    }
    $result = 0;
    for ($i = 0; $i < $kLen; $i++) {
        $result |= (ord($known_string[$i]) ^ ord($user_string[$i]));
    }
    return $result === 0;
}

// 3. INICIO DE SESIÓN Y PROTECCIÓN CSRF
if (!session_id()) {
    @session_start();
}

if (!isset($_SESSION['cf_csrf_token'])) {
    if (function_exists('openssl_random_pseudo_bytes')) {
        $_SESSION['cf_csrf_token'] = bin2hex(openssl_random_pseudo_bytes(16));
    } else {
        $_SESSION['cf_csrf_token'] = md5(uniqid(mt_rand(), true));
    }
}
$csrfToken = $_SESSION['cf_csrf_token'];

// 4. CONTROL DE AUTENTICACIÓN
$authRequired = (ADMIN_PASSWORD_HASH !== '');
$isLoggedIn = false;

if (!$authRequired) {
    $isLoggedIn = true;
} else {
    if (isset($_SESSION['cf_logged_in']) && $_SESSION['cf_logged_in'] === true) {
        $isLoggedIn = true;
    }
}

// Proceso de Login
$loginError = '';
if (isset($_POST['action']) && $_POST['action'] === 'login') {
    $passwordInput = isset($_POST['password']) ? (string)$_POST['password'] : '';
    if (cf_hash_equals(ADMIN_PASSWORD_HASH, hash('sha256', $passwordInput))) {
        $_SESSION['cf_logged_in'] = true;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $loginError = 'Contraseña incorrecta. Inténtelo de nuevo.';
    }
}

// Proceso de Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['cf_logged_in']);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// 5. RUTAS DE FICHEROS
$baseDir = dirname(__FILE__);
$configFile = $baseDir . '/config.json';
$backupFile = $baseDir . '/config.json.bak';

// 6. FUNCIONES DE AYUDA (COMPATIBLES CON PHP 5.3)

/**
 * Codificación JSON legible con sangrado compatible con PHP 5.3+
 */
function cf_pretty_json($data) {
    if (defined('JSON_PRETTY_PRINT')) {
        $flags = JSON_PRETTY_PRINT;
        if (defined('JSON_UNESCAPED_SLASHES')) {
            $flags = $flags | JSON_UNESCAPED_SLASHES;
        }
        if (defined('JSON_UNESCAPED_UNICODE')) {
            $flags = $flags | JSON_UNESCAPED_UNICODE;
        }
        return json_encode($data, $flags);
    }

    // Fallback manual de formateo para PHP 5.3
    $json = json_encode($data);
    $result = '';
    $level = 0;
    $in_quotes = false;
    $in_escape = false;
    $ends_line_level = null;
    $len = strlen($json);

    for ($i = 0; $i < $len; $i++) {
        $char = $json[$i];
        $new_line_level = null;
        $post = '';

        if ($ends_line_level !== null) {
            $new_line_level = $ends_line_level;
            $ends_line_level = null;
        }

        if ($in_escape) {
            $in_escape = false;
        } else if ($char === '"') {
            $in_quotes = !$in_quotes;
        } else if (!$in_quotes) {
            switch ($char) {
                case '}': case ']':
                    $level--;
                    $ends_line_level = null;
                    $new_line_level = $level;
                    break;
                case '{': case '[':
                    $level++;
                case ',':
                    $ends_line_level = $level;
                    break;
                case ':':
                    $post = ' ';
                    break;
                case " ": case "\t": case "\n": case "\r":
                    $char = "";
                    break;
            }
        } else if ($char === '\\') {
            $in_escape = true;
        }

        if ($new_line_level !== null) {
            $result .= "\n" . str_repeat('  ', $new_line_level);
        }
        $result .= $char . $post;
    }
    return $result;
}

/**
 * Convierte fecha ISO o string a formato YYYY-MM-DDTHH:MM para <input type="datetime-local">
 */
function cf_format_for_input($dateStr) {
    if (empty($dateStr)) {
        return '';
    }
    $ts = strtotime($dateStr);
    if ($ts === false) {
        return '';
    }
    return date('Y-m-d\TH:i', $ts);
}

/**
 * Normaliza fecha del input a formato ISO 8601 con zona horaria oficial (ej: 2026-11-21T12:00:00+01:00)
 */
function cf_normalize_iso_date($dateStr) {
    $trimmed = trim($dateStr);
    if ($trimmed === '') {
        return null;
    }
    $ts = strtotime($trimmed);
    if ($ts === false) {
        return null;
    }
    return date('c', $ts);
}

// 7. CARGA DE CONFIGURACIÓN POR DEFECTO
$defaultConfig = array(
    'control_por_fechas' => false,
    'estado' => 'INSCRIPCIONES_ABIERTAS',
    'mostrar_temporizador' => true,
    'fecha_objetivo_temporizador' => '2026-11-21T12:00:00+01:00',
    'rotulo_temporizador' => 'Cuenta atrás para el inicio del rally fotográfico',
    'fases_fechas' => array(
        array(
            'estado' => 'CERRADO',
            'hasta' => '2026-11-16T00:00:00+01:00',
            'mostrar_temporizador' => true,
            'fecha_objetivo_temporizador' => '2026-11-16T00:00:00+01:00',
            'rotulo_temporizador' => 'Cuenta atrás para la apertura de inscripciones'
        ),
        array(
            'estado' => 'INSCRIPCIONES_ABIERTAS',
            'hasta' => '2026-11-21T12:00:00+01:00',
            'mostrar_temporizador' => true,
            'fecha_objetivo_temporizador' => '2026-11-21T12:00:00+01:00',
            'rotulo_temporizador' => 'Cuenta atrás para el inicio del rally fotográfico'
        ),
        array(
            'estado' => 'CONCURSO_ABIERTO',
            'hasta' => '2026-11-21T15:00:00+01:00',
            'mostrar_temporizador' => true,
            'fecha_objetivo_temporizador' => '2026-11-21T15:00:00+01:00',
            'rotulo_temporizador' => 'Tiempo restante para subir tus fotografías (hasta las 15:00h)'
        ),
        array(
            'estado' => 'GALERIA_ABIERTA',
            'hasta' => '2026-12-01T10:00:00+01:00',
            'mostrar_temporizador' => true,
            'fecha_objetivo_temporizador' => '2026-12-01T10:00:00+01:00',
            'rotulo_temporizador' => 'Cuenta atrás para el anuncio oficial de ganadores (1 de diciembre)'
        ),
        array(
            'estado' => 'GANADORES_PUBLICADOS',
            'hasta' => null,
            'mostrar_temporizador' => false,
            'fecha_objetivo_temporizador' => null,
            'rotulo_temporizador' => ''
        )
    ),
    '_comentarios' => array(
        'control_por_fechas' => 'true: activa la transición automática según calendario; false: manda el campo \'estado\'.',
        'estados_posibles' => array(
            'CERRADO',
            'INSCRIPCIONES_ABIERTAS',
            'CONCURSO_ABIERTO',
            'GALERIA_ABIERTA',
            'GANADORES_PUBLICADOS'
        ),
        'descripcion_estados' => array(
            'CERRADO' => 'Fase 0: Previo al concurso. Inscripciones cerradas o cuenta atrás para apertura.',
            'INSCRIPCIONES_ABIERTAS' => 'Fase 1: Registro previo (16-21 nov). Botón 1 activo y destacado.',
            'CONCURSO_ABIERTO' => 'Fase 2: Rally en marcha (21 nov, 12:00-15:00h). Botón 2 (Subir fotos) activo y destacado.',
            'GALERIA_ABIERTA' => 'Fase 3: Galería abierta y deliberación del jurado (21 nov 15:00h - 1 dic). Botón 3 activo para ver fotos participantes con cuenta atrás al fallo.',
            'GANADORES_PUBLICADOS' => 'Fase 4: Ganadores oficiales y selección premiada publicada (1 dic en adelante). Botón 3 activo.'
        ),
        'mostrar_temporizador' => 'true para mostrar la cuenta atrás, false para ocultarla.'
    )
);

// Cargar archivo existente si está disponible
$currentConfig = $defaultConfig;
$configRawString = '';

if (file_exists($configFile)) {
    $rawContents = @file_get_contents($configFile);
    if ($rawContents !== false) {
        $configRawString = $rawContents;
        $decoded = json_decode($rawContents, true);
        if (is_array($decoded)) {
            $currentConfig = array_merge($defaultConfig, $decoded);
            if (isset($decoded['fases_fechas']) && is_array($decoded['fases_fechas'])) {
                $currentConfig['fases_fechas'] = $decoded['fases_fechas'];
            }
            if (isset($decoded['_comentarios']) && is_array($decoded['_comentarios'])) {
                $currentConfig['_comentarios'] = $decoded['_comentarios'];
            }
        }
    }
}

if ($configRawString === '') {
    $configRawString = cf_pretty_json($currentConfig);
}

// 8. ACCIONES DE GUARDADO Y RESTAURACIÓN (SOLO SI ESTÁ AUTENTICADO)
$msgSuccess = '';
$msgError = '';

if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if ($submittedToken !== $csrfToken) {
        $msgError = 'Token de seguridad CSRF no válido. Por favor, recargue el formulario.';
    } else {
        $action = isset($_POST['action']) ? $_POST['action'] : '';

        // ACCIÓN: GUARDAR DESDE EL FORMULARIO VISUAL
        if ($action === 'save_form') {
            $newConfig = array();

            // 1. Modo de control
            $newConfig['control_por_fechas'] = (isset($_POST['control_por_fechas']) && $_POST['control_por_fechas'] === '1');

            // 2. Estado manual
            $newConfig['estado'] = isset($_POST['estado']) ? strtoupper(trim($_POST['estado'])) : 'INSCRIPCIONES_ABIERTAS';

            // 3. Temporizador general (Modo Manual)
            $newConfig['mostrar_temporizador'] = (isset($_POST['mostrar_temporizador']) && $_POST['mostrar_temporizador'] === '1');
            
            $fechaObjManual = isset($_POST['fecha_objetivo_temporizador']) ? trim($_POST['fecha_objetivo_temporizador']) : '';
            $newConfig['fecha_objetivo_temporizador'] = cf_normalize_iso_date($fechaObjManual);

            $newConfig['rotulo_temporizador'] = isset($_POST['rotulo_temporizador']) ? trim($_POST['rotulo_temporizador']) : '';

            // 4. Fases de Fechas
            $fasesArr = array();
            if (isset($_POST['fases']) && is_array($_POST['fases'])) {
                foreach ($_POST['fases'] as $f) {
                    if (!is_array($f)) {
                        continue;
                    }
                    $faseEstado = isset($f['estado']) ? strtoupper(trim($f['estado'])) : 'CERRADO';
                    
                    // Hasta
                    $hastaSinLimite = (isset($f['hasta_null']) && $f['hasta_null'] === '1');
                    $hastaFecha = (isset($f['hasta']) && !$hastaSinLimite) ? cf_normalize_iso_date($f['hasta']) : null;

                    // Temporizador en fase
                    $faseMostrarTemp = (isset($f['mostrar_temporizador']) && $f['mostrar_temporizador'] === '1');

                    // Fecha objetivo temporizador
                    $faseTempObjSinLimite = (isset($f['fecha_objetivo_null']) && $f['fecha_objetivo_null'] === '1');
                    $faseFechaObj = (isset($f['fecha_objetivo_temporizador']) && !$faseTempObjSinLimite) ? cf_normalize_iso_date($f['fecha_objetivo_temporizador']) : null;

                    // Rótulo temporizador
                    $faseRotulo = isset($f['rotulo_temporizador']) ? trim($f['rotulo_temporizador']) : '';

                    $fasesArr[] = array(
                        'estado' => $faseEstado,
                        'hasta' => $hastaFecha,
                        'mostrar_temporizador' => $faseMostrarTemp,
                        'fecha_objetivo_temporizador' => $faseFechaObj,
                        'rotulo_temporizador' => $faseRotulo
                    );
                }
            }
            $newConfig['fases_fechas'] = $fasesArr;

            // 5. Preservar comentarios
            if (isset($currentConfig['_comentarios'])) {
                $newConfig['_comentarios'] = $currentConfig['_comentarios'];
            }

            // Guardar con Backup
            $jsonToSave = cf_pretty_json($newConfig);
            if ($jsonToSave === false || empty($jsonToSave)) {
                $msgError = 'Error al serializar la configuración en JSON.';
            } else {
                // Crear backup del archivo actual
                if (file_exists($configFile)) {
                    @copy($configFile, $backupFile);
                }

                $bytesWritten = @file_put_contents($configFile, $jsonToSave . "\n", LOCK_EX);
                if ($bytesWritten === false) {
                    $msgError = 'No se pudo escribir en ' . htmlspecialchars($configFile) . '. Compruebe los permisos de escritura del servidor.';
                } else {
                    $msgSuccess = '¡Configuración guardada correctamente! Se ha generado una copia de seguridad automática.';
                    $currentConfig = $newConfig;
                    $configRawString = $jsonToSave;
                }
            }
        }

        // ACCIÓN: GUARDAR DESDE EL EDITOR RAW JSON
        else if ($action === 'save_raw') {
            $rawInput = isset($_POST['raw_json']) ? trim($_POST['raw_json']) : '';
            $decoded = json_decode($rawInput, true);

            if ($decoded === null && !empty($rawInput)) {
                $msgError = 'Error de sintaxis JSON: El texto introducido no es un JSON válido.';
            } else if (!is_array($decoded)) {
                $msgError = 'El JSON debe representar un objeto raíz con clave-valor.';
            } else {
                // Crear backup
                if (file_exists($configFile)) {
                    @copy($configFile, $backupFile);
                }

                $formattedJson = cf_pretty_json($decoded);
                $bytesWritten = @file_put_contents($configFile, $formattedJson . "\n", LOCK_EX);
                if ($bytesWritten === false) {
                    $msgError = 'No se pudo escribir en ' . htmlspecialchars($configFile) . '. Compruebe permisos de escritura.';
                } else {
                    $msgSuccess = '¡Fichero JSON actualizado directamente con éxito!';
                    $currentConfig = $decoded;
                    $configRawString = $formattedJson;
                }
            }
        }

        // ACCIÓN: RESTAURAR DESDE BACKUP
        else if ($action === 'restore_backup') {
            if (!file_exists($backupFile)) {
                $msgError = 'No existe un archivo de copia de seguridad (config.json.bak).';
            } else {
                $bakContent = @file_get_contents($backupFile);
                if ($bakContent === false) {
                    $msgError = 'No se pudo leer la copia de seguridad.';
                } else {
                    @file_put_contents($configFile, $bakContent, LOCK_EX);
                    $msgSuccess = '¡Configuración restaurada con éxito desde la última copia de seguridad!';
                    $decoded = json_decode($bakContent, true);
                    if (is_array($decoded)) {
                        $currentConfig = $decoded;
                    }
                    $configRawString = $bakContent;
                }
            }
        }
    }
}

// 9. SIMULACIÓN DEL ESTADO EN TIEMPO REAL (LÓGICA IDÉNTICA A INDEX.PHP)
$ahoraTimestamp = time();
$modoActivoReal = ($currentConfig['control_por_fechas']) ? 'auto' : 'manual';
$estadoResuelto = $currentConfig['estado'];
$temporizadorResuelto = $currentConfig['mostrar_temporizador'];
$fechaObjResuelta = $currentConfig['fecha_objetivo_temporizador'];
$rotuloResuelto = $currentConfig['rotulo_temporizador'];
$faseActivaIndex = null;

if ($currentConfig['control_por_fechas'] && !empty($currentConfig['fases_fechas'])) {
    foreach ($currentConfig['fases_fechas'] as $idx => $fase) {
        if (!is_array($fase) || !isset($fase['estado'])) {
            continue;
        }

        $hastaTimestamp = (!empty($fase['hasta'])) ? strtotime($fase['hasta']) : null;

        if ($hastaTimestamp === null || $ahoraTimestamp < $hastaTimestamp) {
            $estadoResuelto = strtoupper(trim($fase['estado']));
            if (isset($fase['mostrar_temporizador'])) {
                $temporizadorResuelto = (bool)$fase['mostrar_temporizador'];
            }
            if (isset($fase['fecha_objetivo_temporizador']) && !empty($fase['fecha_objetivo_temporizador'])) {
                $fechaObjResuelta = $fase['fecha_objetivo_temporizador'];
            } elseif ($hastaTimestamp !== null) {
                $fechaObjResuelta = $fase['hasta'];
            }
            if (isset($fase['rotulo_temporizador'])) {
                $rotuloResuelto = $fase['rotulo_temporizador'];
            }
            $faseActivaIndex = $idx;
            break;
        }
    }
}

// Estados oficiales disponibles
$estadosValidos = array(
    'CERRADO' => array(
        'label' => 'CERRADO (Fase 0 - Previo / Espera)',
        'badge' => 'badge-closed',
        'desc' => 'Concurso no iniciado o en pausa. Botones de participación inactivos.'
    ),
    'INSCRIPCIONES_ABIERTAS' => array(
        'label' => 'INSCRIPCIONES_ABIERTAS (Fase 1 - Registro Previo)',
        'badge' => 'badge-open',
        'desc' => 'Botón 1 (Inscripción) activo y destacado en rojo. Botones 2 y 3 inactivos.'
    ),
    'CONCURSO_ABIERTO' => array(
        'label' => 'CONCURSO_ABIERTO (Fase 2 - Rally en Marcha)',
        'badge' => 'badge-live',
        'desc' => 'Día del rally (12:00 a 15:00h). Botón 2 (Subir Fotos) activo y destacado.'
    ),
    'GALERIA_ABIERTA' => array(
        'label' => 'GALERIA_ABIERTA (Fase 3 - Galería / Deliberación)',
        'badge' => 'badge-gallery',
        'desc' => 'Fotos participantes en exposición pública mientras el jurado delibera. Botón 3 activo.'
    ),
    'GANADORES_PUBLICADOS' => array(
        'label' => 'GANADORES_PUBLICADOS (Fase 4 - Ganadores Oficiales)',
        'badge' => 'badge-results',
        'desc' => 'Fallo oficial anunciado. Botón 3 activo para explorar las fotos premiadas.'
    ),
    'GALERIA_Y_GANADORES' => array(
        'label' => 'GALERIA_Y_GANADORES (Alias retrocompatible Fase 4)',
        'badge' => 'badge-results',
        'desc' => 'Alias compatible con versiones previas para la fase final con ganadores proclamados.'
    )
);

// Comprobación de permisos de escritura
$isWritable = is_writable($configFile) || (!file_exists($configFile) && is_writable($baseDir));
$hasBackup = file_exists($backupFile);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Gestor de Configuración | Cáceres en el Foco</title>
  <style>
    /* ==========================================================================
       ESTILOS DEL PANEL DE ADMINISTRACIÓN - SISTEMA DIARIO HOY
       ========================================================================== */
    :root {
      --brand-red: #ce0000;
      --brand-red-dark: #b80000;
      --brand-red-light: #fff1f1;
      --brand-red-border: #fca5a5;
      
      --color-slate-900: #0f172a;
      --color-slate-800: #1e293b;
      --color-slate-700: #334155;
      --color-slate-600: #475569;
      --color-slate-500: #64748b;
      --color-slate-400: #94a3b8;
      --color-slate-200: #e2e8f0;
      --color-slate-100: #f1f5f9;
      --color-slate-50: #f8fafc;
      
      --state-open: #10b981;
      --state-open-bg: #ecfdf5;
      --state-live: #ef4444;
      --state-live-bg: #fef2f2;
      --state-closed: #f59e0b;
      --state-closed-bg: #fffbeb;
      --state-results: #8b5cf6;
      --state-results-bg: #f5f3ff;
      --state-gallery: #0284c7;
      --state-gallery-bg: #f0f9ff;
      
      --font-sans: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      --font-mono: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    }

    *, *::before, *::after {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      padding: 0;
      font-family: var(--font-sans);
      background-color: #0b0f19;
      background-image: 
        radial-gradient(at 10% 20%, rgba(206, 0, 0, 0.08) 0px, transparent 50%),
        radial-gradient(at 90% 80%, rgba(30, 41, 59, 0.4) 0px, transparent 50%);
      color: var(--color-slate-800);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* BARRA SUPERIOR */
    .admin-navbar {
      background: #ffffff;
      border-bottom: 2px solid var(--brand-red);
      padding: 12px 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: 0 2px 10px rgba(0,0,0,0.06);
      position: sticky;
      top: 0;
      z-index: 100;
    }

    .admin-navbar .brand-group {
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .admin-navbar .brand-badge {
      background: var(--brand-red);
      color: #ffffff;
      font-weight: 800;
      font-size: 13px;
      letter-spacing: 0.5px;
      padding: 4px 10px;
      border-radius: 4px;
      text-transform: uppercase;
    }

    .admin-navbar h1 {
      margin: 0;
      font-size: 17px;
      font-weight: 700;
      color: var(--color-slate-900);
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .admin-navbar h1 span.subtitle {
      font-weight: 400;
      color: var(--color-slate-500);
      font-size: 13px;
    }

    .admin-navbar .nav-actions {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .btn-link-public {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: var(--color-slate-100);
      color: var(--color-slate-700);
      font-size: 13px;
      font-weight: 600;
      padding: 7px 14px;
      border-radius: 6px;
      text-decoration: none;
      border: 1px solid var(--color-slate-200);
      transition: all 0.15s ease;
    }
    .btn-link-public:hover {
      background: #ffffff;
      color: var(--brand-red);
      border-color: var(--brand-red-border);
    }

    .btn-logout {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      background: transparent;
      color: var(--color-slate-500);
      font-size: 13px;
      padding: 7px 10px;
      border-radius: 6px;
      text-decoration: none;
      transition: all 0.15s ease;
    }
    .btn-logout:hover {
      color: #dc2626;
      background: #fee2e2;
    }

    /* CONTENEDOR PRINCIPAL */
    .admin-container {
      max-width: 1040px;
      width: 100%;
      margin: 24px auto 48px auto;
      padding: 0 16px;
      flex: 1;
    }

    /* ESTADO EN VIVO HERO */
    .live-status-hero {
      background: #ffffff;
      border-radius: 12px;
      padding: 20px 24px;
      margin-bottom: 24px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
      border: 1px solid var(--color-slate-200);
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 16px;
    }

    .live-status-hero .status-info {
      display: flex;
      align-items: center;
      gap: 14px;
    }

    .pulse-dot {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      position: relative;
      display: inline-block;
      flex-shrink: 0;
    }
    .pulse-dot::after {
      content: '';
      position: absolute;
      top: -4px;
      left: -4px;
      right: -4px;
      bottom: -4px;
      border-radius: 50%;
      animation: pulse 2s infinite ease-out;
      opacity: 0.7;
    }
    @keyframes pulse {
      0% { transform: scale(0.8); opacity: 0.8; }
      70% { transform: scale(2.2); opacity: 0; }
      100% { transform: scale(2.2); opacity: 0; }
    }

    .dot-open { background-color: var(--state-open); }
    .dot-open::after { background-color: var(--state-open); }
    .dot-live { background-color: var(--state-live); }
    .dot-live::after { background-color: var(--state-live); }
    .dot-closed { background-color: var(--state-closed); }
    .dot-closed::after { background-color: var(--state-closed); }
    .dot-results { background-color: var(--state-results); }
    .dot-results::after { background-color: var(--state-results); }
    .dot-gallery { background-color: var(--state-gallery); }
    .dot-gallery::after { background-color: var(--state-gallery); }

    .live-title-group h2 {
      margin: 0;
      font-size: 18px;
      font-weight: 700;
      color: var(--color-slate-900);
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .live-title-group p {
      margin: 4px 0 0 0;
      font-size: 13px;
      color: var(--color-slate-500);
    }

    .status-badge {
      display: inline-flex;
      align-items: center;
      padding: 3px 10px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 700;
      letter-spacing: 0.3px;
    }
    .badge-open { background: var(--state-open-bg); color: #065f46; border: 1px solid #a7f3d0; }
    .badge-live { background: var(--state-live-bg); color: #991b1b; border: 1px solid #fecaca; }
    .badge-closed { background: var(--state-closed-bg); color: #92400e; border: 1px solid #fde68a; }
    .badge-results { background: var(--state-results-bg); color: #5b21b6; border: 1px solid #ddd6fe; }
    .badge-gallery { background: var(--state-gallery-bg); color: #075985; border: 1px solid #bae6fd; }

    .mode-indicator {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 6px 12px;
      border-radius: 8px;
      font-size: 12px;
      font-weight: 600;
      background: var(--color-slate-100);
      color: var(--color-slate-700);
      border: 1px solid var(--color-slate-200);
    }

    /* ALERTAS DE ESTADO */
    .alert-banner {
      padding: 14px 18px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 14px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .alert-banner.success {
      background: #ecfdf5;
      color: #065f46;
      border: 1px solid #a7f3d0;
    }
    .alert-banner.error {
      background: #fef2f2;
      color: #991b1b;
      border: 1px solid #fecaca;
    }
    .alert-banner.warning {
      background: #fffbeb;
      color: #92400e;
      border: 1px solid #fde68a;
    }

    /* PESTAÑAS (TABS) */
    .admin-tabs {
      display: flex;
      gap: 8px;
      margin-bottom: 16px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.15);
      padding-bottom: 8px;
    }

    .tab-btn {
      background: rgba(255, 255, 255, 0.08);
      color: #e2e8f0;
      border: 1px solid rgba(255, 255, 255, 0.12);
      padding: 10px 18px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.15s ease;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }
    .tab-btn:hover {
      background: rgba(255, 255, 255, 0.18);
      color: #ffffff;
    }
    .tab-btn.active {
      background: #ffffff;
      color: var(--brand-red);
      border-color: #ffffff;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .tab-pane {
      display: none;
    }
    .tab-pane.active {
      display: block;
    }

    /* TARJETAS DE CONTENIDO */
    .admin-card {
      background: #ffffff;
      border-radius: 12px;
      padding: 24px;
      margin-bottom: 24px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.06);
      border: 1px solid var(--color-slate-200);
    }

    .card-header {
      border-bottom: 1px solid var(--color-slate-200);
      padding-bottom: 16px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 12px;
    }

    .card-header h3 {
      margin: 0;
      font-size: 16px;
      font-weight: 700;
      color: var(--color-slate-900);
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .card-header p {
      margin: 4px 0 0 0;
      font-size: 13px;
      color: var(--color-slate-500);
    }

    /* CAMPOS DE FORMULARIO */
    .form-group {
      margin-bottom: 20px;
    }

    .form-group:last-child {
      margin-bottom: 0;
    }

    label.form-label {
      display: block;
      font-size: 13px;
      font-weight: 700;
      color: var(--color-slate-700);
      margin-bottom: 6px;
    }

    .form-help {
      font-size: 12px;
      color: var(--color-slate-500);
      margin-top: 5px;
      line-height: 1.4;
    }

    input[type="text"],
    input[type="password"],
    input[type="datetime-local"],
    select,
    textarea {
      width: 100%;
      padding: 9px 12px;
      border: 1px solid var(--color-slate-300);
      border-radius: 6px;
      font-family: inherit;
      font-size: 14px;
      color: var(--color-slate-900);
      background: #ffffff;
      transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    input[type="text"]:focus,
    input[type="password"]:focus,
    input[type="datetime-local"]:focus,
    select:focus,
    textarea:focus {
      outline: none;
      border-color: var(--brand-red);
      box-shadow: 0 0 0 3px rgba(206, 0, 0, 0.15);
    }

    textarea.code-editor {
      font-family: var(--font-mono);
      font-size: 13px;
      line-height: 1.5;
      background: #0f172a;
      color: #f8fafc;
      border: 1px solid #334155;
      min-height: 380px;
      tab-size: 2;
    }

    /* SWITCH / TOGGLES */
    .toggle-group {
      display: flex;
      align-items: center;
      gap: 12px;
      background: var(--color-slate-50);
      padding: 12px 16px;
      border-radius: 8px;
      border: 1px solid var(--color-slate-200);
    }

    .toggle-switch {
      position: relative;
      display: inline-block;
      width: 48px;
      height: 26px;
      flex-shrink: 0;
    }

    .toggle-switch input {
      opacity: 0;
      width: 0;
      height: 0;
    }

    .slider {
      position: absolute;
      cursor: pointer;
      top: 0; left: 0; right: 0; bottom: 0;
      background-color: var(--color-slate-300);
      transition: .25s;
      border-radius: 34px;
    }

    .slider:before {
      position: absolute;
      content: "";
      height: 20px;
      width: 20px;
      left: 3px;
      bottom: 3px;
      background-color: white;
      transition: .25s;
      border-radius: 50%;
      box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    input:checked + .slider {
      background-color: var(--brand-red);
    }

    input:checked + .slider:before {
      transform: translateX(22px);
    }

    .toggle-label-text {
      flex: 1;
    }

    .toggle-label-text strong {
      display: block;
      font-size: 14px;
      color: var(--color-slate-900);
    }

    .toggle-label-text span {
      font-size: 12px;
      color: var(--color-slate-500);
    }

    /* FASES DEL CALENDARIO */
    .fases-container {
      display: flex;
      flex-direction: column;
      gap: 16px;
      margin-top: 16px;
    }

    .fase-item {
      border: 1px solid var(--color-slate-200);
      background: var(--color-slate-50);
      border-radius: 10px;
      padding: 18px;
      position: relative;
      transition: all 0.2s ease;
    }

    .fase-item.active-now {
      border-color: var(--brand-red);
      background: #ffffff;
      box-shadow: 0 0 0 2px rgba(206, 0, 0, 0.12);
    }

    .fase-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 14px;
      padding-bottom: 10px;
      border-bottom: 1px solid var(--color-slate-200);
    }

    .fase-title {
      display: flex;
      align-items: center;
      gap: 10px;
      font-weight: 700;
      font-size: 14px;
      color: var(--color-slate-900);
    }

    .fase-number-badge {
      background: var(--color-slate-800);
      color: #ffffff;
      border-radius: 6px;
      padding: 2px 8px;
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 0.5px;
    }

    .fase-actions {
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .btn-icon {
      background: #ffffff;
      border: 1px solid var(--color-slate-200);
      border-radius: 6px;
      width: 28px;
      height: 28px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      color: var(--color-slate-600);
      font-size: 12px;
      transition: all 0.15s ease;
    }
    .btn-icon:hover {
      background: var(--color-slate-100);
      color: var(--color-slate-900);
    }
    .btn-icon.btn-danger:hover {
      background: #fee2e2;
      border-color: #fca5a5;
      color: #dc2626;
    }

    .fase-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
    }
    @media (max-width: 768px) {
      .fase-grid {
        grid-template-columns: 1fr;
      }
    }

    .fase-grid-full {
      grid-column: 1 / -1;
    }

    .checkbox-inline {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      font-size: 13px;
      color: var(--color-slate-700);
      cursor: pointer;
      margin-top: 6px;
      user-select: none;
    }
    .checkbox-inline input {
      cursor: pointer;
    }

    /* BOTONES GLOBALES */
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 10px 20px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 700;
      cursor: pointer;
      border: none;
      transition: all 0.15s ease;
      text-decoration: none;
    }

    .btn-primary {
      background: var(--brand-red);
      color: #ffffff;
      box-shadow: 0 4px 14px rgba(206, 0, 0, 0.3);
    }
    .btn-primary:hover {
      background: var(--brand-red-dark);
      transform: translateY(-1px);
      box-shadow: 0 6px 20px rgba(206, 0, 0, 0.4);
    }

    .btn-secondary {
      background: var(--color-slate-100);
      color: var(--color-slate-700);
      border: 1px solid var(--color-slate-300);
    }
    .btn-secondary:hover {
      background: #ffffff;
      color: var(--color-slate-900);
      border-color: var(--color-slate-400);
    }

    .btn-add-fase {
      width: 100%;
      border: 2px dashed var(--color-slate-300);
      background: #ffffff;
      color: var(--color-slate-600);
      padding: 14px;
      border-radius: 10px;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.15s ease;
      margin-top: 14px;
    }
    .btn-add-fase:hover {
      border-color: var(--brand-red);
      color: var(--brand-red);
      background: var(--brand-red-light);
    }

    .actions-bar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      margin-top: 24px;
      padding-top: 20px;
      border-top: 1px solid var(--color-slate-200);
      flex-wrap: wrap;
    }

    /* LOGIN MODAL / SCREEN */
    .login-wrapper {
      max-width: 420px;
      margin: 80px auto;
      padding: 0 16px;
    }
    .login-card {
      background: #ffffff;
      border-radius: 16px;
      padding: 32px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.3);
      border: 1px solid var(--color-slate-200);
      text-align: center;
    }
    .login-card .logo-mark {
      background: var(--brand-red);
      color: #ffffff;
      width: 52px;
      height: 52px;
      border-radius: 12px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      margin-bottom: 16px;
      font-weight: 900;
    }
    .login-card h2 {
      margin: 0 0 8px 0;
      font-size: 20px;
      color: var(--color-slate-900);
    }
    .login-card p {
      margin: 0 0 24px 0;
      font-size: 13px;
      color: var(--color-slate-500);
    }

    /* FOOTER */
    .admin-footer {
      text-align: center;
      padding: 24px 16px;
      font-size: 12px;
      color: #64748b;
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      margin-top: auto;
    }
  </style>
</head>
<body>

  <!-- BARRA DE NAVEGACIÓN -->
  <header class="admin-navbar">
    <div class="brand-group">
      <span class="brand-badge">HOY</span>
      <h1>
        Cáceres en el Foco
        <span class="subtitle">| Panel de Control (config.json)</span>
      </h1>
    </div>
    <div class="nav-actions">
      <a href="../" target="_blank" class="btn-link-public" title="Abrir landing pública en nueva pestaña">
        Ver Landing Pública ↗
      </a>
      <?php if ($authRequired && $isLoggedIn): ?>
        <a href="?action=logout" class="btn-logout" title="Cerrar sesión">
          Cerrar Sesión ➔
        </a>
      <?php endif; ?>
    </div>
  </header>

  <div class="admin-container">

    <?php if (!$isLoggedIn): ?>
      <!-- PANTALLA DE ACCESO / LOGIN -->
      <div class="login-wrapper">
        <div class="login-card">
          <div class="logo-mark">⚙</div>
          <h2>Acceso al Panel de Control</h2>
          <p>Introduce la contraseña de administración para actualizar el concurso.</p>

          <?php if (!empty($loginError)): ?>
            <div class="alert-banner error" style="margin-bottom: 18px; text-align: left;">
              <?php echo htmlspecialchars($loginError); ?>
            </div>
          <?php endif; ?>

          <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <input type="hidden" name="action" value="login">
            <div class="form-group" style="text-align: left;">
              <label class="form-label" for="password">Contraseña de Administrador</label>
              <input type="password" id="password" name="password" required autofocus placeholder="••••••••">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">
              Entrar al Panel
            </button>
          </form>
        </div>
      </div>

    <?php else: ?>

      <!-- BANNER DE AVISOS -->
      <?php if (!empty($msgSuccess)): ?>
        <div class="alert-banner success">
          <span>✔ <?php echo htmlspecialchars($msgSuccess); ?></span>
          <button type="button" onclick="this.parentElement.style.display='none'" style="background:none; border:none; cursor:pointer; color:inherit; font-size:16px;">&times;</button>
        </div>
      <?php endif; ?>

      <?php if (!empty($msgError)): ?>
        <div class="alert-banner error">
          <span>✖ <?php echo htmlspecialchars($msgError); ?></span>
          <button type="button" onclick="this.parentElement.style.display='none'" style="background:none; border:none; cursor:pointer; color:inherit; font-size:16px;">&times;</button>
        </div>
      <?php endif; ?>

      <?php if (!$isWritable): ?>
        <div class="alert-banner warning">
          <span>⚠ <strong>Atención de permisos:</strong> El fichero <code>config.json</code> no tiene permisos de escritura para el servidor web. Asegúrese de aplicar <code>chmod 664 config.json</code> en el servidor.</span>
        </div>
      <?php endif; ?>

      <!-- ESTADO EN VIVO HERO -->
      <div class="live-status-hero">
        <div class="status-info">
          <?php
            $dotClass = 'dot-closed';
            if ($estadoResuelto === 'INSCRIPCIONES_ABIERTAS') $dotClass = 'dot-open';
            elseif ($estadoResuelto === 'CONCURSO_ABIERTO') $dotClass = 'dot-live';
            elseif ($estadoResuelto === 'GALERIA_ABIERTA') $dotClass = 'dot-gallery';
            elseif ($estadoResuelto === 'GANADORES_PUBLICADOS' || $estadoResuelto === 'GALERIA_Y_GANADORES') $dotClass = 'dot-results';
          ?>
          <span class="pulse-dot <?php echo $dotClass; ?>"></span>
          <div class="live-title-group">
            <h2>
              Estado Activo:
              <span class="status-badge <?php echo isset($estadosValidos[$estadoResuelto]) ? $estadosValidos[$estadoResuelto]['badge'] : 'badge-closed'; ?>">
                <?php echo htmlspecialchars($estadoResuelto); ?>
              </span>
            </h2>
            <p>
              Hora oficial del servidor: <strong><?php echo date('d/m/Y H:i:s'); ?></strong> (Europe/Madrid)
              <?php if ($temporizadorResuelto && !empty($fechaObjResuelta)): ?>
                · Temporizador activo hasta: <strong><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($fechaObjResuelta))); ?></strong>
              <?php endif; ?>
            </p>
          </div>
        </div>

        <div>
          <span class="mode-indicator">
            <?php if ($modoActivoReal === 'auto'): ?>
              📅 Modo Automático por Fechas (Fase #<?php echo ($faseActivaIndex !== null) ? ($faseActivaIndex + 1) : '?'; ?>)
            <?php else: ?>
              🔒 Modo Manual Fijo
            <?php endif; ?>
          </span>
        </div>
      </div>

      <!-- PESTAÑAS -->
      <div class="admin-tabs">
        <button type="button" class="tab-btn active" onclick="cfSwitchTab('tab-visual', this)">
          📋 Formulario Guiado
        </button>
        <button type="button" class="tab-btn" onclick="cfSwitchTab('tab-raw', this)">
          { } Editor JSON Directo
        </button>
        <button type="button" class="tab-btn" onclick="cfSwitchTab('tab-backup', this)">
          💾 Copias de Seguridad
        </button>
      </div>

      <!-- ===================================================================== -->
      <!-- PESTAÑA 1: FORMULARIO VISUAL                                          -->
      <!-- ===================================================================== -->
      <div id="tab-visual" class="tab-pane active">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="cf-form-visual">
          <input type="hidden" name="action" value="save_form">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

          <!-- TARJETA: MODO DE CONTROL -->
          <div class="admin-card">
            <div class="card-header">
              <div>
                <h3>⚙ 1. Modo de Control del Concurso</h3>
                <p>Elige si las fases cambian automáticamente según el reloj o si prefieres forzar un estado manualmente.</p>
              </div>
            </div>

            <div class="toggle-group">
              <label class="toggle-switch">
                <input type="checkbox" id="control_por_fechas" name="control_por_fechas" value="1" <?php echo ($currentConfig['control_por_fechas']) ? 'checked' : ''; ?> onchange="cfToggleControlMode(this.checked)">
                <span class="slider"></span>
              </label>
              <div class="toggle-label-text">
                <strong id="mode-title-text">
                  <?php echo ($currentConfig['control_por_fechas']) ? 'Modo Automático por Fechas (Recomendado)' : 'Modo Manual Fijo'; ?>
                </strong>
                <span id="mode-desc-text">
                  <?php echo ($currentConfig['control_por_fechas']) ? 'El sistema evalúa la fecha y hora oficial para activar cada fase programada a continuación.' : 'El concurso queda fijo en el estado seleccionado en el bloque manual.'; ?>
                </span>
              </div>
            </div>
          </div>

          <!-- TARJETA: CONFIGURACIÓN MODO MANUAL (FALLBACK / MODO FIJO) -->
          <div class="admin-card" id="card-modo-manual" style="<?php echo ($currentConfig['control_por_fechas']) ? 'opacity: 0.75;' : ''; ?>">
            <div class="card-header">
              <div>
                <h3>🔒 2. Configuración para Modo Manual</h3>
                <p>Aplica cuando <em>control_por_fechas</em> está desactivado, o como respaldo predeterminado.</p>
              </div>
              <span class="status-badge badge-closed" id="manual-notice-badge" style="<?php echo ($currentConfig['control_por_fechas']) ? 'display:inline-block;' : 'display:none;'; ?>">
                En pausa (manda el calendario automático)
              </span>
            </div>

            <div class="form-group">
              <label class="form-label" for="manual_estado">Estado Fijo del Concurso:</label>
              <select id="manual_estado" name="estado">
                <?php foreach ($estadosValidos as $stKey => $stData): ?>
                  <option value="<?php echo $stKey; ?>" <?php echo ($currentConfig['estado'] === $stKey) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($stData['label']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="form-help">
                Selecciona qué botón de la landing estará habilitado y destacado en rojo.
              </div>
            </div>

            <div class="form-group">
              <label class="checkbox-inline">
                <input type="checkbox" name="mostrar_temporizador" value="1" <?php echo ($currentConfig['mostrar_temporizador']) ? 'checked' : ''; ?> id="manual_mostrar_temp" onchange="cfToggleManualTemp(this.checked)">
                <strong>Mostrar reloj de cuenta atrás en la landing pública</strong>
              </label>
            </div>

            <div id="manual_temp_fields" style="<?php echo ($currentConfig['mostrar_temporizador']) ? '' : 'display:none;'; ?>">
              <div class="fase-grid" style="margin-top: 14px;">
                <div class="form-group">
                  <label class="form-label" for="manual_fecha_obj">Fecha y Hora Objetivo del Temporizador:</label>
                  <input type="datetime-local" id="manual_fecha_obj" name="fecha_objetivo_temporizador" value="<?php echo htmlspecialchars(cf_format_for_input($currentConfig['fecha_objetivo_temporizador'])); ?>">
                  <div class="form-help">Hora en Madrid peninsular (ej: 21/11/2026 12:00).</div>
                </div>

                <div class="form-group">
                  <label class="form-label" for="manual_rotulo">Texto / Rótulo de la Cuenta Atrás:</label>
                  <input type="text" id="manual_rotulo" name="rotulo_temporizador" value="<?php echo htmlspecialchars($currentConfig['rotulo_temporizador']); ?>" placeholder="Cuenta atrás para el inicio del rally fotográfico">
                  <div class="form-help">Texto mostrado encima de los dígitos del reloj.</div>
                </div>
              </div>
            </div>
          </div>

          <!-- TARJETA: FASES DEL CALENDARIO AUTOMÁTICO -->
          <div class="admin-card" id="card-fases-fechas">
            <div class="card-header">
              <div>
                <h3>📅 3. Fases Programadas del Calendario (Automático)</h3>
                <p>Las fases se evalúan en orden de arriba a abajo. La primera fase cuya fecha <em>"Hasta"</em> sea posterior a la hora actual se convertirá en la fase activa.</p>
              </div>
            </div>

            <div class="fases-container" id="fases-list">
              <?php 
                $fases = !empty($currentConfig['fases_fechas']) ? $currentConfig['fases_fechas'] : array();
                foreach ($fases as $idx => $fase):
                  $isFaseActive = ($modoActivoReal === 'auto' && $faseActivaIndex === $idx);
                  $hastaVal = isset($fase['hasta']) ? $fase['hasta'] : null;
                  $hasHasta = ($hastaVal !== null && $hastaVal !== '');
                  $fechaObjVal = isset($fase['fecha_objetivo_temporizador']) ? $fase['fecha_objetivo_temporizador'] : null;
                  $hasFechaObj = ($fechaObjVal !== null && $fechaObjVal !== '');
                  $mostrarTemp = isset($fase['mostrar_temporizador']) ? (bool)$fase['mostrar_temporizador'] : false;
                  $rotuloVal = isset($fase['rotulo_temporizador']) ? $fase['rotulo_temporizador'] : '';
                  $stFase = isset($fase['estado']) ? $fase['estado'] : 'CERRADO';
              ?>
                <div class="fase-item <?php echo ($isFaseActive) ? 'active-now' : ''; ?>" data-index="<?php echo $idx; ?>">
                  <div class="fase-header">
                    <div class="fase-title">
                      <span class="fase-number-badge">FASE #<span class="fase-num"><?php echo ($idx + 1); ?></span></span>
                      <?php if ($isFaseActive): ?>
                        <span class="status-badge badge-live">🔴 ACTIVA AHORA</span>
                      <?php endif; ?>
                    </div>
                    <div class="fase-actions">
                      <button type="button" class="btn-icon" title="Subir fase" onclick="cfMoveFase(this, -1)">▲</button>
                      <button type="button" class="btn-icon" title="Bajar fase" onclick="cfMoveFase(this, 1)">▼</button>
                      <button type="button" class="btn-icon btn-danger" title="Eliminar fase" onclick="cfRemoveFase(this)">✕</button>
                    </div>
                  </div>

                  <div class="fase-grid">
                    <!-- Estado de la fase -->
                    <div class="form-group">
                      <label class="form-label">Estado asignado a esta fase:</label>
                      <select name="fases[<?php echo $idx; ?>][estado]">
                        <?php foreach ($estadosValidos as $stKey => $stData): ?>
                          <option value="<?php echo $stKey; ?>" <?php echo ($stFase === $stKey) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($stData['label']); ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>

                    <!-- Hasta cuándo -->
                    <div class="form-group">
                      <label class="form-label">Válida hasta (fecha y hora fin):</label>
                      <input type="datetime-local" class="input-hasta" name="fases[<?php echo $idx; ?>][hasta]" value="<?php echo htmlspecialchars(cf_format_for_input($hastaVal)); ?>" <?php echo (!$hasHasta) ? 'disabled style="opacity:0.5;"' : ''; ?>>
                      <label class="checkbox-inline">
                        <input type="checkbox" name="fases[<?php echo $idx; ?>][hasta_null]" value="1" <?php echo (!$hasHasta) ? 'checked' : ''; ?> onchange="cfToggleHastaNull(this)">
                        Sin fecha fin (última fase indefinida / null)
                      </label>
                    </div>

                    <!-- Temporizador en la fase -->
                    <div class="form-group fase-grid-full">
                      <label class="checkbox-inline">
                        <input type="checkbox" name="fases[<?php echo $idx; ?>][mostrar_temporizador]" value="1" <?php echo ($mostrarTemp) ? 'checked' : ''; ?> onchange="cfToggleFaseTemp(this)">
                        <strong>Mostrar cuenta atrás durante esta fase</strong>
                      </label>
                    </div>

                    <!-- Campos del temporizador de la fase -->
                    <div class="form-group fase-temp-fields fase-grid-full" style="<?php echo ($mostrarTemp) ? '' : 'display:none;'; ?>">
                      <div class="fase-grid">
                        <div>
                          <label class="form-label">Fecha objetivo de la cuenta atrás:</label>
                          <input type="datetime-local" class="input-temp-obj" name="fases[<?php echo $idx; ?>][fecha_objetivo_temporizador]" value="<?php echo htmlspecialchars(cf_format_for_input($fechaObjVal)); ?>" <?php echo (!$hasFechaObj) ? 'disabled style="opacity:0.5;"' : ''; ?>>
                          <div style="display:flex; align-items:center; gap:12px; margin-top:4px;">
                            <label class="checkbox-inline">
                              <input type="checkbox" name="fases[<?php echo $idx; ?>][fecha_objetivo_null]" value="1" <?php echo (!$hasFechaObj) ? 'checked' : ''; ?> onchange="cfToggleFaseTempObjNull(this)">
                              Sin objetivo específico (null)
                            </label>
                            <button type="button" class="btn-secondary" style="padding: 2px 8px; font-size: 11px; border-radius: 4px;" onclick="cfCopyHastaToTemp(this)">
                              Copiar fecha "Hasta"
                            </button>
                          </div>
                        </div>

                        <div>
                          <label class="form-label">Rótulo de la cuenta atrás:</label>
                          <input type="text" name="fases[<?php echo $idx; ?>][rotulo_temporizador]" value="<?php echo htmlspecialchars($rotuloVal); ?>" placeholder="Ej: Cuenta atrás para el inicio...">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>

            <button type="button" class="btn-add-fase" onclick="cfAddFase()">
              + Añadir Nueva Fase al Calendario
            </button>
          </div>

          <!-- BARRA DE ACCIÓN GUARDAR -->
          <div class="actions-bar">
            <div>
              <span style="font-size: 13px; color: #cbd5e1;">
                El archivo <code>config.json</code> se actualizará de forma atómica.
              </span>
            </div>
            <button type="submit" class="btn btn-primary" style="padding: 12px 28px; font-size: 15px;">
              💾 Guardar Cambios en config.json
            </button>
          </div>
        </form>
      </div>

      <!-- ===================================================================== -->
      <!-- PESTAÑA 2: EDITOR RAW JSON                                            -->
      <!-- ===================================================================== -->
      <div id="tab-raw" class="tab-pane">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
          <input type="hidden" name="action" value="save_raw">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

          <div class="admin-card">
            <div class="card-header">
              <div>
                <h3>{ } Edición Directa del Fichero JSON</h3>
                <p>Edita el archivo en formato texto plano si necesitas modificar campos personalizados o comentarios.</p>
              </div>
            </div>

            <div class="form-group">
              <textarea class="code-editor" name="raw_json" id="raw_json" spellcheck="false"><?php echo htmlspecialchars($configRawString); ?></textarea>
              <div class="form-help">
                Asegúrate de que la sintaxis JSON sea válida antes de pulsar Guardar.
              </div>
            </div>

            <div class="actions-bar">
              <button type="button" class="btn btn-secondary" onclick="cfValidateRawJson()">
                🔍 Validar Sintaxis JSON
              </button>
              <button type="submit" class="btn btn-primary">
                💾 Guardar JSON Directo
              </button>
            </div>
          </div>
        </form>
      </div>

      <!-- ===================================================================== -->
      <!-- PESTAÑA 3: COPIAS DE SEGURIDAD                                        -->
      <!-- ===================================================================== -->
      <div id="tab-backup" class="tab-pane">
        <div class="admin-card">
          <div class="card-header">
            <div>
              <h3>💾 Copia de Seguridad Automática (config.json.bak)</h3>
              <p>Cada vez que guardas cambios, se crea automáticamente una copia del archivo anterior.</p>
            </div>
          </div>

          <p style="font-size: 14px; color: var(--color-slate-700);">
            Estado de la copia de respaldo:
            <?php if ($hasBackup): ?>
              <strong style="color: #059669;">✔ Copia disponible</strong>
              (última modificación: <?php echo date('d/m/Y H:i:s', filemtime($backupFile)); ?>)
            <?php else: ?>
              <strong style="color: var(--color-slate-400);">No hay copia de seguridad previa aún.</strong>
            <?php endif; ?>
          </p>

          <?php if ($hasBackup): ?>
            <div style="background: var(--color-slate-100); padding: 16px; border-radius: 8px; margin: 18px 0; font-size: 13px; color: var(--color-slate-600);">
              Si has cometido un error de configuración o el concurso no responde adecuadamente, puedes restaurar el archivo al estado previo con un solo clic.
            </div>

            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" onsubmit="return confirm('¿Está seguro de que desea restaurar la copia de seguridad anterior? Se sobrescribirá el config.json actual.');">
              <input type="hidden" name="action" value="restore_backup">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
              <button type="submit" class="btn btn-primary" style="background:#475569;">
                ↺ Restaurar Última Copia de Seguridad
              </button>
            </form>
          <?php endif; ?>
        </div>
      </div>

    <?php endif; ?>

  </div>

  <footer class="admin-footer">
    Cáceres en el Foco &copy; <?php echo date('Y'); ?> Diario HOY · Panel de Configuración Optimizado para PHP 5.3+
  </footer>

  <!-- ========================================================================= -->
  <!-- JAVASCRIPT DE INTERFAZ Y VALIDACIÓN                                       -->
  <!-- ========================================================================= -->
  <script>
    // CAMBIO DE PESTAÑAS
    function cfSwitchTab(tabId, btn) {
      var panes = document.querySelectorAll('.tab-pane');
      for (var i = 0; i < panes.length; i++) {
        panes[i].classList.remove('active');
      }
      var tabs = document.querySelectorAll('.tab-btn');
      for (var j = 0; j < tabs.length; j++) {
        tabs[j].classList.remove('active');
      }
      var target = document.getElementById(tabId);
      if (target) {
        target.classList.add('active');
      }
      if (btn) {
        btn.classList.add('active');
      }
    }

    // ALTERNAR MODO AUTOMÁTICO / MANUAL
    function cfToggleControlMode(isAuto) {
      var titleText = document.getElementById('mode-title-text');
      var descText = document.getElementById('mode-desc-text');
      var manualCard = document.getElementById('card-modo-manual');
      var manualBadge = document.getElementById('manual-notice-badge');

      if (isAuto) {
        titleText.textContent = 'Modo Automático por Fechas (Recomendado)';
        descText.textContent = 'El sistema evalúa la fecha y hora oficial para activar cada fase programada a continuación.';
        manualCard.style.opacity = '0.75';
        manualBadge.style.display = 'inline-block';
      } else {
        titleText.textContent = 'Modo Manual Fijo';
        descText.textContent = 'El concurso queda fijo en el estado seleccionado en el bloque manual.';
        manualCard.style.opacity = '1';
        manualBadge.style.display = 'none';
      }
    }

    // ALTERNAR TEMPORIZADOR MANUAL
    function cfToggleManualTemp(show) {
      var container = document.getElementById('manual_temp_fields');
      if (container) {
        container.style.display = show ? 'block' : 'none';
      }
    }

    // ALTERNAR TEMPORIZADOR EN UNA FASE
    function cfToggleFaseTemp(cb) {
      var faseItem = cb.closest('.fase-item');
      var fields = faseItem.querySelector('.fase-temp-fields');
      if (fields) {
        fields.style.display = cb.checked ? 'block' : 'none';
      }
    }

    // ALTERNAR "HASTA" SIN LÍMITE (NULL)
    function cfToggleHastaNull(cb) {
      var faseItem = cb.closest('.fase-item');
      var input = faseItem.querySelector('.input-hasta');
      if (input) {
        input.disabled = cb.checked;
        input.style.opacity = cb.checked ? '0.5' : '1';
      }
    }

    // ALTERNAR FECHA OBJETIVO NULL
    function cfToggleFaseTempObjNull(cb) {
      var faseItem = cb.closest('.fase-item');
      var input = faseItem.querySelector('.input-temp-obj');
      if (input) {
        input.disabled = cb.checked;
        input.style.opacity = cb.checked ? '0.5' : '1';
      }
    }

    // COPIAR FECHA HASTA A FECHA OBJETIVO DEL TEMPORIZADOR
    function cfCopyHastaToTemp(btn) {
      var faseItem = btn.closest('.fase-item');
      var inputHasta = faseItem.querySelector('.input-hasta');
      var inputObj = faseItem.querySelector('.input-temp-obj');
      var cbNull = faseItem.querySelector('input[name*="[fecha_objetivo_null]"]');
      if (inputHasta && inputObj && inputHasta.value) {
        inputObj.value = inputHasta.value;
        if (cbNull) {
          cbNull.checked = false;
          inputObj.disabled = false;
          inputObj.style.opacity = '1';
        }
      } else {
        alert('Defina primero una fecha válida en el campo "Válida hasta".');
      }
    }

    // REENUMERAR FASES TRAS MOVER / ELIMINAR / AGREGAR
    function cfRenumberFases() {
      var items = document.querySelectorAll('#fases-list .fase-item');
      for (var i = 0; i < items.length; i++) {
        var item = items[i];
        item.setAttribute('data-index', i);
        var numSpan = item.querySelector('.fase-num');
        if (numSpan) {
          numSpan.textContent = (i + 1);
        }
        // Actualizar nombres de inputs
        var inputs = item.querySelectorAll('input, select');
        for (var k = 0; k < inputs.length; k++) {
          var el = inputs[k];
          var name = el.getAttribute('name');
          if (name && name.indexOf('fases[') === 0) {
            el.setAttribute('name', name.replace(/^fases\[\d+\]/, 'fases[' + i + ']'));
          }
        }
      }
    }

    // MOVER FASE ARRIBA O ABAJO
    function cfMoveFase(btn, direction) {
      var item = btn.closest('.fase-item');
      var container = document.getElementById('fases-list');
      if (direction === -1 && item.previousElementSibling) {
        container.insertBefore(item, item.previousElementSibling);
        cfRenumberFases();
      } else if (direction === 1 && item.nextElementSibling) {
        container.insertBefore(item.nextElementSibling, item);
        cfRenumberFases();
      }
    }

    // ELIMINAR FASE
    function cfRemoveFase(btn) {
      var items = document.querySelectorAll('#fases-list .fase-item');
      if (items.length <= 1) {
        alert('Debe haber al menos una fase definida en el concurso.');
        return;
      }
      if (confirm('¿Desea eliminar esta fase del calendario?')) {
        var item = btn.closest('.fase-item');
        item.parentNode.removeChild(item);
        cfRenumberFases();
      }
    }

    // AÑADIR NUEVA FASE
    function cfAddFase() {
      var container = document.getElementById('fases-list');
      var count = container.querySelectorAll('.fase-item').length;
      var newIndex = count;

      var div = document.createElement('div');
      div.className = 'fase-item';
      div.setAttribute('data-index', newIndex);

      div.innerHTML = 
        '<div class="fase-header">' +
          '<div class="fase-title">' +
            '<span class="fase-number-badge">FASE #<span class="fase-num">' + (newIndex + 1) + '</span></span>' +
          '</div>' +
          '<div class="fase-actions">' +
            '<button type="button" class="btn-icon" title="Subir fase" onclick="cfMoveFase(this, -1)">▲</button>' +
            '<button type="button" class="btn-icon" title="Bajar fase" onclick="cfMoveFase(this, 1)">▼</button>' +
            '<button type="button" class="btn-icon btn-danger" title="Eliminar fase" onclick="cfRemoveFase(this)">✕</button>' +
          '</div>' +
        '</div>' +
        '<div class="fase-grid">' +
          '<div class="form-group">' +
            '<label class="form-label">Estado asignado a esta fase:</label>' +
            '<select name="fases[' + newIndex + '][estado]">' +
              '<option value="CERRADO">CERRADO (Fase 0 - Previo / Espera)</option>' +
              '<option value="INSCRIPCIONES_ABIERTAS" selected>INSCRIPCIONES_ABIERTAS (Fase 1 - Registro Previo)</option>' +
              '<option value="CONCURSO_ABIERTO">CONCURSO_ABIERTO (Fase 2 - Rally en Marcha)</option>' +
              '<option value="GALERIA_ABIERTA">GALERIA_ABIERTA (Fase 3 - Galería / Deliberación)</option>' +
              '<option value="GANADORES_PUBLICADOS">GANADORES_PUBLICADOS (Fase 4 - Ganadores Oficiales)</option>' +
            '</select>' +
          '</div>' +
          '<div class="form-group">' +
            '<label class="form-label">Válida hasta (fecha y hora fin):</label>' +
            '<input type="datetime-local" class="input-hasta" name="fases[' + newIndex + '][hasta]">' +
            '<label class="checkbox-inline">' +
              '<input type="checkbox" name="fases[' + newIndex + '][hasta_null]" value="1" onchange="cfToggleHastaNull(this)">' +
              'Sin fecha fin (última fase indefinida / null)' +
            '</label>' +
          '</div>' +
          '<div class="form-group fase-grid-full">' +
            '<label class="checkbox-inline">' +
              '<input type="checkbox" name="fases[' + newIndex + '][mostrar_temporizador]" value="1" checked onchange="cfToggleFaseTemp(this)">' +
              '<strong>Mostrar cuenta atrás durante esta fase</strong>' +
            '</label>' +
          '</div>' +
          '<div class="form-group fase-temp-fields fase-grid-full">' +
            '<div class="fase-grid">' +
              '<div>' +
                '<label class="form-label">Fecha objetivo de la cuenta atrás:</label>' +
                '<input type="datetime-local" class="input-temp-obj" name="fases[' + newIndex + '][fecha_objetivo_temporizador]">' +
                '<div style="display:flex; align-items:center; gap:12px; margin-top:4px;">' +
                  '<label class="checkbox-inline">' +
                    '<input type="checkbox" name="fases[' + newIndex + '][fecha_objetivo_null]" value="1" onchange="cfToggleFaseTempObjNull(this)">' +
                    'Sin objetivo específico (null)' +
                  '</label>' +
                  '<button type="button" class="btn-secondary" style="padding: 2px 8px; font-size: 11px; border-radius: 4px;" onclick="cfCopyHastaToTemp(this)">' +
                    'Copiar fecha "Hasta"' +
                  '</button>' +
                '</div>' +
              '</div>' +
              '<div>' +
                '<label class="form-label">Rótulo de la cuenta atrás:</label>' +
                '<input type="text" name="fases[' + newIndex + '][rotulo_temporizador]" placeholder="Cuenta atrás para...">' +
              '</div>' +
            '</div>' +
          '</div>' +
        '</div>';

      container.appendChild(div);
      cfRenumberFases();
    }

    // VALIDACIÓN CLIENTE RAW JSON
    function cfValidateRawJson() {
      var textarea = document.getElementById('raw_json');
      if (!textarea) return;
      try {
        var obj = JSON.parse(textarea.value);
        if (typeof obj !== 'object' || obj === null) {
          throw new Error('El JSON debe ser un objeto.');
        }
        alert('✔ ¡Sintaxis JSON válida y correcta!');
      } catch (err) {
        alert('✖ Error en la sintaxis JSON:\n\n' + err.message);
      }
    }
  </script>
</body>
</html>
