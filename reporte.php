<?php 
session_start();
$contrasena_secreta = "kiro321"; 

if (isset($_GET['cerrar'])) { session_destroy(); header("Location: reporte.php"); exit; }
if (isset($_POST['clave'])) {
    if ($_POST['clave'] === $contrasena_secreta) { $_SESSION['autorizado'] = true; } 
    else { $error = "❌ Contraseña incorrecta. Intenta de nuevo."; }
}

if (!isset($_SESSION['autorizado'])) {
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bloqueado - Reporte</title>
    <link rel="icon" type="image/png" href="logoxd.png?v=1.1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background-color: #f4f7f6; height: 100vh; display: flex; align-items: center; justify-content: center;">
    <div class="card p-4 shadow" style="width: 350px; text-align: center; border-radius: 15px;">
        <h3 class="mb-3">🔒 Área Privada</h3>
        <p class="text-muted small">Ingresa la clave maestra para ver las ventas.</p>
        <?php if(isset($error)) { echo "<div class='alert alert-danger p-2'>$error</div>"; } ?>
        <form method="POST">
            <input type="password" name="clave" class="form-control mb-3" placeholder="Contraseña..." required autofocus>
            <button type="submit" class="btn btn-dark w-100 fw-bold">Desbloquear Reporte</button>
        </form>
        <br><a href="index.php" class="text-decoration-none">← Volver al Registro de Ventas</a>
    </div>
</body>
</html>
<?php exit; }

include 'db.php'; 
$fecha_pantalla = isset($_GET['fecha_pantalla']) ? $_GET['fecha_pantalla'] : date('Y-m-d');

if (isset($_GET['eliminar_id'])) {
    $id_a_borrar = mysqli_real_escape_string($conexion, $_GET['eliminar_id']);
    mysqli_query($conexion, "DELETE FROM reportes WHERE id = '$id_a_borrar'");
    header("Location: reporte.php?mensaje=borrado&fecha_pantalla=$fecha_pantalla");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ventas - Owen</title>
    <link rel="icon" type="image/png" href="logoxd.png?v=1.1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .contenido-principal { padding: 30px; }
        .card { border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border: none; margin-bottom: 30px; }
        .header-reporte { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 10px; }
        .table thead { background-color: #212529; color: white; }
        .badge-operacion { background-color: #e9ecef; color: #495057; padding: 5px 10px; border-radius: 5px; font-size: 0.85em; }
        .titulo-vendedor { background-color: #e3f2fd; color: #0d6efd; padding: 10px 15px; border-radius: 8px; font-weight: bold; margin-bottom: 15px; border-left: 5px solid #0d6efd; }
    </style>
</head>
<body>

<?php include 'menu.php'; ?>

<div class="container-fluid contenido-principal">
    
    <?php if(isset($_GET['mensaje']) && $_GET['mensaje'] == 'borrado'): ?>
        <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
            <strong>✅ ¡Venta eliminada!</strong> El registro ha sido borrado permanentemente.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="header-reporte" style="align-items: flex-start;">
        <div>
            <h2 class="mb-0">📊 Reporte General de Ventas</h2>
            <p class="text-muted">Mostrando resultados en pantalla para el día: <b><?php echo date('d/m/Y', strtotime($fecha_pantalla)); ?></b></p>
        </div>
        
        <div class="card p-3 shadow-sm bg-white" style="border: 1px solid #dee2e6; width: 100%;">
            <div class="row align-items-center">
                <div class="col-md-3 border-end">
                    <form action="reporte.php" method="GET" class="d-flex flex-column gap-1">
                        <label class="small fw-bold text-primary">📅 Ver ventas del día:</label>
                        <input type="date" name="fecha_pantalla" class="form-control form-control-sm border-primary text-primary fw-bold" value="<?php echo $fecha_pantalla; ?>" onchange="this.form.submit()">
                    </form>
                </div>

                <div class="col-md-9">
                    <form action="exportar.php" method="POST" class="row g-2 align-items-end justify-content-end">
                        <div class="col-auto">
                            <label class="small fw-bold text-muted">Desde:</label>
                            <input type="date" name="desde" class="form-control form-control-sm" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-auto">
                            <label class="small fw-bold text-muted">Hasta:</label>
                            <input type="date" name="hasta" class="form-control form-control-sm" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-auto">
                            <label class="small fw-bold text-muted">Operación:</label>
                            <select name="id_operacion" class="form-select form-select-sm">
                                <option value="">-- Todas --</option>
                                <?php
                                $res_ops = mysqli_query($conexion, "SELECT * FROM tipo_operacion");
                                while($op = mysqli_fetch_assoc($res_ops)){ echo "<option value='".$op['id']."'>".$op['nombre_operacion']."</option>"; }
                                ?>
                            </select>
                        </div>
                        <div class="col-auto">
                            <label class="small fw-bold text-muted">Método:</label>
                            <select name="metodo_pago_filtro" class="form-select form-select-sm">
                                <option value="">-- Todos --</option>
                                <option value="Efectivo">Solo Efectivo</option>
                                <option value="Yape">Solo Yape</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-success btn-sm fw-bold shadow-sm">
                                <i class="bi bi-file-earmark-excel"></i> Descargar Excel
                            </button>
                        </div>
                        <div class="col-auto ms-2 border-start ps-3">
                            <a href="index.php" class="btn btn-primary btn-sm shadow-sm">+ Nueva Venta</a>
                            <a href="reporte.php?cerrar=true" class="btn btn-outline-danger btn-sm shadow-sm ms-1" title="Bloquear Sistema">🔒</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php
    $sql_vendedores = "SELECT DISTINCT u.id, u.nombre FROM reportes r INNER JOIN usuarios u ON r.id_usuario = u.id 
                       WHERE r.fecha_registro BETWEEN '$fecha_pantalla 00:00:00' AND '$fecha_pantalla 23:59:59' ORDER BY u.nombre ASC";
    $res_vendedores = mysqli_query($conexion, $sql_vendedores);
    $gran_total_empresa = 0; 

    if ($res_vendedores && mysqli_num_rows($res_vendedores) > 0) {
        while($vendedor = mysqli_fetch_assoc($res_vendedores)) {
            $id_vend = $vendedor['id'];
            $nombre_vend = ucfirst($vendedor['nombre']);
            
            echo "<div class='card p-4'><div class='titulo-vendedor'>Agente: " . $nombre_vend . "</div><div class='table-responsive'>";
            echo "<table class='table table-hover align-middle table-sm'>";
            echo "<thead><tr>
                    <th>ID</th><th>Tienda</th><th>Operación</th><th>Modalidad</th><th>Plan</th>
                    <th>Equipo / Info Extra</th><th>Tipo Pago</th><th>Hora</th><th>Monto</th>
                    <th class='text-center'>Acción</th>
                  </tr></thead><tbody>";

            $sql_ventas = "SELECT r.id, r.tienda, t.nombre_operacion as operacion, r.modalidad, p.nombre_plan as plan, 
                                  r.marca_modelo, r.tipo_pago, r.precio_equipo, r.descuento, r.inicial, r.descuento_inicial, r.descripcion, 
                                  r.venta_por_fuera, r.metodo_pago, r.monto, r.fecha_registro 
                           FROM reportes r INNER JOIN tipo_operacion t ON r.id_operacion = t.id LEFT JOIN detalle_operacion p ON r.id_plan = p.id
                           WHERE r.id_usuario = $id_vend AND r.fecha_registro BETWEEN '$fecha_pantalla 00:00:00' AND '$fecha_pantalla 23:59:59'
                           ORDER BY r.fecha_registro DESC";
                           
            $res_ventas = mysqli_query($conexion, $sql_ventas);
            $subtotal_vendedor = 0;

            while($venta = mysqli_fetch_assoc($res_ventas)) {
                
                // LÓGICA DE COLORES Y SUMAS
                $color_monto = "#198754"; // Verde (Efectivo)
                $etiqueta_pago = "<br><small class='text-muted'>Efectivo</small>";
                $sumar_a_caja = true;

                if ($venta['venta_por_fuera'] === 'Si') {
                    $color_monto = "red"; // ROJO para ventas externas
                    $etiqueta_pago = "<br><small style='color:red;'>Por fuera</small>";
                    $sumar_a_caja = false; // No se suma
                } elseif ($venta['metodo_pago'] === 'Yape') {
                    $color_monto = "purple"; // Morado para Yape
                    $etiqueta_pago = "<br><small style='color:purple;'>Yape</small>";
                    $sumar_a_caja = false; // No se suma
                }

                if($sumar_a_caja) {
                    $subtotal_vendedor += $venta['monto'];
                }
                
                $info_extra = "";
                if(!empty($venta['marca_modelo'])) {
                    $info_extra .= "📱 <b>" . $venta['marca_modelo'] . "</b><br>";
                    if ($venta['tipo_pago'] === 'Al contado') {
                        if (!empty($venta['precio_equipo'])) $info_extra .= "<small class='text-muted'>Precio: S/ " . number_format($venta['precio_equipo'], 2) . "</small><br>";
                        if (!empty($venta['descuento']) && $venta['descuento'] > 0) $info_extra .= "<small class='text-danger'>Dscto: -S/ " . number_format($venta['descuento'], 2) . "</small><br>";
                    }
                }
                if(!empty($venta['descripcion'])) $info_extra .= "📝 " . $venta['descripcion'];
                if ($venta['venta_por_fuera'] === 'Si') $info_extra .= "<br><span class='badge bg-warning text-dark mt-1'>🚶 Venta por fuera</span>";
                
                if(empty($info_extra)) $info_extra = '<span class="text-muted">---</span>';

                echo "<tr>";
                echo "<td><span class='text-muted'>#" . $venta['id'] . "</span></td>";
                echo "<td><span class='badge bg-secondary'>" . ($venta['tienda'] ?: '---') . "</span></td>";
                echo "<td><span class='badge-operacion'>" . $venta['operacion'] . "</span></td>";
                echo "<td>" . ($venta['modalidad'] ?: '---') . "</td>";
                echo "<td>" . ($venta['plan'] ?: 'N/A') . "</td>";
                echo "<td>" . $info_extra . "</td>";
                echo "<td>" . ($venta['tipo_pago'] ?: '---') . "</td>";
                echo "<td class='text-muted'>" . date('H:i', strtotime($venta['fecha_registro'])) . "</td>";
                
                echo "<td class='fw-bold fs-6' style='color: $color_monto;'>S/ " . number_format($venta['monto'], 2) . $etiqueta_pago . "</td>";
                
                echo "<td class='text-center'>";
                echo "<a href='reporte.php?eliminar_id=" . $venta['id'] . "&fecha_pantalla=" . $fecha_pantalla . "' class='btn btn-outline-danger btn-sm' onclick=\"return confirm('¿Borrar venta #".$venta['id']."?');\">🗑️</a>";
                echo "</td>";
                echo "</tr>";
            }

            $gran_total_empresa += $subtotal_vendedor;
            echo "</tbody><tfoot class='table-light'><tr><td colspan='8' class='text-end fw-bold'>TOTAL CAJA EFECTIVO (" . $nombre_vend . "):</td>";
            echo "<td class='fw-bold text-primary fs-5'>S/ " . number_format($subtotal_vendedor, 2) . "</td><td></td></tr></tfoot></table></div></div>";
        }
        echo "<div class='alert alert-success text-end fs-4 fw-bold shadow-sm'>GRAN TOTAL EFECTIVO DEL DÍA: S/ " . number_format($gran_total_empresa, 2) . "</div>";
    } else {
        echo "<div class='card p-5 text-center text-muted'><h4>No hay ventas registradas el <b>" . date('d/m/Y', strtotime($fecha_pantalla)) . "</b>.</h4></div>";
    }
    ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>