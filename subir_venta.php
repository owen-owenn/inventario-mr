<?php
session_start();
header('Cache-Control: no-store');
if (empty($_SESSION['ventas_csrf'])) {
    $_SESSION['ventas_csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['ventas_csrf'];
$solicitud = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Subir venta - Inventario M&R</title>
<link rel="stylesheet" href="css/ventas.css">
<script src="js/ventas.js" defer></script>
</head>
<body>
<main>
    <a class="volver" href="index.php">← Volver al inicio</a>
    <header><p class="marca">INVENTARIO M&R</p><h1>Subir venta</h1><p>Registra un equipo vendido. El stock se descontará de la tienda seleccionada.</p></header>
    <p id="mensaje" role="status" aria-live="polite">Cargando equipos del inventario…</p>
    <button type="button" id="recargar" hidden>Reintentar carga</button>
    <form id="venta-form">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="solicitud" value="<?= htmlspecialchars($solicitud, ENT_QUOTES, 'UTF-8') ?>">
        <fieldset id="campos" disabled>
            <section>
                <h2>Persona que realizó la venta</h2>
                <label>Vendedor<select name="vendedor" id="vendedor" required>
                    <option value="">Selecciona al vendedor</option>
                    <option value="Jordy">Jordy</option>
                    <option value="Yeisom">Yeisom</option>
                    <option value="Zinedin">Zinedin</option>
                    <option value="Adriano">Adriano</option>
                    <option value="Diana">Diana</option>
                    <option value="Katherine">Katherine</option>
                    <option value="Nicol">Nicol</option>
                </select></label>
            </section>
            <section>
                <h2><span>1</span> Datos del cliente</h2>
                <div class="grid">
                    <label>DNI<input name="dni" inputmode="numeric" pattern="[0-9]{8}" minlength="8" maxlength="8" placeholder="8 dígitos" required></label>
                    <label>Número de teléfono<input name="telefono" type="tel" inputmode="numeric" pattern="9[0-9]{8}" minlength="9" maxlength="9" placeholder="9 dígitos, empieza con 9" required></label>
                </div>
            </section>
            <section>
                <h2><span>2</span> Forma de venta</h2>
                <label>Tipo de venta<select id="tipo" name="tipo" required><option value="">Selecciona una opción</option><option value="contado">Contado</option><option value="credito">Crédito</option></select></label>
                <div id="contado" hidden class="grid">
                    <label>Precio al contado (S/)<input name="precio_contado" type="number" min="0.01" max="99999999.99" step="0.01" disabled></label>
                    <label>Descuento (S/) · opcional<input name="descuento_contado" type="number" min="0" max="99999999.99" step="0.01" placeholder="0.00" disabled></label>
                </div>
                <div id="credito" hidden class="grid">
                    <label>Empresa de financiamiento<input name="financiera" maxlength="100" placeholder="Ej. PayJoy" disabled></label>
                    <label>Precio crédito (S/)<input name="precio_credito" type="number" min="0.01" max="99999999.99" step="0.01" disabled></label>
                    <label>Inicial (S/)<input name="inicial" type="number" min="0" max="99999999.99" step="0.01" disabled></label>
                    <label>Descuento de la inicial (S/) · opcional<input name="descuento_inicial" type="number" min="0" max="99999999.99" step="0.01" placeholder="0.00" disabled></label>
                </div>
                <p id="total" class="total" aria-live="polite">Selecciona contado o crédito.</p>
            </section>
            <section>
                <h2><span>3</span> Equipo vendido</h2>
                <div class="grid">
                    <label>Marca<select id="marca" required><option value="">Selecciona la marca</option></select></label>
                    <label>Equipo<select id="equipo" name="id" required><option value="">Selecciona primero la marca</option></select></label>
                    <label>IMEI del equipo vendido<input name="imei" inputmode="numeric" pattern="[0-9]{15}" minlength="15" maxlength="15" placeholder="Ingresa los 15 dígitos del equipo" required></label>
                    <label>Tienda de salida<select id="tienda" name="columna" required><option value="">Selecciona primero el equipo</option></select></label>
                </div>
                <p class="ayuda">Cada venta descuenta 1 unidad. Solo puedes vender equipos con stock en la tienda elegida.</p>
            </section>
            <button type="submit" id="guardar">Guardar venta y descontar stock</button>
        </fieldset>
    </form>
    <a id="nueva" class="boton" href="subir_venta.php" hidden>Registrar otra venta</a>
</main>
</body>
</html>
