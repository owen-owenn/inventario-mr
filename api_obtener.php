<?php
include 'conexion.php';

$tienda = isset($_GET['tienda']) ? $_GET['tienda'] : '';

if($tienda != '') {
    $sql = "SELECT * FROM equipos WHERE tienda = '$tienda' ORDER BY marca ASC, nombre ASC";
} else {
    $sql = "SELECT * FROM equipos ORDER BY marca ASC, nombre ASC";
}

$result = $conn->query($sql);
$equipos = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $equipos[] = array(
            "id" => $row["id"],
            "tienda" => $row["tienda"],
            "marca" => $row["marca"],
            "nombre" => $row["nombre"],
            "imei" => $row["imei"],
            "rom" => $row["rom"],
            "ram" => $row["ram"],
            "precioCompra" => (float)$row["precio_compra"],
            "precio" => (float)$row["precio_venta"],
            "precioCredito" => (float)$row["precio_credito"], // EL NUEVO PRECIO
            "stock_cab1" => (int)$row["stock_cab1"],
            "stock_cab2" => (int)$row["stock_cab2"],
            "stock_modelo" => (int)$row["stock_modelo"],
            "img" => $row["img"]
        );
    }
}
echo json_encode($equipos);
$conn->close();
?>