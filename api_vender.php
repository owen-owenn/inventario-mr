<?php
include 'conexion.php';
$data = json_decode(file_get_contents("php://input"), true);

if($data && isset($data['id']) && isset($data['columna'])) {
    $id = (int)$data['id'];
    $columna = $conn->real_escape_string($data['columna']);
    
    $columnas_permitidas = ['stock_cab1', 'stock_cab2', 'stock_modelo'];
    
    if (in_array($columna, $columnas_permitidas)) {
        // Restamos 1 al stock solo de la columna seleccionada y si es mayor a 0
        $sql = "UPDATE equipos SET $columna = $columna - 1 WHERE id = $id AND $columna > 0";
        
        if($conn->query($sql) === TRUE) {
            if($conn->affected_rows > 0) {
                echo json_encode(["success" => true, "mensaje" => "Stock actualizado"]);
            } else {
                echo json_encode(["success" => false, "error" => "No hay stock o equipo no encontrado"]);
            }
        } else {
            echo json_encode(["success" => false, "error" => $conn->error]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "Columna no permitida"]);
    }
}
$conn->close();
?>