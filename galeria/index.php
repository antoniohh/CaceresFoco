<?php
/**
 * ==============================================================================
 * GALERÍA OFICIAL DE FOTOS - CONCURSO FOTOGRÁFICO "CÁCERES EN EL FOCO"
 * 40º Aniversario de Cáceres como Patrimonio de la Humanidad
 *
 * @author    Antonio Horrillo <ahorrillo@hoy.es>
 * @copyright (c) 2026 Corporación de Medios de Extremadura, S.A. (Grupo Vocento)
 *            HOY Diario de Extremadura · Avda. del Diario Hoy, s/n, 06008 Badajoz
 *            CIF: A-78865441 · Todos los derechos reservados.
 * @link      https://www.hoy.es
 * @link      https://servicios.hoy.es/caceresenelfoco/galeria/
 * @version   1.5.0 (Compatible estrictamente con PHP 5.3+)
 * ==============================================================================
 */

// 0. ZONA HORARIA OFICIAL (España Peninsular)
date_default_timezone_set('Europe/Madrid');

// 1. CARGA DE CONFIGURACIÓN DINÁMICA (panel/config.json)
$rootDir = dirname(dirname(__FILE__));
$configFile = $rootDir . '/panel/config.json';
if (!file_exists($configFile) && file_exists($rootDir . '/config.json')) {
    $configFile = $rootDir . '/config.json';
}

$config = array(
    'control_por_fechas' => false,
    'estado' => 'GALERIA_ABIERTA',
    'mostrar_temporizador' => true,
    'fecha_objetivo_temporizador' => '2026-12-01T10:00:00+01:00',
    'rotulo_temporizador' => 'Cuenta atrás para el anuncio oficial de ganadores (1 de diciembre)',
    'fases_fechas' => array(),
    'ganadores' => array(
        'premio_1' => '',
        'premio_2' => '',
        'premio_3' => ''
    )
);

if (file_exists($configFile)) {
    $fileContent = @file_get_contents($configFile);
    if ($fileContent) {
        $loadedConfig = json_decode($fileContent, true);
        if (is_array($loadedConfig)) {
            if (isset($loadedConfig['control_por_fechas'])) {
                $config['control_por_fechas'] = (bool)$loadedConfig['control_por_fechas'];
            }
            if (isset($loadedConfig['estado'])) {
                $config['estado'] = strtoupper(trim($loadedConfig['estado']));
            }
            if (isset($loadedConfig['mostrar_temporizador'])) {
                $config['mostrar_temporizador'] = (bool)$loadedConfig['mostrar_temporizador'];
            }
            if (isset($loadedConfig['fecha_objetivo_temporizador'])) {
                $config['fecha_objetivo_temporizador'] = $loadedConfig['fecha_objetivo_temporizador'];
            }
            if (isset($loadedConfig['rotulo_temporizador'])) {
                $config['rotulo_temporizador'] = $loadedConfig['rotulo_temporizador'];
            }
            if (isset($loadedConfig['fases_fechas']) && is_array($loadedConfig['fases_fechas'])) {
                $config['fases_fechas'] = $loadedConfig['fases_fechas'];
            }
            if (isset($loadedConfig['ganadores']) && is_array($loadedConfig['ganadores'])) {
                $g = $loadedConfig['ganadores'];
                $config['ganadores']['premio_1'] = isset($g['premio_1']) ? trim((string)$g['premio_1']) : '';
                $config['ganadores']['premio_2'] = isset($g['premio_2']) ? trim((string)$g['premio_2']) : '';
                $config['ganadores']['premio_3'] = isset($g['premio_3']) ? trim((string)$g['premio_3']) : '';
            }
        }
    }
}

$ahoraTimestamp = time();
if ($config['control_por_fechas'] && !empty($config['fases_fechas'])) {
    foreach ($config['fases_fechas'] as $fase) {
        if (!is_array($fase) || !isset($fase['estado'])) {
            continue;
        }
        $desdeTimestamp = (!empty($fase['desde'])) ? strtotime($fase['desde']) : null;
        $hastaTimestamp = (!empty($fase['hasta'])) ? strtotime($fase['hasta']) : null;
        $desdeOk = ($desdeTimestamp === null || $ahoraTimestamp >= $desdeTimestamp);
        $hastaOk = ($hastaTimestamp === null || $ahoraTimestamp < $hastaTimestamp);

        if ($desdeOk && $hastaOk) {
            $config['estado'] = strtoupper(trim($fase['estado']));
            if (isset($fase['mostrar_temporizador'])) {
                $config['mostrar_temporizador'] = (bool)$fase['mostrar_temporizador'];
            }
            if (isset($fase['fecha_objetivo_temporizador']) && !empty($fase['fecha_objetivo_temporizador'])) {
                $config['fecha_objetivo_temporizador'] = $fase['fecha_objetivo_temporizador'];
            } elseif ($hastaTimestamp !== null) {
                $config['fecha_objetivo_temporizador'] = $fase['hasta'];
            }
            if (isset($fase['rotulo_temporizador'])) {
                $config['rotulo_temporizador'] = $fase['rotulo_temporizador'];
            }
            break;
        }
    }
}

$estado = $config['estado'];
$badgeTexto = 'Galería Oficial · Deliberación del Jurado';
$badgeClase = 'badge-gallery';
if ($estado === 'GANADORES_PUBLICADOS' || $estado === 'GALERIA_Y_GANADORES') {
    $badgeTexto = '¡Ganadores Oficiales y Galería!';
    $badgeClase = 'badge-results';
}

// 2. DETECCIÓN DE PROTOCOLO Y DOMINIO PARA SEO Y REDES
$isHttps = false;
if (isset($_SERVER['HTTPS']) && (strtolower((string)$_SERVER['HTTPS']) === 'on' || (string)$_SERVER['HTTPS'] === '1')) {
    $isHttps = true;
} elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
    $xProtocols = explode(',', strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']));
    if (trim($xProtocols[0]) === 'https') {
        $isHttps = true;
    }
} elseif (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_SSL']) === 'on') {
    $isHttps = true;
} elseif (isset($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower((string)$_SERVER['HTTP_FRONT_END_HTTPS']) === 'on') {
    $isHttps = true;
} elseif (isset($_SERVER['SERVER_PORT']) && (string)$_SERVER['SERVER_PORT'] === '443') {
    $isHttps = true;
} elseif (isset($_SERVER['HTTP_X_FORWARDED_PORT']) && (string)$_SERVER['HTTP_X_FORWARDED_PORT'] === '443') {
    $isHttps = true;
}

$host = 'servicios.hoy.es';
if (isset($_SERVER['HTTP_X_FORWARDED_HOST']) && $_SERVER['HTTP_X_FORWARDED_HOST'] !== '') {
    $forwardedHosts = explode(',', $_SERVER['HTTP_X_FORWARDED_HOST']);
    $host = trim($forwardedHosts[0]);
} elseif (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] !== '') {
    $host = $_SERVER['HTTP_HOST'];
} elseif (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] !== '') {
    $host = $_SERVER['SERVER_NAME'];
}

$isLocalhost = (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false || strpos($host, '::1') !== false);
if (!$isLocalhost && (strpos($host, 'hoy.es') !== false || $host === 'servicios.hoy.es')) {
    $isHttps = true;
}

$protocol = $isHttps ? 'https://' : 'http://';

if (isset($_SERVER['PHP_SELF']) && $_SERVER['PHP_SELF'] !== '') {
    $scriptDir = dirname(dirname($_SERVER['PHP_SELF']));
    if ($scriptDir === '.' || $scriptDir === '/' || $scriptDir === '\\' || $scriptDir === '') {
        $pathPrefix = '/';
    } else {
        $pathPrefix = '/' . trim($scriptDir, '/\\') . '/';
    }
} else {
    $pathPrefix = '/caceresenelfoco/';
}

$baseUrl = $protocol . $host . $pathPrefix;
$galleryUrl = $baseUrl . 'galeria/';

// 3. LECTURA DEL CSV DE PARTICIPANTES Y FOTOGRAFÍAS (COMPATIBLE PHP 5.3)
$csvPath = dirname(__FILE__) . '/participantes_galeria.csv';
if (!file_exists($csvPath)) {
    $csvPath = $rootDir . '/docs/participaciones/participantes_y_fotos_completo.csv';
}

$fotos = array();
$autoresUnicos = array();

if (file_exists($csvPath)) {
    $fp = @fopen($csvPath, 'r');
    if ($fp !== false) {
        $headers = fgetcsv($fp, 0, ';');
        if (is_array($headers)) {
            // Limpiar posible BOM UTF-8 en la primera columna
            if (isset($headers[0])) {
                $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
                $headers[0] = trim($headers[0], "\" \t\n\r\0\x0B");
            }
            $colMap = array();
            for ($i = 0; $i < count($headers); $i++) {
                $colName = strtolower(trim($headers[$i]));
                $colMap[$colName] = $i;
            }

            while (($row = fgetcsv($fp, 0, ';')) !== false) {
                if (!is_array($row) || count($row) < 3) {
                    continue;
                }

                $idxIdFoto = isset($colMap['id_foto']) ? $colMap['id_foto'] : 0;
                $idxIdPart = isset($colMap['id_participante']) ? $colMap['id_participante'] : 1;
                $idxNombre = isset($colMap['nombre']) ? $colMap['nombre'] : 2;
                $idxApellidos = isset($colMap['apellidos']) ? $colMap['apellidos'] : 3;
                $idxNombreComp = isset($colMap['nombre_completo']) ? $colMap['nombre_completo'] : 4;
                $idxArchivo = isset($colMap['archivo_foto']) ? $colMap['archivo_foto'] : 10;
                $idxFecha = isset($colMap['fecha']) ? $colMap['fecha'] : 13;
                $idxHora = isset($colMap['hora']) ? $colMap['hora'] : 14;
                $idxVisible = isset($colMap['visible_galeria']) ? $colMap['visible_galeria'] : 16;

                $visible = isset($row[$idxVisible]) ? trim($row[$idxVisible]) : '1';
                if ($visible === '0') {
                    continue;
                }

                $idFoto = isset($row[$idxIdFoto]) ? trim($row[$idxIdFoto]) : '';
                $idParticipante = isset($row[$idxIdPart]) ? trim($row[$idxIdPart]) : '';
                $nombre = isset($row[$idxNombre]) ? trim($row[$idxNombre]) : '';
                $apellidos = isset($row[$idxApellidos]) ? trim($row[$idxApellidos]) : '';
                $nombreCompleto = isset($row[$idxNombreComp]) ? trim($row[$idxNombreComp]) : trim($nombre . ' ' . $apellidos);
                $archivoFoto = isset($row[$idxArchivo]) ? trim($row[$idxArchivo]) : '';
                $fecha = isset($row[$idxFecha]) ? trim($row[$idxFecha]) : '';
                $hora = isset($row[$idxHora]) ? trim($row[$idxHora]) : '';

                if ($archivoFoto === '') {
                    continue;
                }

                // Ruta relativa desde /galeria/ hacia /public/fotos/
                $rutaRelativaWeb = '../public/fotos/' . $archivoFoto;
                $rutaFisica = $rootDir . '/public/fotos/' . $archivoFoto;

                if (!file_exists($rutaFisica)) {
                    continue;
                }

                $fotos[] = array(
                    'id_foto' => $idFoto,
                    'id_participante' => $idParticipante,
                    'nombre' => $nombre,
                    'apellidos' => $apellidos,
                    'nombre_completo' => $nombreCompleto,
                    'archivo' => $archivoFoto,
                    'src' => $rutaRelativaWeb,
                    'url_absoluta' => $baseUrl . 'public/fotos/' . $archivoFoto,
                    'fecha' => $fecha,
                    'hora' => $hora
                );

                if ($nombreCompleto !== '') {
                    if (!isset($autoresUnicos[$nombreCompleto])) {
                        $autoresUnicos[$nombreCompleto] = 0;
                    }
                    $autoresUnicos[$nombreCompleto]++;
                }
            }
        }
        fclose($fp);
    }
}

$listaAutores = array_keys($autoresUnicos);
sort($listaAutores);

$totalFotos = count($fotos);
$totalAutores = count($listaAutores);

// Metadatos SEO
$siteTitle = 'Galería Oficial de Fotografías · Cáceres en el Foco | Diario HOY';
$siteDesc = 'Explora las ' . $totalFotos . ' fotografías participantes en el rally fotográfico Cáceres en el Foco por el 40º Aniversario de Cáceres como Patrimonio de la Humanidad.';
$ogImage = $baseUrl . 'assets/img/concurso_fotos_caceres.jpg';
$shareText = 'Descubre la galería oficial de fotos de «Cáceres en el Foco» en Diario HOY:';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">

  <title><?php echo htmlspecialchars($siteTitle, ENT_QUOTES, 'UTF-8'); ?></title>

  <!-- SEO BÁSICO -->
  <meta name="description" content="<?php echo htmlspecialchars($siteDesc, ENT_QUOTES, 'UTF-8'); ?>">
  <meta name="author" content="Diario HOY">
  <meta name="robots" content="index, follow, max-image-preview:large">
  <link rel="canonical" href="<?php echo htmlspecialchars($galleryUrl, ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="shortcut icon" href="https://static.vocstatic.com/voc26/latest/assets/images/favicon/hoy/favicon.ico" type="image/x-icon" />
  <link rel="icon" href="https://static.vocstatic.com/voc26/latest/assets/images/favicon/hoy/favicon.ico" type="image/x-icon" />
  <meta name="theme-color" content="#ce0000">

  <!-- OPEN GRAPH -->
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="Diario HOY">
  <meta property="og:locale" content="es_ES">
  <meta property="og:url" content="<?php echo htmlspecialchars($galleryUrl, ENT_QUOTES, 'UTF-8'); ?>">
  <meta property="og:title" content="<?php echo htmlspecialchars($siteTitle, ENT_QUOTES, 'UTF-8'); ?>">
  <meta property="og:description" content="<?php echo htmlspecialchars($siteDesc, ENT_QUOTES, 'UTF-8'); ?>">
  <meta property="og:image" content="<?php echo htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8'); ?>">

  <!-- TWITTER CARDS -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:site" content="@hoyextremadura">
  <meta name="twitter:title" content="<?php echo htmlspecialchars($siteTitle, ENT_QUOTES, 'UTF-8'); ?>">
  <meta name="twitter:description" content="<?php echo htmlspecialchars($siteDesc, ENT_QUOTES, 'UTF-8'); ?>">
  <meta name="twitter:image" content="<?php echo htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8'); ?>">

  <!-- ESTILOS BASE DEL PROYECTO -->
  <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">

  <!-- ESTILOS ESPECÍFICOS DE LA GALERÍA -->
  <style>
    .gallery-card {
      max-width: 1140px;
      width: 100%;
      margin: 0 auto;
      background: #ffffff;
      border-radius: 14px;
      box-shadow:
        0 1px 3px rgba(0, 0, 0, 0.08),
        0 8px 24px -4px rgba(15, 23, 42, 0.18),
        0 24px 48px -8px rgba(15, 23, 42, 0.25);
      overflow: hidden;
      border: 1px solid rgba(226, 232, 240, 0.85);
      animation: cardFadeIn 0.45s ease-out;
    }

    /* Barra superior de retorno y estado */
    .gallery-topbar {
      background: #0f172a;
      color: #f8fafc;
      padding: 12px 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 12px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .back-home-link {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      color: #ffffff;
      text-decoration: none;
      font-size: 13px;
      font-weight: 700;
      background: rgba(255, 255, 255, 0.1);
      padding: 7px 14px;
      border-radius: 20px;
      border: 1px solid rgba(255, 255, 255, 0.16);
      transition: all 0.2s ease;
    }

    .back-home-link:hover {
      background: #ce0000;
      border-color: #ce0000;
      transform: translateX(-2px);
    }

    .topbar-status-badge {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      font-size: 11.5px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.6px;
      padding: 5px 13px;
      border-radius: 20px;
      background: rgba(14, 116, 144, 0.9);
      color: #f8fafc;
    }

    /* Cabecera visual compacta de la galería */
    .gallery-hero-header {
      background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #172554 100%);
      color: #ffffff;
      padding: 32px 28px 28px 28px;
      text-align: center;
      position: relative;
      overflow: hidden;
      border-bottom: 3px solid #ce0000;
    }

    .gallery-hero-header::before {
      content: "";
      position: absolute;
      top: -60px;
      right: -60px;
      width: 220px;
      height: 220px;
      background: radial-gradient(circle, rgba(206, 0, 0, 0.28) 0%, rgba(206, 0, 0, 0) 70%);
      border-radius: 50%;
      pointer-events: none;
    }

    .gallery-hero-title {
      font-size: 28px;
      font-weight: 900;
      margin: 6px 0 10px 0;
      letter-spacing: -0.5px;
      color: #ffffff;
      line-height: 1.2;
    }

    .gallery-hero-subtitle {
      font-size: 14.5px;
      color: #cbd5e1;
      max-width: 680px;
      margin: 0 auto 18px auto;
      line-height: 1.55;
    }

    .gallery-stats-row {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      flex-wrap: wrap;
      gap: 12px;
      margin-top: 4px;
    }

    .stat-pill {
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.18);
      padding: 6px 14px;
      border-radius: 20px;
      font-size: 12.5px;
      font-weight: 700;
      color: #f8fafc;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .stat-pill strong {
      color: #fbbf24;
      font-size: 14px;
    }

    /* Cuerpo de la galería */
    .gallery-body {
      padding: 26px 28px 32px 28px;
    }

    @media (max-width: 767px) {
      .gallery-body {
        padding: 18px 14px 24px 14px;
      }
      .gallery-hero-header {
        padding: 24px 16px 22px 16px;
      }
      .gallery-hero-title {
        font-size: 22px;
      }
      .gallery-topbar {
        padding: 10px 14px;
      }
    }

    /* Barra de herramientas: búsqueda, filtro de autor y orden */
    .gallery-toolbar {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 14px 16px;
      margin-bottom: 22px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 12px;
    }

    .toolbar-controls {
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      gap: 10px;
      flex: 1;
    }

    .search-input-wrap {
      position: relative;
      flex: 1 1 240px;
      min-width: 200px;
    }

    .search-input-wrap .search-icon {
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 14px;
      color: #64748b;
      pointer-events: none;
    }

    .gallery-search-input {
      width: 100%;
      padding: 10px 34px 10px 36px;
      border: 1.5px solid #cbd5e1;
      border-radius: 8px;
      font-size: 13.5px;
      color: #0f172a;
      background: #ffffff;
      font-family: inherit;
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .gallery-search-input:focus {
      outline: none;
      border-color: #ce0000;
      box-shadow: 0 0 0 3px rgba(206, 0, 0, 0.12);
    }

    .clear-search-btn {
      position: absolute;
      right: 8px;
      top: 50%;
      transform: translateY(-50%);
      background: #e2e8f0;
      border: none;
      color: #475569;
      width: 22px;
      height: 22px;
      border-radius: 50%;
      font-size: 12px;
      font-weight: 700;
      cursor: pointer;
      display: none;
      align-items: center;
      justify-content: center;
      line-height: 1;
    }

    .clear-search-btn:hover {
      background: #cbd5e1;
      color: #0f172a;
    }

    .gallery-select {
      padding: 10px 12px;
      border: 1.5px solid #cbd5e1;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 600;
      color: #1e293b;
      background: #ffffff;
      font-family: inherit;
      cursor: pointer;
      min-width: 190px;
      transition: border-color 0.2s ease;
    }

    .gallery-select:focus {
      outline: none;
      border-color: #ce0000;
    }

    .toolbar-results-count {
      font-size: 12.5px;
      font-weight: 700;
      color: #475569;
      background: #ffffff;
      border: 1px solid #e2e8f0;
      padding: 8px 12px;
      border-radius: 8px;
      white-space: nowrap;
    }

    /* Grid de fotografías */
    .photo-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
      margin-bottom: 28px;
    }

    @media (max-width: 960px) {
      .photo-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
      }
    }

    @media (max-width: 560px) {
      .photo-grid {
        grid-template-columns: 1fr;
        gap: 16px;
      }
    }

    /* Tarjeta individual de fotografía */
    .photo-card {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
      transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease, border-color 0.25s ease;
      display: flex;
      flex-direction: column;
      cursor: pointer;
    }

    .photo-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 24px -4px rgba(15, 23, 42, 0.16);
      border-color: #cbd5e1;
    }

    .photo-frame {
      position: relative;
      width: 100%;
      padding-top: 75%; /* Relación 4:3 uniforme y elegante */
      background: #0f172a;
      overflow: hidden;
    }

    .photo-frame img {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      transition: transform 0.45s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .photo-card:hover .photo-frame img {
      transform: scale(1.045);
    }

    .photo-zoom-hint {
      position: absolute;
      top: 10px;
      right: 10px;
      background: rgba(15, 23, 42, 0.75);
      backdrop-filter: blur(4px);
      -webkit-backdrop-filter: blur(4px);
      color: #ffffff;
      font-size: 11px;
      font-weight: 700;
      padding: 5px 10px;
      border-radius: 20px;
      opacity: 0;
      transform: translateY(-4px);
      transition: all 0.2s ease;
      pointer-events: none;
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }

    .photo-card:hover .photo-zoom-hint {
      opacity: 1;
      transform: translateY(0);
    }

    /* Medallas de ganadores sobre la foto en la galería */
    .photo-winner-medal {
      position: absolute;
      top: 10px;
      left: 10px;
      font-size: 11px;
      font-weight: 900;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      padding: 5px 11px;
      border-radius: 20px;
      z-index: 4;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      box-shadow: 0 3px 10px rgba(0, 0, 0, 0.35);
    }

    .medal-gold {
      background: linear-gradient(135deg, #fef3c7 0%, #f59e0b 100%);
      color: #451a03;
      border: 1px solid #fde68a;
    }

    .medal-silver {
      background: linear-gradient(135deg, #f8fafc 0%, #cbd5e1 100%);
      color: #0f172a;
      border: 1px solid #ffffff;
    }

    .medal-bronze {
      background: linear-gradient(135deg, #ffedd5 0%, #ea580c 100%);
      color: #ffffff;
      border: 1px solid #fed7aa;
    }

    .photo-card.is-winner-1 {
      border: 2px solid #f59e0b;
      box-shadow: 0 6px 20px rgba(245, 158, 11, 0.22);
    }

    .photo-card.is-winner-2 {
      border: 2px solid #94a3b8;
    }

    .photo-card.is-winner-3 {
      border: 2px solid #d97706;
    }

    /* Pie de foto con el nombre de la persona participante */
    .photo-caption {
      padding: 13px 16px;
      background: #ffffff;
      border-top: 1px solid #f1f5f9;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      flex: 1;
    }

    .author-info {
      display: flex;
      align-items: center;
      gap: 10px;
      min-width: 0;
    }

    .author-avatar {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: linear-gradient(135deg, #ce0000 0%, #990000 100%);
      color: #ffffff;
      font-size: 12.5px;
      font-weight: 800;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      letter-spacing: 0.3px;
      box-shadow: 0 2px 5px rgba(206, 0, 0, 0.25);
    }

    .author-text {
      display: flex;
      flex-direction: column;
      min-width: 0;
    }

    .author-label {
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #94a3b8;
      line-height: 1.1;
      margin-bottom: 2px;
    }

    .author-name {
      font-size: 14.5px;
      font-weight: 800;
      color: #0f172a;
      line-height: 1.25;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .photo-card:hover .author-name {
      color: #ce0000;
    }

    /* Estado vacío en búsqueda */
    .empty-search-state {
      display: none;
      text-align: center;
      padding: 48px 20px;
      background: #f8fafc;
      border: 1.5px dashed #cbd5e1;
      border-radius: 12px;
      margin-bottom: 28px;
    }

    .empty-search-state h3 {
      margin: 8px 0 6px 0;
      font-size: 18px;
      color: #0f172a;
    }

    .empty-search-state p {
      margin: 0 0 14px 0;
      font-size: 13.5px;
      color: #64748b;
    }

    .reset-filter-btn {
      background: #ce0000;
      color: #ffffff;
      border: none;
      padding: 9px 18px;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
    }

    /* Lightbox Modal a pantalla completa */
    .lightbox-overlay {
      position: fixed;
      inset: 0;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(8, 11, 18, 0.94);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
      z-index: 9999;
      display: none;
      flex-direction: column;
      justify-content: space-between;
      padding: 16px;
      opacity: 0;
      transition: opacity 0.25s ease;
    }

    .lightbox-overlay.active {
      display: flex;
      opacity: 1;
    }

    .lightbox-topbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      color: #f8fafc;
      max-width: 1200px;
      width: 100%;
      margin: 0 auto;
      padding: 4px 8px;
    }

    .lightbox-counter {
      font-size: 13px;
      font-weight: 700;
      color: #cbd5e1;
      background: rgba(255, 255, 255, 0.1);
      padding: 6px 14px;
      border-radius: 20px;
    }

    .lightbox-close-btn {
      background: rgba(255, 255, 255, 0.12);
      border: 1px solid rgba(255, 255, 255, 0.2);
      color: #ffffff;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      font-size: 20px;
      font-weight: 700;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s ease;
    }

    .lightbox-close-btn:hover {
      background: #ce0000;
      border-color: #ce0000;
    }

    .lightbox-stage {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      max-width: 1200px;
      width: 100%;
      margin: 8px auto;
      min-height: 0;
    }

    .lightbox-img-wrap {
      max-width: 100%;
      max-height: 75vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .lightbox-img-wrap img {
      max-width: 100%;
      max-height: 75vh;
      width: auto;
      height: auto;
      object-fit: contain;
      border-radius: 8px;
      box-shadow: 0 16px 48px rgba(0, 0, 0, 0.65);
      border: 1px solid rgba(255, 255, 255, 0.12);
    }

    .lightbox-nav-btn {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      background: rgba(15, 23, 42, 0.8);
      border: 1px solid rgba(255, 255, 255, 0.22);
      color: #ffffff;
      width: 46px;
      height: 46px;
      border-radius: 50%;
      font-size: 20px;
      font-weight: 900;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s ease;
      z-index: 10;
    }

    .lightbox-nav-btn:hover {
      background: #ce0000;
      border-color: #ce0000;
      transform: translateY(-50%) scale(1.06);
    }

    .lightbox-prev {
      left: 10px;
    }

    .lightbox-next {
      right: 10px;
    }

    .lightbox-footer {
      max-width: 720px;
      width: 100%;
      margin: 0 auto 6px auto;
      background: rgba(15, 23, 42, 0.9);
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 12px;
      padding: 14px 20px;
      text-align: center;
      color: #ffffff;
    }

    .lightbox-author-label {
      font-size: 10.5px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      color: #94a3b8;
      margin-bottom: 3px;
    }

    .lightbox-author-name {
      font-size: 18px;
      font-weight: 900;
      color: #ffffff;
      margin: 0;
    }
  </style>
</head>
<body>

  <!-- 1. ENCABEZADO OFICIAL DIARIO HOY -->
  <header class="site-header" role="banner">
    <div class="header-inner">
      <nav aria-label="Navegación principal">
        <a class="logo-link" href="https://www.hoy.es" target="_blank" rel="noopener" title="Diario HOY - Extremadura">
          <img src="https://s1.ppllstatics.com/starfish/latest/assets/images/logos/logo-hoy.svg"
               alt="Diario HOY"
               onerror="this.src='https://servicios.hoy.es/datos/imagen/logo-hoy.png'">
        </a>
      </nav>
    </div>
  </header>

  <!-- 2. CONTENEDOR PRINCIPAL DE LA GALERÍA -->
  <main class="site-wrapper" role="main">
    <article class="gallery-card">

      <!-- BARRA SUPERIOR DE NAVEGACIÓN -->
      <div class="gallery-topbar">
        <div style="display:flex; align-items:center; flex-wrap:wrap; gap:10px;">
          <a href="../" class="back-home-link" title="Volver a la portada del concurso">
            <span>←</span>
            <span>Volver a la portada</span>
          </a>
          <?php if ($estado === 'GANADORES_PUBLICADOS' || $estado === 'GALERIA_Y_GANADORES' || $config['ganadores']['premio_1'] !== ''): ?>
          <a href="../ganadores/" class="back-home-link" style="background: linear-gradient(135deg, #d97706 0%, #b45309 100%); border-color: #f59e0b;" title="Ver cuadro de honor y ganadores oficiales">
            <span>🏆</span>
            <span>Ver Ganadores Oficiales</span>
          </a>
          <?php endif; ?>
        </div>
        <span class="topbar-status-badge <?php echo htmlspecialchars($badgeClase, ENT_QUOTES, 'UTF-8'); ?>">
          <span class="pulse-dot"></span>
          <?php echo htmlspecialchars($badgeTexto, ENT_QUOTES, 'UTF-8'); ?>
        </span>
      </div>

      <!-- CABECERA EDITORIAL DE LA GALERÍA -->
      <header class="gallery-hero-header">
        <span class="tag-anniversary">📸 40º Aniversario Cáceres Patrimonio de la Humanidad</span>
        <h1 class="gallery-hero-title">Galería Oficial de Participantes</h1>
        <p class="gallery-hero-subtitle">
          Una ciudad patrimonio mirada a través del objetivo de sus ciudadanos. Explora todas las instantáneas capturadas durante el rally fotográfico <strong>«Cáceres en el Foco»</strong> en el casco antiguo monumental.
        </p>
        <div class="gallery-stats-row">
          <span class="stat-pill">📷 <strong><?php echo (int)$totalFotos; ?></strong> Fotografías en concurso</span>
          <span class="stat-pill">👤 <strong><?php echo (int)$totalAutores; ?></strong> Participantes</span>
          <span class="stat-pill">🏆 Fallo del jurado: <strong>1 de diciembre</strong></span>
        </div>
      </header>

      <!-- CUERPO PRINCIPAL -->
      <div class="gallery-body">

        <!-- CUENTA ATRÁS SI ESTÁ ACTIVA EN CONFIG.JSON -->
        <?php if ($config['mostrar_temporizador']): ?>
        <div class="countdown-wrapper" id="event-countdown" data-target-date="<?php echo htmlspecialchars($config['fecha_objetivo_temporizador'], ENT_QUOTES, 'UTF-8'); ?>" style="max-width: 560px; margin: 0 auto 24px auto;">
          <div class="countdown-title">
            <span>⏱️ <?php echo htmlspecialchars($config['rotulo_temporizador'], ENT_QUOTES, 'UTF-8'); ?></span>
          </div>
          <div class="countdown-digits-grid">
            <div class="countdown-item">
              <span class="digit-number" id="count-days">--</span>
              <span class="digit-unit">Días</span>
            </div>
            <span class="countdown-separator">:</span>
            <div class="countdown-item">
              <span class="digit-number" id="count-hours">--</span>
              <span class="digit-unit">Horas</span>
            </div>
            <span class="countdown-separator">:</span>
            <div class="countdown-item">
              <span class="digit-number" id="count-mins">--</span>
              <span class="digit-unit">Min</span>
            </div>
            <span class="countdown-separator">:</span>
            <div class="countdown-item">
              <span class="digit-number" id="count-secs">--</span>
              <span class="digit-unit">Seg</span>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <!-- BARRA DE BÚSQUEDA Y FILTRO POR PARTICIPANTE -->
        <section class="gallery-toolbar" aria-label="Herramientas de filtrado de fotografías">
          <div class="toolbar-controls">
            <div class="search-input-wrap">
              <span class="search-icon">🔍</span>
              <input type="text"
                     id="gallery-search"
                     class="gallery-search-input"
                     placeholder="Buscar participante por nombre..."
                     aria-label="Buscar participante por nombre">
              <button type="button" id="clear-search" class="clear-search-btn" title="Limpiar búsqueda" aria-label="Limpiar búsqueda">✕</button>
            </div>

            <select id="gallery-author-select" class="gallery-select" aria-label="Filtrar por participante">
              <option value="">Todos los participantes (<?php echo (int)$totalAutores; ?>)</option>
              <?php foreach ($listaAutores as $autorNombre): ?>
                <?php $numFotosAutor = isset($autoresUnicos[$autorNombre]) ? (int)$autoresUnicos[$autorNombre] : 1; ?>
                <option value="<?php echo htmlspecialchars($autorNombre, ENT_QUOTES, 'UTF-8'); ?>">
                  <?php echo htmlspecialchars($autorNombre, ENT_QUOTES, 'UTF-8'); ?> (<?php echo $numFotosAutor; ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="toolbar-results-count" id="gallery-count-label">
            Mostrando <strong><?php echo (int)$totalFotos; ?></strong> de <?php echo (int)$totalFotos; ?> fotos
          </div>
        </section>

        <!-- GRID DE FOTOGRAFÍAS CON EL NOMBRE DE LA PERSONA DEBAJO -->
        <section class="photo-grid" id="photo-grid" aria-label="Listado de fotografías participantes">
          <?php foreach ($fotos as $idx => $item): ?>
            <?php
              $autor = $item['nombre_completo'];
              // Iniciales para el avatar
              $partes = explode(' ', $autor);
              $iniciales = '';
              for ($p = 0; $p < count($partes); $p++) {
                  $parteLimpia = trim($partes[$p]);
                  if ($parteLimpia !== '') {
                      $iniciales .= function_exists('mb_substr') ? mb_strtoupper(mb_substr($parteLimpia, 0, 1, 'UTF-8'), 'UTF-8') : strtoupper(substr($parteLimpia, 0, 1));
                      if (strlen($iniciales) >= 2) {
                          break;
                      }
                  }
              }
              if ($iniciales === '') {
                  $iniciales = '📷';
              }
              $premioCardClass = '';
              $premioBadgeHtml = '';
              if ($item['id_foto'] !== '') {
                  if ($config['ganadores']['premio_1'] !== '' && $item['id_foto'] === $config['ganadores']['premio_1']) {
                      $premioCardClass = ' is-winner-1';
                      $premioBadgeHtml = '<span class="photo-winner-medal medal-gold">🥇 1er Premio</span>';
                  } elseif ($config['ganadores']['premio_2'] !== '' && $item['id_foto'] === $config['ganadores']['premio_2']) {
                      $premioCardClass = ' is-winner-2';
                      $premioBadgeHtml = '<span class="photo-winner-medal medal-silver">🥈 2º Premio</span>';
                  } elseif ($config['ganadores']['premio_3'] !== '' && $item['id_foto'] === $config['ganadores']['premio_3']) {
                      $premioCardClass = ' is-winner-3';
                      $premioBadgeHtml = '<span class="photo-winner-medal medal-bronze">🥉 3er Premio</span>';
                  }
              }
            ?>
            <figure class="photo-card<?php echo $premioCardClass; ?>"
                    data-index="<?php echo (int)$idx; ?>"
                    data-author="<?php echo htmlspecialchars($autor, ENT_QUOTES, 'UTF-8'); ?>"
                    data-src="<?php echo htmlspecialchars($item['src'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-id="<?php echo htmlspecialchars($item['id_foto'], ENT_QUOTES, 'UTF-8'); ?>"
                    tabindex="0"
                    role="button"
                    aria-label="Ver fotografía de <?php echo htmlspecialchars($autor, ENT_QUOTES, 'UTF-8'); ?>">
              <div class="photo-frame">
                <?php echo $premioBadgeHtml; ?>
                <img src="<?php echo htmlspecialchars($item['src'], ENT_QUOTES, 'UTF-8'); ?>"
                     alt="Fotografía de <?php echo htmlspecialchars($autor, ENT_QUOTES, 'UTF-8'); ?> - Concurso Cáceres en el Foco"
                     loading="lazy">
                <span class="photo-zoom-hint">🔍 Ampliar</span>
              </div>
              <figcaption class="photo-caption">
                <div class="author-info">
                  <span class="author-avatar" aria-hidden="true"><?php echo htmlspecialchars($iniciales, ENT_QUOTES, 'UTF-8'); ?></span>
                  <div class="author-text">
                    <span class="author-label">Fotografía de</span>
                    <span class="author-name"><?php echo htmlspecialchars($autor, ENT_QUOTES, 'UTF-8'); ?></span>
                  </div>
                </div>
              </figcaption>
            </figure>
          <?php endforeach; ?>
        </section>

        <!-- ESTADO SIN RESULTADOS -->
        <div class="empty-search-state" id="empty-search-state">
          <div style="font-size: 36px;">📷</div>
          <h3>No se encontraron fotografías para ese filtro</h3>
          <p>Prueba con otro nombre o restablece el filtro para ver todas las fotos del certamen.</p>
          <button type="button" class="reset-filter-btn" id="reset-filter-btn">Ver todas las fotografías</button>
        </div>

        <!-- BARRA SOCIAL PARA COMPARTIR LA GALERÍA -->
        <section class="share-section" aria-label="Compartir galería en redes sociales">
          <div class="share-title">¡Comparte la galería del concurso Cáceres en el Foco!</div>
          <div class="share-buttons-row">
            <a href="https://api.whatsapp.com/send?text=<?php echo urlencode($shareText . ' ' . $galleryUrl); ?>"
               target="_blank"
               rel="noopener"
               class="share-btn share-whatsapp"
               title="Compartir en WhatsApp">
              <svg viewBox="0 0 24 24"><path d="M12.031 2C6.504 2 2 6.494 2 12.016c0 1.956.564 3.774 1.537 5.32L2 22l4.825-1.503A9.97 9.97 0 0 0 12.031 22C17.556 22 22 17.538 22 12.016 22 6.494 17.556 2 12.031 2zm5.787 14.394c-.24.673-1.396 1.285-1.925 1.365-.515.078-1.184.11-3.418-.813-2.673-1.106-4.38-3.83-4.512-4.008-.133-.178-1.077-1.433-1.077-2.733 0-1.3.682-1.939.924-2.195.24-.256.527-.32.703-.32.176 0 .352.002.507.01.164.007.385-.062.602.46.223.535.76 1.854.827 1.99.066.136.11.295.02.473-.09.178-.135.29-.267.447-.133.156-.279.35-.398.47-.133.133-.272.278-.117.544.155.267.688 1.135 1.474 1.835 1.012.902 1.865 1.181 2.132 1.314.267.133.424.111.58-.067.157-.178.67-.78 848-1.047.178-.267.356-.223.596-.134.24.089 1.52.716 1.78.847.26.13.434.195.498.306.064.111.064.646-.176 1.319z"/></svg>
              <span>WhatsApp</span>
            </a>

            <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($shareText); ?>&url=<?php echo urlencode($galleryUrl); ?>&via=hoyextremadura"
               target="_blank"
               rel="noopener"
               class="share-btn share-x"
               title="Compartir en X">
              <svg viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
              <span>Compartir</span>
            </a>

            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($galleryUrl); ?>"
               target="_blank"
               rel="noopener"
               class="share-btn share-facebook"
               title="Compartir en Facebook">
              <svg viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
              <span>Facebook</span>
            </a>

            <button type="button" class="share-btn share-copy" id="btn-copy-link" title="Copiar enlace de la galería">
              <span>📋 Copiar Enlace</span>
            </button>
          </div>
        </section>

        <!-- PATROCINADORES Y COLABORADORES OFICIALES -->
        <section class="sponsors-wrapper" aria-label="Organización y colaboradores">
          <div class="sponsors-label">Organiza y Colaboran</div>
          <div class="sponsors-banner-wrap">
            <img src="../assets/img/patrocinadores.png"
                 alt="Organiza: Diario HOY. Colabora: Ayuntamiento de Cáceres, Capital Europea de la Cultura 2031, Consorcio Cáceres Ciudad Histórica"
                 width="2067"
                 height="267"
                 loading="lazy">
          </div>
        </section>

      </div>
    </article>
  </main>

  <!-- VISOR LIGHTBOX A PANTALLA COMPLETA -->
  <div class="lightbox-overlay" id="lightbox-modal" role="dialog" aria-modal="true" aria-label="Visor de fotografía a pantalla completa">
    <div class="lightbox-topbar">
      <span class="lightbox-counter" id="lightbox-counter">Foto 1 de <?php echo (int)$totalFotos; ?></span>
      <button type="button" class="lightbox-close-btn" id="lightbox-close" title="Cerrar visor (Esc)" aria-label="Cerrar visor">✕</button>
    </div>

    <div class="lightbox-stage">
      <button type="button" class="lightbox-nav-btn lightbox-prev" id="lightbox-prev" title="Fotografía anterior" aria-label="Fotografía anterior">❮</button>
      <div class="lightbox-img-wrap">
        <img src="" alt="" id="lightbox-image">
      </div>
      <button type="button" class="lightbox-nav-btn lightbox-next" id="lightbox-next" title="Fotografía siguiente" aria-label="Fotografía siguiente">❯</button>
    </div>

    <div class="lightbox-footer">
      <div class="lightbox-author-label">Fotografía realizada por</div>
      <h2 class="lightbox-author-name" id="lightbox-author">—</h2>
    </div>
  </div>

  <!-- NOTIFICACIÓN TOAST -->
  <div class="toast-notice" id="toast-notice" role="status" aria-live="polite">
    ✓ ¡Enlace de la galería copiado al portapapeles!
  </div>

  <!-- 3. PIE DE PÁGINA NATIVO DIARIO HOY -->
  <footer class="site-footer" role="contentinfo">
    <div class="footer-content">
      <div class="footer-links">
        <a href="https://www.hoy.es/contactar/" target="_blank" rel="noopener">Contactar</a>
        <span class="dot">&middot;</span>
        <a href="https://www.hoy.es/aviso-legal.html" target="_blank" rel="noopener">Aviso legal</a>
        <span class="dot">&middot;</span>
        <a href="https://www.hoy.es/condiciones-uso.html" target="_blank" rel="noopener">Condiciones de uso</a>
        <span class="dot">&middot;</span>
        <a href="https://www.hoy.es/politica-privacidad.html" target="_blank" rel="noopener">Política de privacidad</a>
        <span class="dot">&middot;</span>
        <a href="https://www.hoy.es/publicidad/" target="_blank" rel="noopener">Publicidad</a>
        <span class="dot">&middot;</span>
        <a href="https://www.hoy.es/mapa-web.html" target="_blank" rel="noopener">Mapa web</a>
        <span class="dot">&middot;</span>
        <a href="https://www.hoy.es/politica-cookies.html" target="_blank" rel="noopener">Política de cookies</a>
      </div>
    </div>
  </footer>

  <!-- SCRIPTS INTERACTIVOS -->
  <script src="../assets/js/main.js?v=<?php echo time(); ?>"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var searchInput = document.getElementById('gallery-search');
      var clearBtn = document.getElementById('clear-search');
      var authorSelect = document.getElementById('gallery-author-select');
      var countLabel = document.getElementById('gallery-count-label');
      var emptyState = document.getElementById('empty-search-state');
      var resetBtn = document.getElementById('reset-filter-btn');
      var cards = Array.prototype.slice.call(document.querySelectorAll('.photo-card'));
      var totalCards = cards.length;
      var visibleCards = cards.slice();

      function normalizeStr(str) {
        if (!str) return '';
        var s = str.toLowerCase();
        if (s.normalize) {
          s = s.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        }
        return s;
      }

      function filterGallery() {
        var q = normalizeStr(searchInput ? searchInput.value : '');
        var selectedAuthor = authorSelect ? authorSelect.value : '';

        if (clearBtn) {
          clearBtn.style.display = (searchInput && searchInput.value.length > 0) ? 'flex' : 'none';
        }

        visibleCards = [];
        for (var i = 0; i < cards.length; i++) {
          var card = cards[i];
          var author = card.getAttribute('data-author') || '';
          var matchSearch = (q === '' || normalizeStr(author).indexOf(q) !== -1);
          var matchSelect = (selectedAuthor === '' || author === selectedAuthor);

          if (matchSearch && matchSelect) {
            card.style.display = 'flex';
            visibleCards.push(card);
          } else {
            card.style.display = 'none';
          }
        }

        if (countLabel) {
          countLabel.innerHTML = 'Mostrando <strong>' + visibleCards.length + '</strong> de ' + totalCards + ' fotos';
        }

        if (emptyState) {
          emptyState.style.display = (visibleCards.length === 0) ? 'block' : 'none';
        }
      }

      if (searchInput) {
        searchInput.addEventListener('input', filterGallery);
      }

      if (clearBtn) {
        clearBtn.addEventListener('click', function () {
          searchInput.value = '';
          filterGallery();
          searchInput.focus();
        });
      }

      if (authorSelect) {
        authorSelect.addEventListener('change', filterGallery);
      }

      if (resetBtn) {
        resetBtn.addEventListener('click', function () {
          if (searchInput) searchInput.value = '';
          if (authorSelect) authorSelect.value = '';
          filterGallery();
        });
      }

      // Lightbox Modal
      var modal = document.getElementById('lightbox-modal');
      var lbImg = document.getElementById('lightbox-image');
      var lbAuthor = document.getElementById('lightbox-author');
      var lbCounter = document.getElementById('lightbox-counter');
      var lbClose = document.getElementById('lightbox-close');
      var lbPrev = document.getElementById('lightbox-prev');
      var lbNext = document.getElementById('lightbox-next');
      var currentVisibleIdx = 0;

      function openLightbox(idxInVisible) {
        if (!visibleCards.length) return;
        if (idxInVisible < 0) idxInVisible = visibleCards.length - 1;
        if (idxInVisible >= visibleCards.length) idxInVisible = 0;
        currentVisibleIdx = idxInVisible;

        var targetCard = visibleCards[currentVisibleIdx];
        var src = targetCard.getAttribute('data-src');
        var author = targetCard.getAttribute('data-author');

        lbImg.src = src;
        lbImg.alt = 'Fotografía de ' + author;
        lbAuthor.textContent = author;
        lbCounter.textContent = 'Foto ' + (currentVisibleIdx + 1) + ' de ' + visibleCards.length;

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
      }

      function closeLightbox() {
        modal.classList.remove('active');
        lbImg.src = '';
        document.body.style.overflow = '';
      }

      for (var c = 0; c < cards.length; c++) {
        (function (cardElement) {
          cardElement.addEventListener('click', function () {
            var pos = visibleCards.indexOf(cardElement);
            if (pos !== -1) {
              openLightbox(pos);
            }
          });
          cardElement.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
              e.preventDefault();
              var pos = visibleCards.indexOf(cardElement);
              if (pos !== -1) {
                openLightbox(pos);
              }
            }
          });
        })(cards[c]);
      }

      if (lbClose) lbClose.addEventListener('click', closeLightbox);
      if (lbPrev) {
        lbPrev.addEventListener('click', function (e) {
          e.stopPropagation();
          openLightbox(currentVisibleIdx - 1);
        });
      }
      if (lbNext) {
        lbNext.addEventListener('click', function (e) {
          e.stopPropagation();
          openLightbox(currentVisibleIdx + 1);
        });
      }

      if (modal) {
        modal.addEventListener('click', function (e) {
          if (e.target === modal || e.target.classList.contains('lightbox-stage')) {
            closeLightbox();
          }
        });
      }

      document.addEventListener('keydown', function (e) {
        if (!modal || !modal.classList.contains('active')) return;
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') openLightbox(currentVisibleIdx - 1);
        if (e.key === 'ArrowRight') openLightbox(currentVisibleIdx + 1);
      });
    });
  </script>
</body>
</html>
