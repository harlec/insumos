<?php
session_start();
require_once __DIR__ . '/permisos.php';
guardia_endpoint_auto();
if (empty($_SESSION['ingress'])) { http_response_code(403); exit; }

require_once __DIR__ . '/dompdf/autoload.inc.php';
require_once __DIR__ . '/vendor/autoload.php';
use Luecano\NumeroALetras\NumeroALetras;
use Dompdf\Dompdf;
require_once __DIR__ . '/empresa.php';

$data     = json_decode(file_get_contents('php://input'), true);
$items    = $data['items']    ?? [];
$grand    = floatval($data['grand']    ?? 0);
$vendedor = htmlspecialchars($data['vendedor'] ?? '', ENT_QUOTES);
$fecha    = htmlspecialchars($data['fecha']    ?? '', ENT_QUOTES);
$cliente  = htmlspecialchars(strtoupper($data['cliente']  ?? ''), ENT_QUOTES);
$addr     = htmlspecialchars($data['addr']     ?? '', ENT_QUOTES);
$phone    = htmlspecialchars($data['phone']    ?? '', ENT_QUOTES);

$formatter = new NumeroALetras();
$letras    = $formatter->toInvoice($grand, 2) . ' SOLES';

$mostrar_de_venta = '';
foreach ($items as $ci) {
    $name  = htmlspecialchars($ci['name']  ?? '', ENT_QUOTES);
    $total = number_format(floatval($ci['total'] ?? 0), 2, '.', ',');
    $mostrar_de_venta .= '<tr>
        <td style="font-weight:bold; font-size:11px;"> ' . $name . '</td>
        <td style="text-align: right; font-weight:bold;">' . $total . '</td>
    </tr>';
}

$tot_fmt  = number_format($grand, 2, '.', ',');


ob_start();
?>
<style>
    body {
        font-family: Helvetica, Sans-Serif;
    }
    thead th {
        font-size: 9px;
        font-weight: bold;
    }
    tbody td {
        font-size: 9px;
    }
    @page {
        margin-left: 0.4cm;
        margin-right: 0.4cm;
        margin-top: 0.4cm;
    }
</style>
<?= empresa_cabecera_html(230) ?>
<h5 style="text-align:center;"><b>NOTA VENTA</b></h5>
<h6>FECHA: <?= $fecha ?><br>CLIENTE: <?= $cliente ?>
</h6>
<hr>
<table>
    <thead>
        <tr>
            <th>[CANT.][UNID] DESCRIPCIÓN</th>
            <th style="text-align: right;">TOTAL</th>
        </tr>
    </thead>
    <tbody>
        <?= $mostrar_de_venta ?>
        <tr>
            <td style="text-align: right;" class="text-right"><h4>TOTAL: S/</h4></td>
            <td style="padding-left:8px; text-align: right; font-size:12px;"><h4><?= $tot_fmt ?></h4></td>
        </tr>
        <tr>
            <td colspan="2"><B>IMPORTE EN LETRAS: </B><?= $letras ?></td>
        </tr>
        <tr>
            <td colspan="2"><B>VENDEDOR: </B><?= $vendedor ?></td>
        </tr>
        <tr>
            <td colspan="2"><B>PERSONAL ENTREGA: __________________________</td>
        </tr>
    </tbody>
</table>
<h6 style="text-align:center;"><?= empresa_h(empresa('empresa_gracias')) ?></h6>
<?php
$html = ob_get_clean();

$dompdf = new DOMPDF();
$dompdf->load_html($html);
$dompdf->set_paper(array(0, 0, 200, 2000));
$dompdf->render();
$pdf = $dompdf->output();

// ── Guardar en /temp/ y devolver URL pública para RawBT ────
$temp_dir = realpath(__DIR__ . '/../temp');
$sess_id  = session_id();

// Limpiar tickets anteriores de esta sesión
foreach (glob("$temp_dir/ticket_{$sess_id}_*.pdf") ?: [] as $old) {
    @unlink($old);
}

$filename = "ticket_{$sess_id}_" . time() . ".pdf";
file_put_contents("$temp_dir/$filename", $pdf);

$protocol   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host       = $_SERVER['HTTP_HOST'];
$ticket_url = "$protocol://$host/temp/$filename";

header('Content-Type: application/json');
ob_clean();
echo json_encode(['ok' => true, 'url' => $ticket_url]);
