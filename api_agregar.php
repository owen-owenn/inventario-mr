<?php
include 'conexion.php';
$data = json_decode(file_get_contents("php://input"), true);

if($data) {
    $marca = $conn->real_escape_string($data['marca']);
    $nombre = $conn->real_escape_string($data['nombre']);
    $imei = $conn->real_escape_string($data['imei']);
    $rom = $conn->real_escape_string($data['rom']);
    $ram = $conn->real_escape_string($data['ram']);
    $precio_compra = (float)$data['precioCompra'];
    $precio_venta = (float)$data['precio'];
    $precio_credito = (float)$data['precioCredito']; // NUEVO PRECIO
    
    $stock_cab1 = (int)$data['stock_cab1'];
    $stock_cab2 = (int)$data['stock_cab2'];
    $stock_modelo = (int)$data['stock_modelo'];
    $img = "img/default.png";

    $sql = "INSERT INTO equipos (tienda, marca, nombre, imei, rom, ram, precio_compra, precio_venta, precio_credito, stock_cab1, stock_cab2, stock_modelo, img) 
            VALUES ('General', '$marca', '$nombre', '$imei', '$rom', '$ram', $precio_compra, $precio_venta, $precio_credito, $stock_cab1, $stock_cab2, $stock_modelo, '$img')";

    if($conn->query($sql) === TRUE) {
        echo json_encode(["success" => true, "id" => $conn->insert_id]);
    } else {
        echo json_encode(["success" => false, "error" => $conn->error]);
    }
}
$conn->close();
?>