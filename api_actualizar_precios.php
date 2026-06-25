<?php
include 'conexion.php';
$data = json_decode(file_get_contents("php://input"), true);

if($data && isset($data['id'])) {
    $id = (int)$data['id'];
    $costo = (float)$data['precio_compra'];
    $venta = (float)$data['precio_venta'];
    $credito = (float)$data['precio_credito']; // RECIBE EL PRECIO CRÉDITO

    $sql = "UPDATE equipos SET precio_compra = $costo, precio_venta = $venta, precio_credito = $credito WHERE id = $id";
    
    if($conn->query($sql) === TRUE) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => $conn->error]);
    }
}
$conn->close();
?>