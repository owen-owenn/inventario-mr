<?php
function ventas_motores(mysqli $conn): array {
    $filas = $conn->query("SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ('equipos', 'ventas_equipos')")->fetch_all(MYSQLI_NUM);
    $motores = [];
    foreach ($filas as [$tabla, $motor]) $motores[$tabla] = strtoupper($motor ?? '');
    return $motores;
}

// Solo la utilidad de mantenimiento autenticada llama a esta función.
// El formulario de venta nunca cambia motores ni crea respaldos.
function ventas_reparar_almacenamiento(mysqli $conn): array {
    $resultado = [];
    foreach (ventas_motores($conn) as $tabla => $motor) {
        if ($motor === 'INNODB') continue;
        if ($motor !== 'MYISAM') throw new RuntimeException('Motor no compatible con la reparación automática.');
        $respaldo = $tabla . '_respaldo_' . gmdate('Ymd_His') . '_' . bin2hex(random_bytes(3));
        $conn->query("CREATE TABLE $respaldo LIKE $tabla");
        $conn->query("LOCK TABLES $tabla WRITE, $respaldo WRITE");
        try {
            $conn->query("INSERT INTO $respaldo SELECT * FROM $tabla");
            $antes = (int)$conn->query("SELECT COUNT(*) FROM $tabla")->fetch_row()[0];
            $copiados = (int)$conn->query("SELECT COUNT(*) FROM $respaldo")->fetch_row()[0];
            if ($antes !== $copiados) throw new RuntimeException('El respaldo no coincide. Conversión cancelada.');
            $conn->query("ALTER TABLE $tabla ENGINE=InnoDB");
            $despues = (int)$conn->query("SELECT COUNT(*) FROM $tabla")->fetch_row()[0];
            if ($antes !== $despues) throw new RuntimeException('Revisar filas después de la conversión; el respaldo está conservado.');
            $resultado[] = ['tabla'=>$tabla, 'respaldo'=>$respaldo, 'filas'=>$despues];
        } finally {
            $conn->query('UNLOCK TABLES');
        }
    }
    return $resultado;
}

// Migración aditiva para instalaciones con ventas anteriores, sin asignarles un vendedor inventado.
// Ejecutar antes de empezar cualquier transacción de venta (ALTER TABLE confirma implícitamente).
function ventas_asegurar_vendedor(mysqli $conn): void {
    $columna = $conn->query("SHOW COLUMNS FROM ventas_equipos LIKE 'vendedor'");
    if ($columna->num_rows > 0) return;
    try {
        $conn->query("ALTER TABLE ventas_equipos ADD COLUMN vendedor VARCHAR(20) NULL DEFAULT NULL");
    } catch (mysqli_sql_exception $error) {
        // Otra petición pudo completar la misma migración entre la consulta y el ALTER.
        if ($error->getCode() !== 1060) throw $error;
    }
}
