
<?php
require_once "../config/seguridad.php";
require_once "../config/conexion.php";

exigirRol("admin");

// Consultar los usuarios registrados
$sql = "SELECT id_usuario, nombre, email, rol, fecha_registro
        FROM usuarios
        ORDER BY id_usuario DESC";

$resultado = $conexion->query($sql);

if (!$resultado) {
    die("Error al consultar los usuarios: " . $conexion->error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar usuarios | Worky</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

    <div class="contenedor">
        <h1>Gestionar usuarios</h1>
        <p>Desde aquí puedes consultar las cuentas registradas en Worky.</p>

        <a href="panel.php" class="boton">Volver al panel</a>

        <br><br>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Fecha de registro</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($usuario = $resultado->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($usuario["id_usuario"]); ?></td>
                        <td><?php echo htmlspecialchars($usuario["nombre"]); ?></td>
                        <td><?php echo htmlspecialchars($usuario["email"]); ?></td>
                        <td><?php echo htmlspecialchars($usuario["rol"]); ?></td>
                        <td><?php echo htmlspecialchars($usuario["fecha_registro"]); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

</body>
</html>