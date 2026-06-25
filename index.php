<?php
session_start();

// 🛑 Lógica para CERRAR SESIÓN manualmente
if (isset($_GET['salir'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// 🔒 AQUÍ ESCRIBES LA CONTRASEÑA QUE QUIERES PEDIR
$contrasena_secreta = "chispon321"; 

$error = "";
if (isset($_POST['clave'])) {
    if ($_POST['clave'] === $contrasena_secreta) {
        $_SESSION['acceso_concedido'] = true;
    } else {
        $error = "❌ Contraseña incorrecta. Intenta de nuevo.";
    }
}

// Si no ha puesto la contraseña, mostramos la pantalla de Login y bloqueamos el resto
if (!isset($_SESSION['acceso_concedido']) || $_SESSION['acceso_concedido'] !== true) {
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Inventario M&R</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: white; padding: 40px; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: center; width: 320px; }
        .login-box h2 { color: #004a99; margin-top: 0; font-weight: 900; }
        .login-input { width: 100%; padding: 12px; margin: 15px 0; border: 1px solid #ccc; border-radius: 8px; font-size: 1rem; box-sizing: border-box; }
        .btn-login { background: #004a99; color: white; border: none; padding: 12px; width: 100%; border-radius: 8px; font-size: 1.1rem; cursor: pointer; font-weight: bold; transition: 0.3s; }
        .btn-login:hover { background: #003366; transform: scale(1.02); }
        .error { color: #d63031; font-size: 0.9rem; margin-bottom: 15px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>📱 Inventario M&R</h2>
        <p style="color: #666; font-size: 0.9rem;">Acceso Restringido al Personal</p>
        <?php if($error != "") echo "<div class='error'>$error</div>"; ?>
        <form method="POST">
            <input type="password" name="clave" class="login-input" placeholder="Ingresa la contraseña..." required>
            <button type="submit" class="btn-login">Entrar al Sistema</button>
        </form>
    </div>
</body>
</html>
<?php
    exit(); // 🛑 Este comando detiene todo y protege tu código de abajo
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario M&R</title>
    <link rel="stylesheet" href="css/style.css">
    
    <script src="https://cdn.jsdelivr.net/npm/xlsx-js-style@1.2.0/dist/xlsx.bundle.js"></script>
    
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; margin: 0; padding: 0; }
        
        .app-container { max-width: 1300px !important; margin: 0 auto; padding: 20px; } 
        
        .brand-container { 
            display: grid; 
            grid-template-columns: repeat(2, 1fr); 
            gap: 25px; 
            margin-bottom: 30px; 
        }
        @media (min-width: 550px) { .brand-container { grid-template-columns: repeat(3, 1fr); } }
        @media (min-width: 850px) { .brand-container { grid-template-columns: repeat(4, 1fr); } }
        
        .brand-btn { 
            border: none; border-radius: 25px; cursor: pointer; font-weight: 900; font-size: 1.8rem; 
            text-transform: uppercase; min-height: 160px; display: flex; align-items: center; justify-content: center;
            text-align: center; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 8px 15px rgba(0,0,0,0.15);
            color: white; text-shadow: 2px 2px 5px rgba(0,0,0,0.4); 
        }
        .brand-btn:hover { transform: translateY(-8px) scale(1.05); box-shadow: 0 15px 25px rgba(0,0,0,0.3); }
        
        .btn-volver {
            background: #e2e8f0; color: #334155; border: none; padding: 15px 20px; border-radius: 15px; 
            font-weight: 900; font-size: 1.2rem; cursor: pointer; margin-bottom: 25px; width: 100%; text-align: left; 
            transition: 0.2s; display: flex; align-items: center; gap: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .btn-volver:hover { background: #cbd5e1; transform: translateX(-5px); }

        #grid-equipos {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)) !important;
            gap: 20px !important;
            align-items: start !important; 
        }

        @media (min-width: 1100px) { #grid-equipos { grid-template-columns: repeat(4, 1fr) !important; } }

        .card-modelo { 
            background: white !important; border-radius: 16px !important; padding: 20px !important; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05) !important; display: flex !important; flex-direction: column !important;
            justify-content: space-between !important; margin-bottom: 0 !important; 
        }
        .model-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f1f1f1; padding-bottom: 15px; margin-bottom: 15px; }
        
        .stock-controls { display: flex; align-items: center; gap: 10px; background: #f8f9fa; padding: 6px 8px; border-radius: 10px; border: 1px solid #ddd; }
        .stock-controls button { width: 30px; height: 30px; border: none; background: white; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 1.2rem; box-shadow: 0 2px 5px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: center; }
        .stock-controls button:hover { background: #eee; }
        
        .price-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .price-input { width: 75px; padding: 8px; font-weight: bold; text-align: right; border: 1px solid #ccc; border-radius: 8px; font-size: 1.1rem; }
    </style>
</head>
<body>

    <div class="app-container">
        
        <!-- 🚪 BOTÓN DE CERRAR SESIÓN -->
        <div style="text-align: right; margin-bottom: -15px; margin-top: 10px;">
            <a href="?salir=true" style="background: #d63031; color: white; padding: 10px 15px; text-decoration: none; border-radius: 8px; font-weight: bold; box-shadow: 0 4px 6px rgba(0,0,0,0.1); display: inline-block; transition: 0.3s;">🚪 Cerrar Sesión</a>
        </div>

        <h1 style="text-align: center; color: #004a99; margin-bottom: 5px;">📱 Inventario M&R</h1>
        
        <div id="seccion-marcas">
            <p style="text-align: center; color: #666; margin-top: 0; margin-bottom: 25px;">Selecciona la Marca</p>
            <div id="brand-list" class="brand-container"></div>
        </div>

        <div id="seccion-modelos" style="display: none;">
            <button class="btn-volver" onclick="volverAMarcas()">⬅ Volver a Marcas</button>
            <h2 id="titulo-marca" style="color: #004a99; text-align: center; margin-bottom: 20px; font-weight: 900; font-size: 1.8rem; text-transform: uppercase;"></h2>
            <div id="grid-equipos"></div>
        </div>
    </div>

    <button class="btn-flotante" onclick="abrirModal()">➕ Agregar Equipo</button>
    <button class="btn-flotante-excel" onclick="descargarInventario()">📊 Imprimir Excel</button>

    <div id="modal-agregar" class="modal-oculto">
        <div class="modal-contenido">
            <span class="cerrar-modal" onclick="cerrarModal()">&times;</span>
            <h2 style="text-align: center; color: #004a99; margin-top: 0; font-weight: 800;">Nuevo Equipo</h2>
            
            <select id="n-marca" class="input-form" style="font-weight: bold; color: #004a99;" required>
                <option value="">-- Escoge la Marca --</option>
                <option value="HONOR">HONOR</option>
                <option value="INFINIX">INFINIX</option>
                <option value="IPHONE">IPHONE</option>
                <option value="MOTOROLA">MOTOROLA</option>
                <option value="NEO">NEO</option>
                <option value="POCO">POCO</option>
                <option value="REDMI">REDMI</option>
                <option value="SAMSUNG">SAMSUNG</option>
                <option value="ZTE">ZTE</option>
            </select>

            <input type="text" id="n-nombre" placeholder="Nombre/Modelo (ej. 200 Lite)" class="input-form">
            <input type="text" id="n-imei" placeholder="IMEI (Opcional o S/N)" class="input-form">
            
            <div style="display: flex; gap: 10px;">
                <input type="text" id="n-rom" placeholder="ROM (ej. 256GB)" class="input-form" style="width: 50%;">
                <input type="text" id="n-ram" placeholder="RAM (ej. 8GB)" class="input-form" style="width: 50%;">
            </div>
            
            <div style="display: flex; gap: 10px; margin-bottom: 5px;">
                <input type="number" id="n-precio-compra" placeholder="Costo S/" class="input-form" style="width: 100%;">
            </div>
            
            <div style="display: flex; gap: 10px;">
                <input type="number" id="n-precio" placeholder="Venta Contado S/" class="input-form" style="width: 50%; border-color:#004a99; background: #eef5ff;">
                <input type="number" id="n-precio-credito" placeholder="Venta Crédito S/" class="input-form" style="width: 50%; border-color:#e67e22; background: #fdf2e9;">
            </div>
            
            <button onclick="guardarNuevoEquipo()" class="btn-guardar">💾 Guardar en Inventario</button>
        </div>
    </div>

    <script src="js/app.js"></script>
</body>
</html>
