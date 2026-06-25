<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$servidor = "shinkansen.proxy.rlwy.net";
$usuario = "root";
$password = "IAysjpnhHovoOajGxXLrYYpkFDPxHsXl"; 
$base_datos = "railway";
$puerto = 30900;

$conn = new mysqli($servidor, $usuario, $password, $base_datos, $puerto);

if ($conn->connect_error) {
    die(json_encode(["error" => "Conexión fallida: " . $conn->connect_error]));
}

$conn->set_charset("utf8");
?>
