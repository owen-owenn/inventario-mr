<?php 
include 'db.php'; 

// Lógica para guardar un nuevo usuario si se envía el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $rol    = mysqli_real_escape_string($conexion, $_POST['rol']);

    $sql = "INSERT INTO usuarios (nombre, rol) VALUES ('$nombre', '$rol')";
    
    if (mysqli_query($conexion, $sql)) {
        echo "<script>alert('Vendedor agregado con éxito'); window.location.href='usuarios.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conexion);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Owen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .contenido-principal { padding: 30px; }
        .card { border-radius: 15px; border: none; box-shadow: 0 8px 16px rgba(0,0,0,0.1); }
        .table thead { background-color: #212529; color: white; }
    </style>
</head>
<body>

<?php include 'menu.php'; ?>

<div class="container contenido-principal">
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card p-4">
                <h4 class="mb-3">+ Nuevo Vendedor</h4>
                <hr>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre del Vendedor:</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej: Enyeli" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Rol:</label>
                        <select name="rol" class="form-select">
                            <option value="Vendedor">Vendedor</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success w-100 shadow-sm">Registrar Vendedor</button>
                </form>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card p-4">
                <h4 class="mb-3">Vendedores Activos</h4>
                <div class="table-responsive">
                    <table class="table table-hover mt-2">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Rol</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $res = mysqli_query($conexion, "SELECT * FROM usuarios");
                            if (mysqli_num_rows($res) > 0) {
                                while($u = mysqli_fetch_assoc($res)){
                                    echo "<tr>
                                            <td><span class='text-muted'>#".$u['id']."</span></td>
                                            <td><strong>".ucfirst($u['nombre'])."</strong></td>
                                            <td><span class='badge bg-light text-dark border'>".$u['rol']."</span></td>
                                          </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3' class='text-center'>No hay usuarios registrados.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>