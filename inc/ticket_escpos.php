<?php
session_start();
require_once __DIR__ . '/permisos.php';
guardia_endpoint_auto();
if (empty($_SESSION['ingress'])) { http_response_code(403); exit; }

header('Content-Type: application/json');
ob_start();
ini_set('display_errors','0');

require_once __DIR__ . '/empresa.php';
$data     = json_decode(file_get_contents('php://input'), true);
$items    = $data['items']    ?? [];
$grand    = floatval($data['grand']    ?? 0);
$vendedor = $data['vendedor'] ?? '';
$fecha    = $data['fecha']    ?? '';
$cliente  = strtoupper($data['cliente']  ?? '');

// ── Número a letras ────────────────────────────────────────
require_once __DIR__ . '/vendor/autoload.php';
use Luecano\NumeroALetras\NumeroALetras;
$fmt    = new NumeroALetras();
$letras = $fmt->toInvoice($grand, 2) . ' SOLES';

// ── Comandos ESC/POS ───────────────────────────────────────
$ESC = "\x1B";
$GS  = "\x1D";
$LF  = "\x0A";

$init      = $ESC . "@";           // Inicializar
$cut       = $GS  . "V\x42\x03";  // Cortar papel (avance + corte parcial)
$center    = $ESC . "a\x01";
$left      = $ESC . "a\x00";
$bold_on   = $ESC . "E\x01";
$bold_off  = $ESC . "E\x00";
$dbl_on    = $GS  . "!\x11";      // Doble ancho y alto
$dbl_off   = $GS  . "!\x00";
$wide_on   = $GS  . "!\x10";      // Solo doble ancho
$wide_off  = $GS  . "!\x00";

// Separador de 32 caracteres (ancho típico 80mm)
$sep = str_repeat('-', 32) . $LF;

$out  = $init;
$out .= $center;

// Nombre del negocio grande y datos de la empresa
$lineas_emp = empresa_lineas_ticket();
$out .= $dbl_on . $bold_on;
$out .= iconv('UTF-8','CP850//TRANSLIT', mb_substr(array_shift($lineas_emp), 0, 16)) . $LF;
$out .= $bold_off . $dbl_off;
$out .= $ESC . "a\x01";
foreach ($lineas_emp as $le) {
    foreach (str_split(iconv('UTF-8','CP850//TRANSLIT',$le), 32) as $trozo) { $out .= $trozo . $LF; }
}
if (empresa('empresa_lema') !== '') { $out .= iconv('UTF-8','CP850//TRANSLIT', empresa('empresa_lema')) . $LF; }

$out .= $LF;
$out .= $bold_on . iconv('UTF-8','CP850//TRANSLIT','NOTA VENTA') . $bold_off . $LF;
$out .= $LF;

// Fecha y cliente
$out .= $left;
$out .= iconv('UTF-8','CP850//TRANSLIT','FECHA: ' . $fecha) . $LF;
if ($cliente) {
    $out .= iconv('UTF-8','CP850//TRANSLIT','CLIENTE: ' . $cliente) . $LF;
}

$out .= $sep;

// Cabecera tabla
$out .= $bold_on;
$out .= str_pad(iconv('UTF-8','CP850//TRANSLIT','DESCRIPCION'), 24) . str_pad('TOTAL', 8, ' ', STR_PAD_LEFT) . $LF;
$out .= $bold_off;
$out .= $sep;

// Items
foreach ($items as $ci) {
    $name  = iconv('UTF-8','CP850//TRANSLIT', mb_substr($ci['name'] ?? '', 0, 30));
    $total = 'S/' . number_format(floatval($ci['total'] ?? 0), 2, '.', ',');
    // Nombre en primera línea si es largo, total a la derecha
    if (mb_strlen($name) <= 24) {
        $out .= str_pad($name, 24) . str_pad($total, 8, ' ', STR_PAD_LEFT) . $LF;
    } else {
        $out .= mb_substr($name, 0, 32) . $LF;
        $out .= str_repeat(' ', 24) . str_pad($total, 8, ' ', STR_PAD_LEFT) . $LF;
    }
}

$out .= $sep;

// Total
$total_str = 'S/' . number_format($grand, 2, '.', ',');
$out .= $bold_on . $dbl_on;
$out .= $center;
$out .= iconv('UTF-8','CP850//TRANSLIT','TOTAL: ' . $total_str) . $LF;
$out .= $dbl_off . $bold_off;
$out .= $left;

$out .= $sep;
$out .= iconv('UTF-8','CP850//TRANSLIT','IMPORTE EN LETRAS:') . $LF;
$out .= iconv('UTF-8','CP850//TRANSLIT',mb_substr($letras, 0, 32)) . $LF;
$out .= iconv('UTF-8','CP850//TRANSLIT','VENDEDOR: ' . $vendedor) . $LF;
$out .= iconv('UTF-8','CP850//TRANSLIT','PERSONAL ENTREGA: ____________') . $LF;
$out .= $sep;

// Pie
$out .= $center;
$out .= iconv('UTF-8','CP850//TRANSLIT', empresa('empresa_gracias')) . $LF;
$out .= $LF . $LF . $LF;
$out .= $cut;

// ── Guardar en archivo temporal y devolver token ───────────
$token = bin2hex(random_bytes(16));
$file  = sys_get_temp_dir() . '/insumos_ticket_' . $token . '.bin';
file_put_contents($file, $out);

ob_clean();
echo json_encode(['ok' => true, 'token' => $token]);
