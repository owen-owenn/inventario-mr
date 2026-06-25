<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Caja - Owen</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🚀</text></svg>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .card { border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border: none; }
    </style>
</head>
<body>

<?php include 'menu.php'; ?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-white text-center rounded-top" style="background-color: #198754; border-radius: 15px 15px 0 0;">
                    <h4 class="mb-0 py-2"> Control de Caja</h4>
                </div>
                <div class="card-body p-4">
                    <form action="guardar.php" method="POST">
                        <input type="hidden" name="origen" value="caja">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tienda / Sucursal:</label>
                            <select name="tienda" class="form-select" required>
                                <option value="">-- Selecciona la tienda --</option>
                                <option value="Mercado modelo">Mercado modelo</option>
                                <option value="CAB">CAB</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Vendedor (Dueño de la caja):</label>
                            <select name="id_usuario" class="form-select" required>
                                <option value="">-- Selecciona --</option>
                                <?php
                                $res_user = mysqli_query($conexion, "SELECT * FROM usuarios");
                                while($u = mysqli_fetch_assoc($res_user)){
                                    echo "<option value='".$u['id']."'>".$u['nombre']."</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Movimiento de Caja:</label>
                            <select name="id_operacion" id="id_operacion" class="form-select" required>
                                <option value="">-- Selecciona --</option>
                                <?php
                                // AQUÍ SOLO MOSTRAMOS LAS OPCIONES DE CAJA
                                $res_op = mysqli_query($conexion, "SELECT * FROM tipo_operacion WHERE nombre_operacion IN ('Caja inicial', 'Plata entregada')");
                                while($o = mysqli_fetch_assoc($res_op)){
                                    // Le ponemos un emoji para que se vea más pro
                                    $emoji = ($o['nombre_operacion'] == 'Caja inicial') ? '🟢 ' : '🔴 ';
                                    echo "<option value='".$o['id']."'>". $emoji . $o['nombre_operacion']."</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Descripción (Opcional):</label>
                            <input type="text" name="descripcion" class="form-control" placeholder="Ej. Billetes de 10 o Entregado a supervisor">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold fs-5">Monto (S/):</label>
                            <input type="number" step="0.01" name="monto" class="form-control fs-4 fw-bold" placeholder="0.00" required>
                            <div class="form-text">Si es "Caja Inicial" se sumará a tu total. Si es "Plata entregada" se restará.</div>
                        </div>

                        <button type="submit" class="btn btn-success w-100 fw-bold fs-5 py-2">Registrar Movimiento</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>