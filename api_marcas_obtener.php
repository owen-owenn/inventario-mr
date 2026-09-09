<?php
include 'conexion.php';

// Asegurarnos de que la tabla exista antes de leerla
$conn->query("CREATE TABLE IF NOT EXISTS marcas_colores (nombre VARCHAR(50) PRIMARY KEY, color VARCHAR(20))");

$res = $conn->query("SELECT nombre, color FROM marcas_colores");
$marcas = [];
while($row = $res->fetch_assoc()){
    $marcas[] = $row;
}
echo json_encode($marcas);
?>