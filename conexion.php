<?php
// conexion.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// CONFIGURACIÓN PARA RAILWAY
$servidor = "shinkansen.proxy.rlwy.net"; 
$usuario = "root";            
$password = "PEGAR_AQUI_TU_CONTRASEÑA_DE_RAILWAY"; // <-- BORRA ESTO Y PEGA TU CONTRASEÑA DE RAILWAY
$base_datos = "railway"; 
$puerto = 30900; 

// Intentar la conexión (se añade la variable del puerto al final para Railway)
$conn = new mysqli($servidor, $usuario, $password, $base_datos, $puerto);

// Si falla, nos dirá exactamente por qué
if ($conn->connect_error) {
    die(json_encode(["error" => "Conexión fallida: " . $conn->connect_error]));
}

// Para que las tildes de los nombres se vean bien
$conn->set_charset("utf8");
?>