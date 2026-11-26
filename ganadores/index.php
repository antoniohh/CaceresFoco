<?php
/**
 * ==============================================================================
 * CUADRO DE HONOR Y GANADORES OFICIALES - "CÁCERES EN EL FOCO"
 * 40º Aniversario de Cáceres como Patrimonio de la Humanidad
 *
 * @author    Antonio Horrillo <ahorrillo@hoy.es>
 * @copyright (c) 2026 Corporación de Medios de Extremadura, S.A. (Grupo Vocento)
 *            HOY Diario de Extremadura · Avda. del Diario Hoy, s/n, 06008 Badajoz
 *            CIF: A-78865441 · Todos los derechos reservados.
 * @link      https://www.hoy.es
 * @link      https://servicios.hoy.es/caceresenelfoco/ganadores/
 * @version   1.5.1 (Compatible estrictamente con PHP 5.3+)
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

$defaultNotaJurado = 'El jurado profesional nombrado por Diario HOY ha seleccionado las obras ganadoras valorando su fuerza visual, originalidad y capacidad para ensalzar la esencia y belleza monumental de Cáceres en su 40º Aniversario como Ciudad Patrimonio de la Humanidad.';

$config = array(
    'control_por_fechas' => false,
    'estado' => 'GANADORES_PUBLICADOS',
    'mostrar_temporizador' => false,
    'fecha_objetivo_temporizador' => '2026-12-02T10:00:00+01:00',
    'rotulo_temporizador' => 'Cuenta atrás para el anuncio oficial de ganadores (2 de diciembre)',
    'fases_fechas' => array(),
    'ganadores' => array(
        'premio_1' => '',
        'premio_2' => '',
        'premio_3' => '',
        'nota_jurado' => $defaultNotaJurado
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
                if (isset($g['nota_jurado']) && trim((string)$g['nota_jurado']) !== '') {
                    $config['ganadores']['nota_jurado'] = trim((string)$g['nota_jurado']);
                }
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
$winnersUrl = $baseUrl . 'ganadores/';

// 3. LECTURA DEL CSV DE PARTICIPANTES Y MAPEO POR ID_FOTO
$csvPath = $rootDir . '/galeria/participantes_galeria.csv';
if (!file_exists($csvPath)) {
    $csvPath = $rootDir . '/docs/participaciones/participantes_y_fotos_completo.csv';
}

$fotosPorId = array();
$totalFotosActivas = 0;

if (file_exists($csvPath)) {
    $fp = @fopen($csvPath, 'r');
    if ($fp !== false) {
        $headers = fgetcsv($fp, 0, ';');
        if (is_array($headers)) {
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
                $idxNombre = isset($colMap['nombre']) ? $colMap['nombre'] : 2;
                $idxApellidos = isset($colMap['apellidos']) ? $colMap['apellidos'] : 3;
                $idxNombreComp = isset($colMap['nombre_completo']) ? $colMap['nombre_completo'] : 4;
                $idxArchivo = isset($colMap['archivo_foto']) ? $colMap['archivo_foto'] : 10;
                $idxVisible = isset($colMap['visible_galeria']) ? $colMap['visible_galeria'] : 16;

                $idFoto = isset($row[$idxIdFoto]) ? trim($row[$idxIdFoto]) : '';
                $nombre = isset($row[$idxNombre]) ? trim($row[$idxNombre]) : '';
                $apellidos = isset($row[$idxApellidos]) ? trim($row[$idxApellidos]) : '';
                $nombreCompleto = isset($row[$idxNombreComp]) ? trim($row[$idxNombreComp]) : trim($nombre . ' ' . $apellidos);
                $archivoFoto = isset($row[$idxArchivo]) ? trim($row[$idxArchivo]) : '';
                $visible = isset($row[$idxVisible]) ? trim($row[$idxVisible]) : '1';

                if ($idFoto === '' || $archivoFoto === '') {
                    continue;
                }

                $rutaFisica = $rootDir . '/public/fotos/' . $archivoFoto;
                if (!file_exists($rutaFisica)) {
                    continue;
                }

                if ($visible !== '0') {
                    $totalFotosActivas++;
                }

                // Iniciales
                $partes = explode(' ', $nombreCompleto);
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
                    $iniciales = '🏆';
                }

                $fotosPorId[$idFoto] = array(
                    'id_foto' => $idFoto,
                    'nombre_completo' => $nombreCompleto,
                    'iniciales' => $iniciales,
                    'archivo' => $archivoFoto,
                    'src' => '../public/fotos/' . $archivoFoto
                );
            }
        }
        fclose($fp);
    }
}

$gCfg = $config['ganadores'];
$ganadoresPublicados = ($config['estado'] === 'GANADORES_PUBLICADOS' || $config['estado'] === 'GALERIA_Y_GANADORES');
$win1 = ($ganadoresPublicados && $gCfg['premio_1'] !== '' && isset($fotosPorId[$gCfg['premio_1']])) ? $fotosPorId[$gCfg['premio_1']] : null;
$win2 = ($ganadoresPublicados && $gCfg['premio_2'] !== '' && isset($fotosPorId[$gCfg['premio_2']])) ? $fotosPorId[$gCfg['premio_2']] : null;
$win3 = ($ganadoresPublicados && $gCfg['premio_3'] !== '' && isset($fotosPorId[$gCfg['premio_3']])) ? $fotosPorId[$gCfg['premio_3']] : null;

$hayGanadores = ($win1 !== null || $win2 !== null || $win3 !== null);

$siteTitle = 'Ganadores Oficiales · Concurso Fotográfico Cáceres en el Foco | Diario HOY';
$siteDesc = 'Descubre las fotografías ganadoras y el fallo oficial del jurado del concurso fotográfico Cáceres en el Foco por el 40º Aniversario de Cáceres como Patrimonio de la Humanidad.';
$ogImage = $baseUrl . 'assets/img/concurso_fotos_caceres.jpg';
$shareText = '¡Ya puedes conocer las fotografías ganadoras del concurso «Cáceres en el Foco» de Diario HOY!';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">

  <title><?php echo htmlspecialchars($siteTitle, ENT_QUOTES, 'UTF-8'); ?></title>

  <meta name="description" content="<?php echo htmlspecialchars($siteDesc, ENT_QUOTES, 'UTF-8'); ?>">
  <meta name="author" content="Diario HOY">
  <meta name="robots" content="index, follow, max-image-preview:large">
  <link rel="canonical" href="<?php echo htmlspecialchars($winnersUrl, ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="shortcut icon" href="https://static.vocstatic.com/voc26/latest/assets/images/favicon/hoy/favicon.ico" type="image/x-icon" />
  <link rel="icon" href="https://static.vocstatic.com/voc26/latest/assets/images/favicon/hoy/favicon.ico" type="image/x-icon" />
  <meta name="theme-color" content="#ce0000">

  <!-- OPEN GRAPH -->
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="Diario HOY">
  <meta property="og:locale" content="es_ES">
  <meta property="og:url" content="<?php echo htmlspecialchars($winnersUrl, ENT_QUOTES, 'UTF-8'); ?>">
  <meta property="og:title" content="<?php echo htmlspecialchars($siteTitle, ENT_QUOTES, 'UTF-8'); ?>">
  <meta property="og:description" content="<?php echo htmlspecialchars($siteDesc, ENT_QUOTES, 'UTF-8'); ?>">
  <meta property="og:image" content="<?php echo htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8'); ?>">

  <!-- TWITTER CARDS -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:site" content="@hoyextremadura">
  <meta name="twitter:title" content="<?php echo htmlspecialchars($siteTitle, ENT_QUOTES, 'UTF-8'); ?>">
  <meta name="twitter:description" content="<?php echo htmlspecialchars($siteDesc, ENT_QUOTES, 'UTF-8'); ?>">
  <meta name="twitter:image" content="<?php echo htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8'); ?>">

  <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">

  <style>
    .winners-card {
      max-width: 1080px;
      width: 100%;
      margin: 0 auto;
      background: #ffffff;
      border-radius: 14px;
      box-shadow:
        0 1px 3px rgba(0, 0, 0, 0.08),
        0 10px 28px -4px rgba(15, 23, 42, 0.2),
        0 28px 56px -8px rgba(15, 23, 42, 0.28);
      overflow: hidden;
      border: 1px solid rgba(226, 232, 240, 0.85);
      animation: cardFadeIn 0.45s ease-out;
    }

    .winners-topbar {
      background: #0f172a;
      color: #f8fafc;
      padding: 12px 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 10px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .topbar-nav-group {
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      gap: 10px;
    }

    .nav-pill-link {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      color: #ffffff;
      text-decoration: none;
      font-size: 12.5px;
      font-weight: 700;
      background: rgba(255, 255, 255, 0.1);
      padding: 7px 14px;
      border-radius: 20px;
      border: 1px solid rgba(255, 255, 255, 0.18);
      transition: all 0.2s ease;
    }

    .nav-pill-link:hover {
      background: #ce0000;
      border-color: #ce0000;
      transform: translateY(-1px);
    }

    .nav-pill-link.pill-gallery {
      background: rgba(14, 116, 144, 0.35);
      border-color: rgba(56, 189, 248, 0.4);
    }

    .nav-pill-link.pill-gallery:hover {
      background: #0284c7;
      border-color: #38bdf8;
    }

    .topbar-gold-badge {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      font-size: 11.5px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.6px;
      padding: 5px 14px;
      border-radius: 20px;
      background: linear-gradient(135deg, #b45309 0%, #d97706 100%);
      color: #ffffff;
      box-shadow: 0 2px 10px rgba(217, 119, 6, 0.35);
    }

    /* Cabecera de gala */
    .winners-hero {
      background: linear-gradient(135deg, #090d16 0%, #0f172a 45%, #1e1b4b 100%);
      color: #ffffff;
      padding: 38px 28px 34px 28px;
      text-align: center;
      position: relative;
      overflow: hidden;
      border-bottom: 3px solid #f59e0b;
    }

    .winners-hero::before {
      content: "";
      position: absolute;
      top: -80px;
      left: 50%;
      transform: translateX(-50%);
      width: 480px;
      height: 240px;
      background: radial-gradient(circle, rgba(251, 191, 36, 0.22) 0%, rgba(251, 191, 36, 0) 70%);
      pointer-events: none;
    }

    .winners-hero-title {
      font-size: 30px;
      font-weight: 900;
      margin: 8px 0 12px 0;
      letter-spacing: -0.5px;
      color: #ffffff;
      line-height: 1.2;
    }

    .winners-hero-subtitle {
      font-size: 14.5px;
      color: #cbd5e1;
      max-width: 720px;
      margin: 0 auto;
      line-height: 1.6;
    }

    .winners-body {
      padding: 32px 28px 36px 28px;
    }

    @media (max-width: 767px) {
      .winners-body {
        padding: 20px 14px 26px 14px;
      }
      .winners-hero {
        padding: 26px 16px 24px 16px;
      }
      .winners-hero-title {
        font-size: 23px;
      }
      .winners-topbar {
        padding: 10px 14px;
      }
    }

    /* 1ER PREMIO: TARJETA GRAN GANADOR (ORO) */
    .podium-first-wrap {
      margin-bottom: 28px;
    }

    .podium-card {
      background: #ffffff;
      border-radius: 14px;
      overflow: hidden;
      position: relative;
      display: flex;
      flex-direction: column;
      transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .podium-card.podium-gold {
      border: 2px solid #f59e0b;
      box-shadow: 0 12px 32px -4px rgba(245, 158, 11, 0.22), 0 4px 12px rgba(15, 23, 42, 0.08);
    }

    .podium-card.podium-silver {
      border: 2px solid #94a3b8;
      box-shadow: 0 10px 24px -4px rgba(100, 116, 139, 0.18);
    }

    .podium-card.podium-bronze {
      border: 2px solid #d97706;
      box-shadow: 0 10px 24px -4px rgba(180, 83, 9, 0.18);
    }

    .podium-ribbon {
      padding: 12px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 10px;
      font-weight: 900;
      text-transform: uppercase;
      letter-spacing: 0.6px;
      font-size: 13px;
    }

    .podium-gold .podium-ribbon {
      background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
      color: #78350f;
      border-bottom: 1px solid #f59e0b;
    }

    .podium-silver .podium-ribbon {
      background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
      color: #1e293b;
      border-bottom: 1px solid #cbd5e1;
    }

    .podium-bronze .podium-ribbon {
      background: linear-gradient(135deg, #ffedd5 0%, #fed7aa 100%);
      color: #7c2d12;
      border-bottom: 1px solid #fdba74;
    }

    .ribbon-medal-title {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      font-size: 14px;
    }

    .ribbon-prize-tag {
      background: rgba(15, 23, 42, 0.88);
      color: #ffffff;
      font-size: 11px;
      font-weight: 800;
      padding: 4px 12px;
      border-radius: 20px;
      letter-spacing: 0.4px;
    }

    .podium-photo-stage {
      position: relative;
      width: 100%;
      background: #090d16;
      overflow: hidden;
      cursor: pointer;
    }

    .podium-gold .podium-photo-stage {
      max-height: 560px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .podium-gold .podium-photo-stage img {
      width: 100%;
      max-height: 560px;
      object-fit: contain;
      display: block;
      transition: transform 0.4s ease;
    }

    .podium-secondary-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 24px;
      margin-bottom: 32px;
    }

    @media (max-width: 820px) {
      .podium-secondary-grid {
        grid-template-columns: 1fr;
      }
    }

    .podium-silver .podium-photo-stage,
    .podium-bronze .podium-photo-stage {
      padding-top: 72%;
    }

    .podium-silver .podium-photo-stage img,
    .podium-bronze .podium-photo-stage img {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      transition: transform 0.4s ease;
    }

    .podium-card:hover .podium-photo-stage img {
      transform: scale(1.03);
    }

    .podium-zoom-badge {
      position: absolute;
      bottom: 14px;
      right: 14px;
      background: rgba(15, 23, 42, 0.85);
      backdrop-filter: blur(4px);
      -webkit-backdrop-filter: blur(4px);
      color: #ffffff;
      font-size: 12px;
      font-weight: 700;
      padding: 6px 13px;
      border-radius: 20px;
      border: 1px solid rgba(255, 255, 255, 0.2);
      display: inline-flex;
      align-items: center;
      gap: 6px;
      pointer-events: none;
    }

    /* Pie con el Nombre del Ganador y su Premio */
    .podium-caption {
      padding: 18px 22px;
      background: #ffffff;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 16px;
    }

    .winner-person-box {
      display: flex;
      align-items: center;
      gap: 13px;
    }

    .winner-avatar {
      width: 44px;
      height: 44px;
      border-radius: 50%;
      font-size: 16px;
      font-weight: 900;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      color: #ffffff;
    }

    .podium-gold .winner-avatar {
      background: linear-gradient(135deg, #f59e0b 0%, #b45309 100%);
      box-shadow: 0 4px 12px rgba(245, 158, 11, 0.35);
    }

    .podium-silver .winner-avatar {
      background: linear-gradient(135deg, #64748b 0%, #334155 100%);
      box-shadow: 0 4px 12px rgba(100, 116, 139, 0.3);
    }

    .podium-bronze .winner-avatar {
      background: linear-gradient(135deg, #d97706 0%, #9a3412 100%);
      box-shadow: 0 4px 12px rgba(217, 119, 6, 0.3);
    }

    .winner-meta-label {
      font-size: 11px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.6px;
      color: #64748b;
      display: block;
      margin-bottom: 2px;
    }

    .winner-full-name {
      font-size: 20px;
      font-weight: 900;
      color: #0f172a;
      margin: 0;
      line-height: 1.2;
    }

    .podium-gold .winner-full-name {
      font-size: 22px;
    }

    .winner-prize-summary {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      padding: 10px 14px;
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 13px;
      color: #1e293b;
      font-weight: 700;
    }

    /* Suplentes oficiales */
    .substitutes-section {
      margin-top: 10px;
      margin-bottom: 32px;
      padding-top: 24px;
      border-top: 1px solid #e2e8f0;
    }

    .substitutes-title {
      font-size: 15px;
      font-weight: 800;
      color: #334155;
      text-transform: uppercase;
      letter-spacing: 0.6px;
      margin: 0 0 16px 0;
      text-align: center;
    }

    .substitutes-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 18px;
    }

    @media (max-width: 767px) {
      .substitutes-grid {
        grid-template-columns: 1fr;
      }
    }

    /* Bloque de deliberación cuando aún no hay ganadores asignados */
    .deliberation-box {
      background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
      border: 2px dashed #cbd5e1;
      border-radius: 14px;
      padding: 36px 24px;
      text-align: center;
      margin-bottom: 28px;
    }

    .deliberation-box h2 {
      font-size: 22px;
      font-weight: 900;
      color: #0f172a;
      margin: 10px 0 10px 0;
    }

    .deliberation-box p {
      font-size: 14.5px;
      color: #475569;
      max-width: 620px;
      margin: 0 auto 22px auto;
      line-height: 1.6;
    }

    .cta-gallery-banner {
      background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
      border-radius: 12px;
      padding: 22px 24px;
      color: #ffffff;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 16px;
      margin-bottom: 26px;
    }

    .cta-gallery-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: #ce0000;
      color: #ffffff !important;
      text-decoration: none;
      font-size: 14px;
      font-weight: 800;
      padding: 12px 22px;
      border-radius: 8px;
      box-shadow: 0 4px 14px rgba(206, 0, 0, 0.35);
      transition: all 0.2s ease;
    }

    .cta-gallery-btn:hover {
      background: #b80000;
      transform: translateY(-2px);
    }

    /* Lightbox Modal */
    .lightbox-overlay {
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(8, 11, 18, 0.94);
      z-index: 9999;
      display: none;
      flex-direction: column;
      justify-content: space-between;
      padding: 16px;
    }

    .lightbox-overlay.active {
      display: flex;
    }

    .lightbox-topbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      color: #f8fafc;
      max-width: 1200px;
      width: 100%;
      margin: 0 auto;
    }

    .lightbox-badge {
      font-size: 13px;
      font-weight: 800;
      color: #fef3c7;
      background: rgba(217, 119, 6, 0.3);
      border: 1px solid rgba(245, 158, 11, 0.5);
      padding: 6px 14px;
      border-radius: 20px;
    }

    .lightbox-close-btn {
      background: rgba(255, 255, 255, 0.14);
      border: 1px solid rgba(255, 255, 255, 0.2);
      color: #ffffff;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      font-size: 20px;
      font-weight: 700;
      cursor: pointer;
    }

    .lightbox-close-btn:hover {
      background: #ce0000;
    }

    .lightbox-stage {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      max-width: 1200px;
      width: 100%;
      margin: 8px auto;
      min-height: 0;
    }

    .lightbox-stage img {
      max-width: 100%;
      max-height: 76vh;
      object-fit: contain;
      border-radius: 8px;
      box-shadow: 0 16px 48px rgba(0, 0, 0, 0.7);
    }

    .lightbox-footer {
      max-width: 700px;
      width: 100%;
      margin: 0 auto 6px auto;
      background: rgba(15, 23, 42, 0.92);
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 12px;
      padding: 14px 20px;
      text-align: center;
      color: #ffffff;
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

  <!-- 2. CONTENEDOR PRINCIPAL -->
  <main class="site-wrapper" role="main">
    <article class="winners-card">

      <!-- BARRA SUPERIOR DE NAVEGACIÓN -->
      <div class="winners-topbar">
        <div class="topbar-nav-group">
          <a href="../" class="nav-pill-link" title="Volver a la portada del concurso">
            <span>←</span>
            <span>Portada del Concurso</span>
          </a>
          <a href="../galeria/" class="nav-pill-link pill-gallery" title="Ver las <?php echo (int)$totalFotosActivas; ?> fotografías participantes">
            <span>📷</span>
            <span>Ver Galería Completa (<?php echo (int)$totalFotosActivas; ?> fotos)</span>
          </a>
        </div>
        <span class="topbar-gold-badge">
          <span>🏆</span>
          <span><?php echo $hayGanadores ? 'Fallo Oficial del Jurado' : 'Anuncio: 2 de Diciembre'; ?></span>
        </span>
      </div>

      <!-- CABECERA DE GALA -->
      <header class="winners-hero">
        <span class="tag-anniversary">🏆 40º Aniversario Cáceres Patrimonio de la Humanidad</span>
        <h1 class="winners-hero-title">Cuadro de Honor y Ganadores Oficiales</h1>
        <p class="winners-hero-subtitle">
          <?php echo htmlspecialchars($gCfg['nota_jurado'], ENT_QUOTES, 'UTF-8'); ?>
        </p>
      </header>

      <!-- CUERPO DE GANADORES -->
      <div class="winners-body">

        <?php if ($hayGanadores): ?>

          <!-- =============================================================== -->
          <!-- 🥇 1ER PREMIO: SAMSUNG GALAXY S25 FE                            -->
          <!-- =============================================================== -->
          <?php if ($win1 !== null): ?>
          <section class="podium-first-wrap" aria-label="Primer Premio">
            <div class="podium-card podium-gold">
              <div class="podium-ribbon">
                <span class="ribbon-medal-title">🥇 PRIMER PREMIO · FOTOGRAFÍA GANADORA</span>
                <span class="ribbon-prize-tag">📱 Premio: Samsung Galaxy S25 FE</span>
              </div>

              <div class="podium-photo-stage js-lightbox-trigger"
                   data-src="<?php echo htmlspecialchars($win1['src'], ENT_QUOTES, 'UTF-8'); ?>"
                   data-author="<?php echo htmlspecialchars($win1['nombre_completo'], ENT_QUOTES, 'UTF-8'); ?>"
                   data-prize="🥇 1er Premio · Samsung Galaxy S25 FE">
                <img src="<?php echo htmlspecialchars($win1['src'], ENT_QUOTES, 'UTF-8'); ?>"
                     alt="1er Premio - Fotografía de <?php echo htmlspecialchars($win1['nombre_completo'], ENT_QUOTES, 'UTF-8'); ?>">
                <span class="podium-zoom-badge">🔍 Ver a pantalla completa</span>
              </div>

              <div class="podium-caption">
                <div class="winner-person-box">
                  <span class="winner-avatar"><?php echo htmlspecialchars($win1['iniciales'], ENT_QUOTES, 'UTF-8'); ?></span>
                  <div>
                    <span class="winner-meta-label">Autor/a de la fotografía ganadora</span>
                    <h2 class="winner-full-name"><?php echo htmlspecialchars($win1['nombre_completo'], ENT_QUOTES, 'UTF-8'); ?></h2>
                  </div>
                </div>
                <div class="winner-prize-summary">
                  <span>🏆</span>
                  <span>Ganador/a de un <strong>Samsung Galaxy S25 FE</strong></span>
                </div>
              </div>
            </div>
          </section>
          <?php endif; ?>

          <!-- =============================================================== -->
          <!-- 🥈 2º PREMIO Y 🥉 3ER PREMIO                                    -->
          <!-- =============================================================== -->
          <section class="podium-secondary-grid" aria-label="Segundo y Tercer Premio">

            <!-- 🥈 2º PREMIO -->
            <?php if ($win2 !== null): ?>
            <div class="podium-card podium-silver">
              <div class="podium-ribbon">
                <span class="ribbon-medal-title">🥈 SEGUNDO PREMIO</span>
                <span class="ribbon-prize-tag">🎁 Tarjeta Amazon 200€</span>
              </div>

              <div class="podium-photo-stage js-lightbox-trigger"
                   data-src="<?php echo htmlspecialchars($win2['src'], ENT_QUOTES, 'UTF-8'); ?>"
                   data-author="<?php echo htmlspecialchars($win2['nombre_completo'], ENT_QUOTES, 'UTF-8'); ?>"
                   data-prize="🥈 2º Premio · Tarjeta Regalo Amazon 200€">
                <img src="<?php echo htmlspecialchars($win2['src'], ENT_QUOTES, 'UTF-8'); ?>"
                     alt="2º Premio - Fotografía de <?php echo htmlspecialchars($win2['nombre_completo'], ENT_QUOTES, 'UTF-8'); ?>">
                <span class="podium-zoom-badge">🔍 Ampliar</span>
              </div>

              <div class="podium-caption">
                <div class="winner-person-box">
                  <span class="winner-avatar"><?php echo htmlspecialchars($win2['iniciales'], ENT_QUOTES, 'UTF-8'); ?></span>
                  <div>
                    <span class="winner-meta-label">2º Premio del Certamen</span>
                    <h2 class="winner-full-name"><?php echo htmlspecialchars($win2['nombre_completo'], ENT_QUOTES, 'UTF-8'); ?></h2>
                  </div>
                </div>
              </div>
            </div>
            <?php endif; ?>

            <!-- 🥉 3ER PREMIO -->
            <?php if ($win3 !== null): ?>
            <div class="podium-card podium-bronze">
              <div class="podium-ribbon">
                <span class="ribbon-medal-title">🥉 TERCER PREMIO</span>
                <span class="ribbon-prize-tag">🍽️ Experiencia Atrio</span>
              </div>

              <div class="podium-photo-stage js-lightbox-trigger"
                   data-src="<?php echo htmlspecialchars($win3['src'], ENT_QUOTES, 'UTF-8'); ?>"
                   data-author="<?php echo htmlspecialchars($win3['nombre_completo'], ENT_QUOTES, 'UTF-8'); ?>"
                   data-prize="🥉 3er Premio · Bono Desayuno + Visita Bodega en Atrio">
                <img src="<?php echo htmlspecialchars($win3['src'], ENT_QUOTES, 'UTF-8'); ?>"
                     alt="3er Premio - Fotografía de <?php echo htmlspecialchars($win3['nombre_completo'], ENT_QUOTES, 'UTF-8'); ?>">
                <span class="podium-zoom-badge">🔍 Ampliar</span>
              </div>

              <div class="podium-caption">
                <div class="winner-person-box">
                  <span class="winner-avatar"><?php echo htmlspecialchars($win3['iniciales'], ENT_QUOTES, 'UTF-8'); ?></span>
                  <div>
                    <span class="winner-meta-label">3er Premio del Certamen</span>
                    <h2 class="winner-full-name"><?php echo htmlspecialchars($win3['nombre_completo'], ENT_QUOTES, 'UTF-8'); ?></h2>
                  </div>
                </div>
              </div>
            </div>
            <?php endif; ?>

          </section>

        <?php else: ?>

          <!-- =============================================================== -->
          <!-- ESTADO PREVIO: DELIBERACIÓN DEL JURADO EN CURSO                 -->
          <!-- =============================================================== -->
          <div class="deliberation-box">
            <div style="font-size: 44px; line-height: 1;">⚖️ 🏆</div>
            <h2>El Jurado Profesional se encuentra en Fase de Deliberación</h2>
            <p>
              El plazo de recepción de fotografías concluyó el sábado 21 de noviembre con <strong><?php echo (int)$totalFotosActivas; ?> instantáneas a concurso</strong>. El fallo oficial con los 3 ganadores se publicará en esta página a partir del <strong>2 de diciembre de 2026</strong>.
            </p>

            <!-- CUENTA ATRÁS HACIA EL FALLO DEL 2 DE DICIEMBRE -->
            <div class="countdown-wrapper" id="event-countdown" data-target-date="2026-12-02T10:00:00+01:00" style="max-width: 520px; margin: 0 auto 22px auto;">
              <div class="countdown-title">
                <span>⏱️ Cuenta atrás para el anuncio oficial de ganadores (2 de diciembre)</span>
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

            <a href="../galeria/" class="cta-gallery-btn">
              <span>📷</span>
              <span>Explorar las <?php echo (int)$totalFotosActivas; ?> Fotografías Participantes</span>
            </a>
          </div>

        <?php endif; ?>

        <!-- BANNER DE ACCESO A LA GALERÍA COMPLETA -->
        <div class="cta-gallery-banner">
          <div>
            <strong style="display:block; font-size:16px; margin-bottom:4px;">📷 ¿Quieres ver todas las fotografías presentadas al rally?</strong>
            <span style="font-size:13px; color:#cbd5e1;">Explora la galería completa con las <?php echo (int)$totalFotosActivas; ?> obras de los participantes.</span>
          </div>
          <a href="../galeria/" class="cta-gallery-btn">
            <span>Ver Galería Completa ➔</span>
          </a>
        </div>

        <!-- BARRA SOCIAL -->
        <section class="share-section" aria-label="Compartir ganadores en redes sociales">
          <div class="share-title">¡Comparte el cuadro de honor de Cáceres en el Foco!</div>
          <div class="share-buttons-row">
            <a href="https://api.whatsapp.com/send?text=<?php echo urlencode($shareText . ' ' . $winnersUrl); ?>"
               target="_blank"
               rel="noopener"
               class="share-btn share-whatsapp">
              <svg viewBox="0 0 24 24"><path d="M12.031 2C6.504 2 2 6.494 2 12.016c0 1.956.564 3.774 1.537 5.32L2 22l4.825-1.503A9.97 9.97 0 0 0 12.031 22C17.556 22 22 17.538 22 12.016 22 6.494 17.556 2 12.031 2zm5.787 14.394c-.24.673-1.396 1.285-1.925 1.365-.515.078-1.184.11-3.418-.813-2.673-1.106-4.38-3.83-4.512-4.008-.133-.178-1.077-1.433-1.077-2.733 0-1.3.682-1.939.924-2.195.24-.256.527-.32.703-.32.176 0 .352.002.507.01.164.007.385-.062.602.46.223.535.76 1.854.827 1.99.066.136.11.295.02.473-.09.178-.135.29-.267.447-.133.156-.279.35-.398.47-.133.133-.272.278-.117.544.155.267.688 1.135 1.474 1.835 1.012.902 1.865 1.181 2.132 1.314.267.133.424.111.58-.067.157-.178.67-.78 848-1.047.178-.267.356-.223.596-.134.24.089 1.52.716 1.78.847.26.13.434.195.498.306.064.111.064.646-.176 1.319z"/></svg>
              <span>WhatsApp</span>
            </a>

            <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($shareText); ?>&url=<?php echo urlencode($winnersUrl); ?>&via=hoyextremadura"
               target="_blank"
               rel="noopener"
               class="share-btn share-x">
              <svg viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
              <span>Compartir</span>
            </a>

            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($winnersUrl); ?>"
               target="_blank"
               rel="noopener"
               class="share-btn share-facebook">
              <svg viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
              <span>Facebook</span>
            </a>

            <button type="button" class="share-btn share-copy" id="btn-copy-link">
              <span>📋 Copiar Enlace</span>
            </button>
          </div>
        </section>

        <!-- PATROCINADORES -->
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
  <div class="lightbox-overlay" id="lightbox-modal" role="dialog" aria-modal="true" aria-label="Visor de fotografía ganadora">
    <div class="lightbox-topbar">
      <span class="lightbox-badge" id="lightbox-prize">—</span>
      <button type="button" class="lightbox-close-btn" id="lightbox-close" title="Cerrar visor (Esc)">✕</button>
    </div>

    <div class="lightbox-stage">
      <img src="" alt="" id="lightbox-image">
    </div>

    <div class="lightbox-footer">
      <div style="font-size:11px; text-transform:uppercase; letter-spacing:0.8px; color:#94a3b8; margin-bottom:3px;">Fotografía realizada por</div>
      <h2 style="font-size:19px; font-weight:900; margin:0; color:#ffffff;" id="lightbox-author">—</h2>
    </div>
  </div>

  <!-- NOTIFICACIÓN TOAST -->
  <div class="toast-notice" id="toast-notice" role="status" aria-live="polite">
    ✓ ¡Enlace copiado al portapapeles!
  </div>

  <!-- PIE DE PÁGINA NATIVO DIARIO HOY -->
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

  <script src="../assets/js/main.js?v=<?php echo time(); ?>"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var modal = document.getElementById('lightbox-modal');
      var lbImg = document.getElementById('lightbox-image');
      var lbAuthor = document.getElementById('lightbox-author');
      var lbPrize = document.getElementById('lightbox-prize');
      var lbClose = document.getElementById('lightbox-close');
      var triggers = document.querySelectorAll('.js-lightbox-trigger');

      for (var i = 0; i < triggers.length; i++) {
        (function (el) {
          el.addEventListener('click', function () {
            var src = el.getAttribute('data-src');
            var author = el.getAttribute('data-author');
            var prize = el.getAttribute('data-prize');
            lbImg.src = src;
            lbImg.alt = 'Fotografía de ' + author;
            lbAuthor.textContent = author;
            lbPrize.textContent = prize;
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
          });
        })(triggers[i]);
      }

      function closeLb() {
        modal.classList.remove('active');
        lbImg.src = '';
        document.body.style.overflow = '';
      }

      if (lbClose) lbClose.addEventListener('click', closeLb);
      if (modal) {
        modal.addEventListener('click', function (e) {
          if (e.target === modal || e.target.classList.contains('lightbox-stage')) {
            closeLb();
          }
        });
      }
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal && modal.classList.contains('active')) {
          closeLb();
        }
      });
    });
  </script>
</body>
</html>
