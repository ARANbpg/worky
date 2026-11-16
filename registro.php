<?php

require_once "config/conexion.php";

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = trim($_POST["nombre"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $rol = $_POST["rol"];

    if (empty($nombre) || empty($email) || empty($password)) {

        $mensaje = "Todos los campos son obligatorios.";

    } else {

        $password_segura = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios 
                (nombre, email, password, rol)
                VALUES (?, ?, ?, ?)";

        $stmt = $conexion->prepare($sql);

        $stmt->bind_param(
            "ssss",
            $nombre,
            $email,
            $password_segura,
            $rol
        );

        if ($stmt->execute()) {

            $mensaje = "Cuenta creada correctamente.";

        } else {

            $mensaje = "No se pudo crear la cuenta. El correo puede estar registrado.";

        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>Crear cuenta</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h1>Crear una Cuenta</h1>

    <?php if (!empty($mensaje)): ?>

        <p>
            <?php echo htmlspecialchars($mensaje); ?>
        </p>

    <?php endif; ?>


    <form method="POST">

        <label>Nombre completo:</label>
        <br>

        <input
            type="text"
            name="nombre"
            required
        >

        <br><br>


        <label>Correo electronico:</label>
        <br>

        <input
            type="email"
            name="email"
            required
        >

        <br><br>


        <label>Contraseña:</label>
        <br>

        <input
            type="password"
            name="password"
            required
        >

        <br><br>


        <label>Tipo de usuario:</label>
        <br>

        <select name="rol" required>

            <option value="candidato">
                Candidato
            </option>

            <option value="empresa">
                Empresa
            </option>

        </select>

        <br><br>


        <button type="submit">
            Crear Cuenta
        </button>

    </form>

</body>

</html>