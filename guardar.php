<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
mysqli_report(MYSQLI_REPORT_OFF); 

include 'db.php';

$tienda = isset($_POST['tienda']) ? mysqli_real_escape_string($conexion, $_POST['tienda']) : '';
$id_usuario = isset($_POST['id_usuario']) ? mysqli_real_escape_string($conexion, $_POST['id_usuario']) : '';
$id_operacion = isset($_POST['id_operacion']) ? mysqli_real_escape_string($conexion, $_POST['id_operacion']) : '';
$monto_input = isset($_POST['monto']) ? (float)$_POST['monto'] : 0;
$metodo_pago = isset($_POST['metodo_pago']) ? mysqli_real_escape_string($conexion, $_POST['metodo_pago']) : 'Efectivo';

// ¡AQUÍ ESTÁ LA MAGIA DEL INTERRUPTOR!
$venta_por_fuera = isset($_POST['venta_por_fuera']) ? "'Si'" : "'No'";

$query_op = mysqli_query($conexion, "SELECT nombre_operacion FROM tipo_operacion WHERE id = '$id_operacion'");
$arr_op = mysqli_fetch_assoc($query_op);
$nom_op = strtolower($arr_op['nombre_operacion']);

if (strpos($nom_op, 'retiro') !== false || strpos($nom_op, 'entregada') !== false) {
    $monto = -abs($monto_input);
} else {
    $monto = abs($monto_input);
}

$modalidad = !empty($_POST['modalidad']) ? "'" . mysqli_real_escape_string($conexion, $_POST['modalidad']) . "'" : "NULL";
$id_plan = !empty($_POST['id_plan']) ? "'" . mysqli_real_escape_string($conexion, $_POST['id_plan']) . "'" : "NULL"; 
$marca_modelo = !empty($_POST['marca_modelo']) ? "'" . mysqli_real_escape_string($conexion, $_POST['marca_modelo']) . "'" : "NULL";
$tipo_pago = !empty($_POST['tipo_pago']) ? "'" . mysqli_real_escape_string($conexion, $_POST['tipo_pago']) . "'" : "NULL";
$descripcion = !empty($_POST['descripcion']) ? "'" . mysqli_real_escape_string($conexion, $_POST['descripcion']) . "'" : "NULL";
$precio_equipo = !empty($_POST['precio_equipo']) ? "'" . mysqli_real_escape_string($conexion, $_POST['precio_equipo']) . "'" : "NULL";
$descuento = !empty($_POST['descuento']) ? "'" . mysqli_real_escape_string($conexion, $_POST['descuento']) . "'" : "NULL";
$inicial = !empty($_POST['inicial']) ? "'" . mysqli_real_escape_string($conexion, $_POST['inicial']) . "'" : "NULL";
$descuento_inicial = !empty($_POST['descuento_inicial']) ? "'" . mysqli_real_escape_string($conexion, $_POST['descuento_inicial']) . "'" : "NULL";

// Subida de foto clásica desde el index
$nombre_foto_final = "NULL"; 
if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
    $directorio_destino = "fotos/";
    $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $nuevo_nombre = "foto_" . time() . "_" . rand(100,999) . "." . $extension;
    $ruta_completa = $directorio_destino . $nuevo_nombre;
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_completa)) {
        $nombre_foto_final = "'" . $nuevo_nombre . "'"; 
    }
}

// Inyectamos todo en SQL (Fíjate que aquí ya viaja $venta_por_fuera)
$sql = "INSERT INTO reportes (tienda, id_usuario, id_operacion, modalidad, id_plan, marca_modelo, tipo_pago, precio_equipo, descuento, inicial, descuento_inicial, descripcion, venta_por_fuera, metodo_pago, foto, monto) 
        VALUES ('$tienda', '$id_usuario', '$id_operacion', $modalidad, $id_plan, $marca_modelo, $tipo_pago, $precio_equipo, $descuento, $inicial, $descuento_inicial, $descripcion, $venta_por_fuera, '$metodo_pago', $nombre_foto_final, '$monto')";

if (mysqli_query($conexion, $sql)) {
    if (isset($_POST['origen']) && $_POST['origen'] == 'caja') {
        header("Location: caja.php");
    } else {
        header("Location: index.php");
    }
    exit;
} else {
    echo "<h2>🚨 ¡Error en Base de Datos!</h2>" . mysqli_error($conexion);
}
?>