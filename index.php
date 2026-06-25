<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Venta - Owen</title>
    <link rel="icon" type="image/png" href="logoxd.png?v=1.1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .oculto { display: none; }
        .card { border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border: none; }
    </style>
</head>
<body>

<?php include 'menu.php'; ?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white text-center rounded-top" style="border-radius: 15px 15px 0 0;">
                    <h4 class="mb-0 py-2">Registrar Venta</h4>
                </div>
                <div class="card-body p-4">
                    <form action="guardar.php" method="POST" enctype="multipart/form-data">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tienda / Sucursal:</label>
                            <select name="tienda" class="form-select" required>
                                <option value="">-- Selecciona la tienda --</option>
                                <option value="Mercado modelo">Mercado modelo</option>
                                <option value="CAB">CAB</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Vendedor:</label>
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
                            <label class="form-label fw-bold">Tipo de Operación:</label>
                            <select name="id_operacion" id="id_operacion" class="form-select" required>
                                <option value="">-- Selecciona --</option>
                                <?php
                                $res_op = mysqli_query($conexion, "SELECT * FROM tipo_operacion WHERE nombre_operacion NOT IN ('Caja inicial', 'Plata entregada')");
                                while($o = mysqli_fetch_assoc($res_op)){
                                    echo "<option value='".$o['id']."'>".$o['nombre_operacion']."</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mb-3 oculto" id="div_modalidad">
                            <label class="form-label fw-bold">Modalidad:</label>
                            <select name="modalidad" id="modalidad" class="form-select">
                                <option value="">-- Selecciona --</option>
                                <option value="Prepago">Prepago</option>
                                <option value="Postpago">Postpago</option>
                            </select>
                        </div>

                        <div class="mb-3 oculto" id="div_plan">
                            <label class="form-label fw-bold">Plan:</label>
                            <select name="id_plan" id="id_plan" class="form-select">
                                <option value="">-- Selecciona --</option>
                                <?php
                                $res_plan = mysqli_query($conexion, "SELECT * FROM detalle_operacion");
                                while($p = mysqli_fetch_assoc($res_plan)){
                                    echo "<option value='".$p['id']."'>".$p['nombre_plan']."</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mb-3 oculto" id="div_marca">
                            <label class="form-label fw-bold">Marca y modelo:</label>
                            <input type="text" name="marca_modelo" id="marca_modelo" class="form-control" placeholder="Ej. Xiaomi Redmi Note 12">
                        </div>

                        <div class="mb-3 oculto" id="div_pago">
                            <label class="form-label fw-bold">Tipo de pago:</label>
                            <select name="tipo_pago" id="tipo_pago" class="form-select">
                                <option value="">-- Selecciona --</option>
                                <option value="Al contado">Al contado</option>
                                <option value="A credito">A crédito</option>
                            </select>
                        </div>

                        <div class="mb-3 oculto" id="div_precio">
                            <label class="form-label fw-bold text-primary">Precio total del equipo (S/):</label>
                            <input type="number" step="0.01" name="precio_equipo" id="precio_equipo" class="form-control" placeholder="0.00">
                        </div>

                        <div class="mb-3 oculto" id="div_descuento">
                            <label class="form-label fw-bold text-danger">Descuento del equipo (S/):</label>
                            <input type="number" step="0.01" name="descuento" id="descuento" class="form-control" placeholder="0.00">
                        </div>

                        <div class="mb-3 oculto" id="div_inicial">
                            <label class="form-label fw-bold text-warning">Pago Inicial (S/):</label>
                            <input type="number" step="0.01" name="inicial" id="inicial" class="form-control" placeholder="0.00">
                        </div>

                        <div class="mb-3 oculto" id="div_descuento_inicial">
                            <label class="form-label fw-bold text-danger">Descuento a la Inicial (S/):</label>
                            <input type="number" step="0.01" name="descuento_inicial" id="descuento_inicial" class="form-control" placeholder="0.00">
                        </div>

                        <div class="mb-3 oculto" id="div_descripcion">
                            <label class="form-label fw-bold">Breve descripción:</label>
                            <input type="text" name="descripcion" id="descripcion" class="form-control" placeholder="Opcional: Detalla la operación...">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Subir Foto (Opcional):</label>
                            <input type="file" name="foto" class="form-control" accept="image/*">
                            <div class="form-text">Adjunta una imagen si la operación lo requiere.</div>
                        </div>

                        <div class="mb-3 form-check form-switch border p-2 rounded bg-light">
                            <input class="form-check-input fs-5 ms-0 me-2" type="checkbox" name="venta_por_fuera" id="venta_por_fuera" value="Si">
                            <label class="form-check-label fw-bold text-primary mt-1" for="venta_por_fuera">¿Es venta por fuera?</label>
                            <div class="form-text ms-1">Marca este interruptor solo si la venta se realizó fuera del local.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Método de Ingreso:</label>
                            <select name="metodo_pago" class="form-select" required>
                                <option value="Efectivo">Efectivo</option>
                                <option value="Yape">Yape</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-success">Monto final a caja (S/):</label>
                            <input type="number" step="0.01" name="monto" id="monto_final" class="form-control fs-5 fw-bold text-success" value="0.00">
                            <div class="form-text">Si la operación es gratuita, déjalo en 0.00.</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-bold fs-5 py-2">Registrar Venta</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const opSelect = document.getElementById('id_operacion');
    const modSelect = document.getElementById('modalidad');
    const pagoSelect = document.getElementById('tipo_pago');
    
    const divMod = document.getElementById('div_modalidad');
    const divPlan = document.getElementById('div_plan');
    const divMarca = document.getElementById('div_marca');
    const divPago = document.getElementById('div_pago');
    const divPrecio = document.getElementById('div_precio');
    const divDescuento = document.getElementById('div_descuento');
    const divInicial = document.getElementById('div_inicial');
    const divDescInicial = document.getElementById('div_descuento_inicial');
    const divDesc = document.getElementById('div_descripcion');

    const precioInput = document.getElementById('precio_equipo');
    const descuentoInput = document.getElementById('descuento');
    const inicialInput = document.getElementById('inicial');
    const descuentoIniInput = document.getElementById('descuento_inicial');
    const montoFinalInput = document.getElementById('monto_final');

    function actualizarFormulario() {
        let op = opSelect.options[opSelect.selectedIndex].text.toLowerCase();
        let mod = modSelect.value;
        let pago = pagoSelect.value;

        divMod.classList.add('oculto');
        divPlan.classList.add('oculto');
        divMarca.classList.add('oculto');
        divPago.classList.add('oculto');
        divPrecio.classList.add('oculto');
        divDescuento.classList.add('oculto');
        divInicial.classList.add('oculto');
        divDescInicial.classList.add('oculto');
        divDesc.classList.add('oculto');

        // AQUÍ ESTÁ EL CAMBIO: Ahora también muestra el divDesc (Descripción)
        if (op.includes("portabilidad") || op.includes("linea nueva")) {
            divMod.classList.remove('oculto');
            divDesc.classList.remove('oculto'); 
            
            if (mod === "Postpago") {
                divPlan.classList.remove('oculto');
            }
        } 
        else if (op.includes("migracion")) {
            divPlan.classList.remove('oculto');
        }
        else if (op.includes("venta de equipo")) {
            divMarca.classList.remove('oculto');
            divPago.classList.remove('oculto');
            
            if (pago === "Al contado") {
                divPrecio.classList.remove('oculto');
                divDescuento.classList.remove('oculto');
            } else if (pago === "A credito") {
                divPrecio.classList.remove('oculto'); 
                divInicial.classList.remove('oculto'); 
                divDescInicial.classList.remove('oculto'); 
            }
        }
        else if (op.includes("otros") || op.includes("retiro") || op.includes("recarga")) {
            divDesc.classList.remove('oculto');
        }
    }

    function calcularMonto() {
        let total = 0;
        
        if (pagoSelect.value === "Al contado") {
            let precio = parseFloat(precioInput.value) || 0;
            let descuento = parseFloat(descuentoInput.value) || 0;
            total = precio - descuento;
            
        } else if (pagoSelect.value === "A credito") {
            let inicial = parseFloat(inicialInput.value) || 0;
            let descuentoIni = parseFloat(descuentoIniInput.value) || 0;
            total = inicial - descuentoIni;
        }
        
        if (total < 0) total = 0; 
        
        if (pagoSelect.value === "Al contado" || pagoSelect.value === "A credito") {
            montoFinalInput.value = total.toFixed(2);
        }
    }

    opSelect.addEventListener('change', actualizarFormulario);
    modSelect.addEventListener('change', actualizarFormulario);
    pagoSelect.addEventListener('change', actualizarFormulario);
    
    precioInput.addEventListener('input', calcularMonto);
    descuentoInput.addEventListener('input', calcularMonto);
    inicialInput.addEventListener('input', calcularMonto);
    descuentoIniInput.addEventListener('input', calcularMonto);
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>