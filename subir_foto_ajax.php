<?php
include 'db.php';

if(isset($_POST['id_reporte']) && isset($_POST['imagen_base64'])) {
    $id = (int)$_POST['id_reporte'];
    $base64 = $_POST['imagen_base64'];

    // 1. Limpiamos el código Base64 para convertirlo en archivo físico
    $base64 = str_replace('data:image/jpeg;base64,', '', $base64);
    $base64 = str_replace(' ', '+', $base64);
    $data = base64_decode($base64);

    // 2. Creamos un nombre único
    $nombre_foto = "evidencia_" . $id . "_" . time() . "_" . rand(10,99) . ".jpg";
    $ruta = "fotos/" . $nombre_foto;

    // 3. Guardamos la foto en la carpeta 'fotos'
    if(file_put_contents($ruta, $data)) {
        // Buscamos si ya tiene fotos guardadas
        $res = mysqli_query($conexion, "SELECT fotos_adjuntas FROM reportes WHERE id = $id");
        $row = mysqli_fetch_assoc($res);
        $actuales = $row['fotos_adjuntas'];

        // Añadimos la nueva separada por coma
        if(empty($actuales)) {
            $nuevas = $nombre_foto;
        } else {
            $nuevas = $actuales . "," . $nombre_foto;
        }

        mysqli_query($conexion, "UPDATE reportes SET fotos_adjuntas = '$nuevas' WHERE id = $id");
        echo "ok";
    } else {
        echo "error";
    }
}
?>