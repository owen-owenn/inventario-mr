<?php
include 'conexion.php';
$data = json_decode(file_get_contents("php://input"), true);

if($data && isset($data['id']) && isset($data['columna']) && isset($data['stock'])) {
    $id = (int)$data['id'];
    $columna = $conn->real_escape_string($data['columna']); 
    $nuevoStock = (int)$data['stock'];
    
    // Medida de seguridad: Solo permitimos modificar estas 3 columnas exactas
    $columnas_permitidas = ['stock_cab1', 'stock_cab2', 'stock_modelo'];
    
    if (in_array($columna, $columnas_permitidas)) {
        $sql = "UPDATE equipos SET $columna = $nuevoStock WHERE id = $id";
        
        if($conn->query($sql) === TRUE) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => $conn->error]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "Columna no permitida"]);
    }
}
$conn->close();
?>