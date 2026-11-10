<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if (($_SESSION['acceso_concedido'] ?? false) !== true) {
    http_response_code(403);
    echo json_encode(['error' => 'Ingresa la contraseña del inventario para descargar las ventas.']);
    exit;
}
session_write_close();
require_once __DIR__ . '/ventas_almacenamiento.php';
try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    require __DIR__ . '/db.php';
    $conn->set_charset('utf8mb4');
    // Actualiza únicamente el esquema antiguo; conserva todos los registros existentes.
    ventas_asegurar_vendedor($conn);
    $zona = new DateTimeZone('America/Lima');
    // TIMESTAMP se devuelve según la zona de la sesión MySQL.
    // Volver a consultar en UTC para convertir sin depender de la configuración del servidor.
    $conn->query("SET time_zone = '+00:00'");
    $consulta = $conn->query("SELECT id, creado_en, tienda, marca, modelo, imei, rom, ram, dni,
        telefono, tipo, financiera, vendedor, precio_centimos, inicial_centimos, descuento_centimos, total_centimos
        FROM ventas_equipos ORDER BY creado_en DESC, id DESC");
    $ventas = $consulta->fetch_all(MYSQLI_ASSOC);
    foreach ($ventas as &$venta) {
        $fecha = new DateTimeImmutable($venta['creado_en'], new DateTimeZone('UTC'));
        $venta['creado_en'] = $fecha->setTimezone($zona)->format('Y-m-d H:i:s');
    }
    unset($venta);
    echo json_encode(['ventas' => $ventas], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() === 1146) {
        echo json_encode(['ventas' => []]);
    } else {
        error_log('Error al exportar ventas: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'No se pudo consultar el reporte. Intenta nuevamente.']);
    }
} catch (Throwable $e) {
    error_log('Error al exportar ventas: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo generar el reporte.']);
}
