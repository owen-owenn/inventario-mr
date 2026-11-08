<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if (($_SESSION['acceso_concedido'] ?? false) !== true) {
    http_response_code(403); echo json_encode(['error'=>'Acceso restringido']); exit;
}
session_write_close();
try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    require __DIR__ . '/db.php';
    $tablas = $conn->query("SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ('equipos', 'ventas_equipos')")->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['tablas'=>$tablas, 'version'=>$conn->server_info]);
} catch (Throwable $e) {
    error_log('Diagnostico ventas: '.$e->getMessage());
    http_response_code(500); echo json_encode(['error'=>'No se pudo consultar el almacenamiento.']);
}
