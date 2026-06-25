<?php
include 'conexion.php';
$data = json_decode(file_get_contents("php://input"), true);

if($data && isset($data['id'])) {
    $id = (int)$data['id'];
    
    // Comando SQL para borrar la fila exacta de ese equipo
    $sql = "DELETE FROM equipos WHERE id = $id";
    
    if($conn->query($sql) === TRUE) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => $conn->error]);
    }
} else {
    echo json_encode(["success" => false, "error" => "ID no recibido"]);
}
$conn->close();
?>