<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if (($_SESSION['acceso_concedido'] ?? false) !== true) {
    http_response_code(403); echo json_encode(['error'=>'Acceso restringido']); exit;
}
$_SESSION['mantenimiento_csrf'] ??= bin2hex(random_bytes(32));
$csrf = $_SESSION['mantenimiento_csrf'];
session_write_close();
require_once __DIR__ . '/ventas_almacenamiento.php';
try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    require __DIR__ . '/db.php';
    $reparaciones = [];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!is_string($_POST['csrf'] ?? null) || !hash_equals($csrf, $_POST['csrf']) || ($_POST['accion'] ?? '') !== 'convertir_innodb') {
            http_response_code(403); echo json_encode(['error'=>'Solicitud inválida']); exit;
        }
        $reparaciones = ventas_reparar_almacenamiento($conn);
    } elseif ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405); exit;
    }
    echo json_encode(['motores'=>ventas_motores($conn), 'csrf'=>$csrf, 'reparaciones'=>$reparaciones]);
} catch (Throwable $e) {
    error_log('Diagnostico ventas: '.$e->getMessage());
    http_response_code(500); echo json_encode(['error'=>'No se pudo verificar o reparar el almacenamiento. Revisa los registros de Railway.']);
}
