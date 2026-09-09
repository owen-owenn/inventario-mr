<?php
include 'conexion.php';
$data = json_decode(file_get_contents('php://input'), true);
$nombre = strtoupper(trim($data['nombre']));
$color = $data['color'];

if (!$nombre) {
    echo json_encode(['success' => false]);
    exit;
}

// Magia pura: Crear la tabla automáticamente si no existe en Railway
$conn->query("CREATE TABLE IF NOT EXISTS marcas_colores (nombre VARCHAR(50) PRIMARY KEY, color VARCHAR(20))");

$stmt = $conn->prepare("INSERT INTO marcas_colores (nombre, color) VALUES (?, ?) ON DUPLICATE KEY UPDATE color = ?");
$stmt->bind_param("sss", $nombre, $color, $color);

if($stmt->execute()){
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>