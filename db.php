<?php
$servidor = "shinkansen.proxy.rlwy.net";
$usuario = "root";
$password = "IAysjpnhHovoOajGxXLrYYpkFDPxHsXl"; 
$base_datos = "railway";
$puerto = 30900;

// Conexión a la base de datos de Railway
$conn = mysqli_connect($servidor, $usuario, $password, $base_datos, $puerto);

if (!$conn) {
    die("La conexión ha fallado: " . mysqli_connect_error());
}
?>
