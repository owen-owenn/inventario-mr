<?php
// Datos de conexión de InfinityFree
$host = "sql100.infinityfree.com";
$user = "if0_41626201";
$pass = "6ADKu2NYrGoBj3"; // <-- OJO: Borra esto y pon la contraseña que copiaste antes
$db   = "if0_41626201_ventas";

$conexion = mysqli_connect($host, $user, $pass, $db);

if (!$conexion) {
    die("Error de conexión a la nube: " . mysqli_connect_error());
}
?>