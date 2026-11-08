<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
require_once __DIR__ . '/ventas_validacion.php';

function respuesta_venta(int $estado, array $datos): void {
    http_response_code($estado);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    respuesta_venta(405, ['success' => false, 'error' => 'Usa el formulario Subir venta.']);
}
try {
    $entrada = json_decode(file_get_contents('php://input'), true, 32, JSON_THROW_ON_ERROR);
    if (!is_array($entrada)) throw new InvalidArgumentException('Datos de venta inválidos.');
    if (!isset($_SESSION['ventas_csrf'], $entrada['csrf']) || !is_string($entrada['csrf']) ||
        !hash_equals($_SESSION['ventas_csrf'], $entrada['csrf'])) {
        respuesta_venta(403, ['success' => false, 'error' => 'La sesión del formulario venció. Recarga la página.']);
    }
    $venta = validar_venta($entrada);
} catch (JsonException | InvalidArgumentException $error) {
    respuesta_venta(422, ['success' => false, 'error' => $error instanceof JsonException ? 'Datos de venta inválidos.' : $error->getMessage()]);
}
session_write_close();
$conn = null;
$en_transaccion = false;
try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    require __DIR__ . '/db.php';
    $conn->set_charset('utf8mb4');

    // Instalación no destructiva; nunca modifica los equipos existentes.
    $tabla = $conn->query("SHOW TABLES LIKE 'ventas_equipos'");
    if ($tabla->num_rows === 0) {
        $conn->query(file_get_contents(__DIR__ . '/ventas_schema.sql'));
    }
    $motores = $conn->query("SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ('equipos', 'ventas_equipos')")->fetch_all(MYSQLI_ASSOC);
    if (count($motores) !== 2 || array_filter($motores, fn($t) => strtoupper($t['ENGINE'] ?? '') !== 'INNODB')) {
        throw new RuntimeException('Las tablas de ventas e inventario deben usar InnoDB.');
    }
    $hash = hash('sha256', json_encode($venta, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    $buscar = $conn->prepare('SELECT id, datos_hash, total_centimos FROM ventas_equipos WHERE solicitud = ?');
    $buscar->execute([$venta['solicitud']]);
    $existente = $buscar->get_result()->fetch_assoc();
    if ($existente) {
        if (!hash_equals($existente['datos_hash'], $hash)) {
            respuesta_venta(409, ['success' => false, 'error' => 'Esta solicitud ya fue registrada con otros datos. Usa Registrar otra venta o vuelve a abrir el formulario.']);
        }
        respuesta_venta(200, ['success' => true, 'id' => $existente['id'], 'total_centimos' => (int)$existente['total_centimos']]);
    }

    $conn->begin_transaction();
    $en_transaccion = true;
    // La columna procede exclusivamente de la lista permitida de validar_venta.
    $columna = $venta['columna'];
    $consulta = $conn->prepare("SELECT marca, nombre, imei, rom, ram, $columna AS disponible FROM equipos WHERE id = ? FOR UPDATE");
    $consulta->execute([$venta['id']]);
    $equipo = $consulta->get_result()->fetch_assoc();
    if (!$equipo || (int)$equipo['disponible'] <= 0) {
        $conn->rollback();
        $en_transaccion = false;
        // Un reintento concurrente pudo haber consumido la última unidad.
        $buscar->execute([$venta['solicitud']]);
        $existente = $buscar->get_result()->fetch_assoc();
        if ($existente && hash_equals($existente['datos_hash'], $hash)) {
            respuesta_venta(200, ['success' => true, 'id' => $existente['id'], 'total_centimos' => (int)$existente['total_centimos']]);
        }
        respuesta_venta(409, ['success' => false, 'error' => 'El equipo ya no tiene stock en esa tienda. Selecciona otra tienda o equipo.']);
    }
    $insertar = $conn->prepare('INSERT INTO ventas_equipos
        (solicitud, datos_hash, equipo_id, marca, modelo, imei, rom, ram, tienda, dni, telefono,
         tipo, financiera, precio_centimos, inicial_centimos, descuento_centimos, total_centimos)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $insertar->execute([
        $venta['solicitud'], $hash, $venta['id'], $equipo['marca'] ?? '', $equipo['nombre'] ?? '',
        $equipo['imei'] ?? '', $equipo['rom'] ?? '', $equipo['ram'] ?? '', $columna,
        $venta['dni'], $venta['telefono'], $venta['tipo'], $venta['financiera'],
        $venta['precio'], $venta['inicial'], $venta['descuento'], $venta['total']
    ]);
    $id_venta = $conn->insert_id;
    $descontar = $conn->prepare("UPDATE equipos SET $columna = $columna - 1 WHERE id = ? AND $columna > 0");
    $descontar->execute([$venta['id']]);
    if ($descontar->affected_rows !== 1) throw new RuntimeException('No se pudo descontar el stock.');
    $conn->commit();
    $en_transaccion = false;
    respuesta_venta(201, ['success' => true, 'id' => $id_venta, 'total_centimos' => $venta['total']]);
} catch (Throwable $error) {
    if ($en_transaccion && $conn) $conn->rollback();
    // La restricción UNIQUE hace idempotentes los envíos simultáneos.
    if ($error instanceof mysqli_sql_exception && $error->getCode() === 1062 && isset($buscar, $hash)) {
        $buscar->execute([$venta['solicitud']]);
        $existente = $buscar->get_result()->fetch_assoc();
        if ($existente && hash_equals($existente['datos_hash'], $hash)) {
            respuesta_venta(200, ['success' => true, 'id' => $existente['id'], 'total_centimos' => (int)$existente['total_centimos']]);
        }
        respuesta_venta(409, ['success' => false, 'error' => 'Esta solicitud ya se registró. Abre un nuevo formulario para otra venta.']);
    }
    error_log('Error al registrar venta: ' . $error->getMessage());
    respuesta_venta(500, ['success' => false, 'error' => 'No se pudo confirmar la venta. Reintenta con los mismos datos; si persiste, revisa la conexión y la configuración de ventas_equipos.']);
}
