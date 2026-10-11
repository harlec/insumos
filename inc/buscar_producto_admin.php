<?php require_once __DIR__ . '/config.php';
session_start();
require_once __DIR__ . '/permisos.php';
guardia_endpoint_auto();
header('Content-Type: application/json');

if (!isset($_SESSION['ingress']) || $_SESSION['ingress'] !== true) {
    echo json_encode([]);
    exit;
}

$conn = db_connect();
$conn->set_charset('utf8');

$q = '%' . $conn->real_escape_string($_GET['q'] ?? '') . '%';
$r = $conn->query("SELECT id_producto, nom_prod FROM productos WHERE estado = '1' AND nom_prod LIKE '$q' ORDER BY nom_prod LIMIT 20");
$data = [];
while ($row = $r->fetch_assoc()) {
    $data[] = ['id' => (int)$row['id_producto'], 'text' => $row['nom_prod']];
}
$conn->close();
echo json_encode($data);
