<?php
session_start();
if (!isset($_SESSION['autorizado'])) { die("⛔ Acceso denegado."); }
include 'db.php';

$desde = $_POST['desde'];
$hasta = $_POST['hasta'];
$id_op_filtro = isset($_POST['id_operacion']) ? $_POST['id_operacion'] : '';
$metodo_filtro = isset($_POST['metodo_pago_filtro']) ? $_POST['metodo_pago_filtro'] : '';

$condicion_sql = "";
$texto_filtro = "TODAS LAS OPERACIONES";

if (!empty($id_op_filtro)) {
    $condicion_sql .= " AND r.id_operacion = '$id_op_filtro' ";
    $res_nombre = mysqli_query($conexion, "SELECT nombre_operacion FROM tipo_operacion WHERE id = '$id_op_filtro'");
    $dato_op = mysqli_fetch_assoc($res_nombre);
    $texto_filtro = strtoupper($dato_op['nombre_operacion']);
}
if (!empty($metodo_filtro)) {
    $condicion_sql .= " AND r.metodo_pago = '$metodo_filtro' ";
    $texto_filtro .= " (" . strtoupper($metodo_filtro) . ")";
}

// CONFIGURACIÓN DE CABECERAS PARA EXCEL
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=REPORTE_BITEL_".$desde.".xls");
header("Pragma: no-cache");
header("Expires: 0");
echo "\xEF\xBB\xBF"; // BOM para asegurar que los acentos y la Ñ se vean bien en Excel

// CONSULTA PARA RESUMEN GENERAL (DASHBOARD)
$sql_global = "SELECT 
    SUM(CASE WHEN metodo_pago = 'Efectivo' AND venta_por_fuera = 'No' THEN monto ELSE 0 END) as global_efectivo,
    SUM(CASE WHEN metodo_pago = 'Yape' AND venta_por_fuera = 'No' THEN monto ELSE 0 END) as global_yape,
    SUM(CASE WHEN venta_por_fuera = 'Si' THEN monto ELSE 0 END) as global_fuera,
    COUNT(*) as total_transacciones
    FROM reportes r
    WHERE r.fecha_registro BETWEEN '$desde 00:00:00' AND '$hasta 23:59:59' $condicion_sql";
$res_global = mysqli_query($conexion, $sql_global);
$dg = mysqli_fetch_assoc($res_global);

$sql_vendedores = "SELECT DISTINCT u.id, u.nombre FROM reportes r INNER JOIN usuarios u ON r.id_usuario = u.id 
                   WHERE r.fecha_registro BETWEEN '$desde 00:00:00' AND '$hasta 23:59:59' $condicion_sql ORDER BY u.nombre ASC";
$res_vendedores = mysqli_query($conexion, $sql_vendedores);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        .tabla-excel { border-collapse: collapse; width: 100%; font-family: 'Segoe UI', Arial, sans-serif; }
        .tabla-excel th { background-color: #FFCC00; color: #000; border: 1px solid #000; padding: 10px; font-size: 12px; }
        .tabla-excel td { border: 1px solid #ccc; padding: 6px; text-align: center; font-size: 11px; }
        
        /* Estilos de Fila */
        .header-negro { background-color: #212529; color: #ffffff; font-weight: bold; padding: 10px; text-align: left; font-size: 14px; }
        .zebra { background-color: #f9f9f9; }
        
        /* Colores de Montos */
        .monto-verde { color: #198754; font-weight: bold; }
        .monto-morado { color: #800080; font-weight: bold; }
        .monto-rojo { color: #dc3545; font-weight: bold; }
        
        /* Dashboard */
        .dash-box { border: 2px solid #FFCC00; background-color: #fff; text-align: center; }
        .dash-label { font-size: 10px; color: #666; text-transform: uppercase; }
        .dash-value { font-size: 16px; font-weight: bold; }
    </style>
</head>
<body>

<table class="tabla-excel">
    <tr>
        <td colspan="4" style="text-align:left; font-size: 20px; font-weight: bold; color: #FFCC00;">🚀 SISTEMA OWEN</td>
        <td colspan="9" style="text-align:right; font-weight: bold;">REPORTE DE GESTIÓN DE VENTAS - BITEL</td>
    </tr>
    <tr>
        <td colspan="4" style="text-align:left; color: #666;">Distrito: Ate, Lima</td>
        <td colspan="9" style="text-align:right; color: #666;">Generado el: <?php echo date('d/m/Y H:i'); ?></td>
    </tr>
</table>

<br>

<table class="tabla-excel" style="width: 800px;">
    <tr>
        <th colspan="4" style="background-color: #eee; border: 1px solid #ccc;">📊 RESUMEN EJECUTIVO DEL PERIODO</th>
    </tr>
    <tr>
        <td class="dash-box">
            <span class="dash-label">Ventas Totales</span><br>
            <span class="dash-value"><?php echo $dg['total_transacciones']; ?> ops.</span>
        </td>
        <td class="dash-box">
            <span class="dash-label">Caja Efectivo</span><br>
            <span class="dash-value" style="color: #198754;">S/ <?php echo number_format($dg['global_efectivo'], 2); ?></span>
        </td>
        <td class="dash-box">
            <span class="dash-label">Total Yape</span><br>
            <span class="dash-value" style="color: #800080;">S/ <?php echo number_format($dg['global_yape'], 2); ?></span>
        </td>
        <td class="dash-box">
            <span class="dash-label">Ventas por Fuera</span><br>
            <span class="dash-value" style="color: #dc3545;">S/ <?php echo number_format($dg['global_fuera'], 2); ?></span>
        </td>
    </tr>
</table>

<br>

<?php
while($vendedor = mysqli_fetch_assoc($res_vendedores)) {
    $id_vend = $vendedor['id'];
    ?>
    <table class="tabla-excel">
        <thead>
            <tr>
                <th colspan="13" class="header-negro">👤 AGENTE: <?php echo strtoupper($vendedor['nombre']); ?></th>
            </tr>
            <tr>
                <th style="width: 50px;">ID</th>
                <th>TIENDA</th>
                <th>OPERACIÓN</th>
                <th>MODALIDAD</th>
                <th>PLAN / EQUIPO</th>
                <th>MÉTODO</th>
                <th>PAGO</th>
                <th>PRECIO</th>
                <th>DSCTO</th>
                <th>INICIAL</th>
                <th>DSCTO INIC.</th>
                <th>MONTO FINAL</th>
                <th>FECHA / HORA</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $sql_ventas = "SELECT r.*, t.nombre_operacion as operacion, p.nombre_plan as plan 
                       FROM reportes r 
                       INNER JOIN tipo_operacion t ON r.id_operacion = t.id
                       LEFT JOIN detalle_operacion p ON r.id_plan = p.id
                       WHERE r.id_usuario = $id_vend 
                       AND r.fecha_registro BETWEEN '$desde 00:00:00' AND '$hasta 23:59:59' $condicion_sql
                       ORDER BY r.fecha_registro ASC";

        $res_ventas = mysqli_query($conexion, $sql_ventas);
        $sub_efectivo = 0;
        $sub_yape = 0;
        $idx = 0;

        while($f = mysqli_fetch_assoc($res_ventas)) {
            $idx++;
            $clase_monto = "monto-verde";
            
            if ($f['venta_por_fuera'] === 'Si') {
                $clase_monto = "monto-rojo";
            } elseif ($f['metodo_pago'] === 'Yape') {
                $clase_monto = "monto-morado";
                $sub_yape += $f['monto'];
            } else {
                $sub_efectivo += $f['monto'];
            }

            // Detalle inteligente de Plan o Equipo
            $detalle = $f['plan'] ? "Plan: " . $f['plan'] : ($f['marca_modelo'] ? "Equipo: " . $f['marca_modelo'] : "---");
            ?>
            <tr <?php echo ($idx % 2 == 0) ? 'class="zebra"' : ''; ?>>
                <td style="color: #999;">#<?php echo $f['id']; ?></td>
                <td><?php echo strtoupper($f['tienda']); ?></td>
                <td><b><?php echo $f['operacion']; ?></b></td>
                <td><?php echo $f['modalidad'] ?: '---'; ?></td>
                <td style="text-align: left;"><?php echo $detalle; ?></td>
                <td><?php echo $f['metodo_pago']; ?></td>
                <td><?php echo $f['tipo_pago'] ?: '---'; ?></td>
                <td><?php echo $f['precio_equipo'] > 0 ? "S/ ".number_format($f['precio_equipo'], 2) : '---'; ?></td>
                <td style="color: red;"><?php echo $f['descuento'] > 0 ? "-S/ ".number_format($f['descuento'], 2) : '---'; ?></td>
                <td><?php echo $f['inicial'] > 0 ? "S/ ".number_format($f['inicial'], 2) : '---'; ?></td>
                <td style="color: red;"><?php echo $f['descuento_inicial'] > 0 ? "-S/ ".number_format($f['descuento_inicial'], 2) : '---'; ?></td>
                <td class="<?php echo $clase_monto; ?>">S/ <?php echo number_format($f['monto'], 2); ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($f['fecha_registro'])); ?></td>
            </tr>
        <?php } ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="11" style="text-align: right; font-weight: bold; background-color: #eee;">SUBTOTAL EFECTIVO CAJA:</td>
                <td class="monto-verde" style="background-color: #eee;">S/ <?php echo number_format($sub_efectivo, 2); ?></td>
                <td style="background-color: #eee;"></td>
            </tr>
            <tr>
                <td colspan="11" style="text-align: right; font-weight: bold; background-color: #f3e5f5;">SUBTOTAL YAPE (Bancos):</td>
                <td class="monto-morado" style="background-color: #f3e5f5;">S/ <?php echo number_format($sub_yape, 2); ?></td>
                <td style="background-color: #f3e5f5;"></td>
            </tr>
        </tfoot>
    </table>
    <br>
<?php } ?>

<br>
<table class="tabla-excel" style="width: 400px; float: right;">
    <tr>
        <th colspan="2" style="font-size: 16px;">RESUMEN FINAL DEL REPORTE</th>
    </tr>
    <tr class="gran-total-bg">
        <td style="padding: 15px; text-align: right;">GRAN TOTAL CAJA (EFECTIVO):</td>
        <td style="padding: 15px; color: #198754; font-size: 18px;">S/ <?php echo number_format($dg['global_efectivo'], 2); ?></td>
    </tr>
    <tr class="gran-total-yape-bg">
        <td style="padding: 15px; text-align: right;">GRAN TOTAL YAPE (BANCOS):</td>
        <td style="padding: 15px; color: #800080; font-size: 18px;">S/ <?php echo number_format($dg['global_yape'], 2); ?></td>
    </tr>
</table>

</body>
</html>