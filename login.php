
<?php
session_start();

require_once "config/conexion.php";

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (empty($email) || empty($password)) {

        $mensaje = "Completa todos los campos.";

    } else {

        $sql = "SELECT id_usuario, nombre, email, password, rol
                FROM usuarios
                WHERE email = ?
                LIMIT 1";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $stmt->bind_result(
            $id_usuario,
            $nombre,
            $emailUsuario,
            $hash,
            $rol
        );

        if ($stmt->fetch() && password_verify($password, $hash)) {

            session_regenerate_id(true);

            $_SESSION["usuario_id"] = $id_usuario;
            $_SESSION["nombre"] = $nombre;
            $_SESSION["rol"] = $rol;

            $stmt->close();

            if ($rol == "candidato") {
                header("Location: candidato/perfil.php");
            } elseif ($rol == "empresa") {
                header("Location: empresa/panel.php");
            } elseif ($rol == "admin") {
                header("Location: admin/panel.php");
            }

            exit;

        } else {

            $mensaje = "Correo o contraseña incorrectos.";

        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesion</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Iniciar sesion</h1>

    <?php if (!empty($mensaje)): ?>
        <p>
            <?php echo htmlspecialchars($mensaje); ?>
        </p>
    <?php endif; ?>

    <form method="POST">

        <label>Correo electronico:</label><br>
        <input type="email" name="email" required>

        <br><br>

        <label>Contraseña:</label><br>
        <input type="password" name="password" required>

        <br><br>

        <button type="submit">
            Iniciar sesion
        </button>

    </form>

    <p>
        ¿No tienes cuenta?
        <a href="registro.php">Registrate</a>
    </p>

    <a href="index.php">Volver al inicio</a>

</body>
</html>