
<?php
require_once "../config/seguridad.php";
require_once "../config/conexion.php";

exigirRol("empresa");

$id_usuario = $_SESSION["usuario_id"];
$mensaje = "";
$error = "";

// Buscar el perfil de la empresa
$sql = "SELECT id_empresa FROM empresas WHERE id_usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$stmt->bind_result($id_empresa);
$existe = $stmt->fetch();
$stmt->close();

if (!$existe) {
    exit("No se encontro el perfil de la empresa. Ingresa nuevamente al panel.");
}

// Guardar la vacante cuando se envia el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $titulo = trim($_POST["titulo"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");
    $modalidad = $_POST["modalidad"] ?? "";
    $salario = $_POST["salario"] ?? "";

    $modalidades_validas = ["Remoto", "Hibrido", "Presencial"];

    if ($titulo === "" || $descripcion === "" || $modalidad === "" || $salario === "") {
        $error = "Completa todos los campos.";
    } elseif (!in_array($modalidad, $modalidades_validas, true)) {
        $error = "Selecciona una modalidad valida.";
    } elseif (!is_numeric($salario) || $salario < 0) {
        $error = "Ingresa un salario valido.";
    } else {
        $salario = (float) $salario;

        $sql = "INSERT INTO vacantes
                (id_empresa, titulo, descripcion, forma_trabajo,
                 salario, fecha_publicacion, estado)
                VALUES (?, ?, ?, ?, ?, CURDATE(), 'Activa')";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param(
            "isssd",
            $id_empresa,
            $titulo,
            $descripcion,
            $modalidad,
            $salario
        );

        if ($stmt->execute()) {
            $mensaje = "La vacante se publico correctamente.";
        } else {
            $error = "Ocurrio un error al publicar la vacante.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Publicar vacante - Worky</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <h1>Publicar una vacante</h1>

    <p><a href="panel.php">Volver al panel</a></p>

    <?php if ($mensaje !== "") { ?>
        <p style="color: green;">
            <?php echo htmlspecialchars($mensaje); ?>
        </p>
    <?php } ?>

    <?php if ($error !== "") { ?>
        <p style="color: red;">
            <?php echo htmlspecialchars($error); ?>
        </p>
    <?php } ?>

    <form method="POST" action="">

        <label for="titulo">Nombre del puesto:</label><br>
        <input type="text" id="titulo" name="titulo"
               value="<?php echo htmlspecialchars($_POST["titulo"] ?? ""); ?>"
               required><br><br>

        <label for="descripcion">Descripcion del puesto:</label><br>
        <textarea id="descripcion" name="descripcion"
                  rows="6" required><?php
            echo htmlspecialchars($_POST["descripcion"] ?? "");
        ?></textarea><br><br>

        <label for="modalidad">Modalidad de trabajo:</label><br>
        <select id="modalidad" name="modalidad" required>
            <option value="">Selecciona una modalidad</option>
            <option value="Remoto">Remoto</option>
            <option value="Hibrido">Hibrido</option>
            <option value="Presencial">Presencial</option>
        </select><br><br>

        <label for="salario">Salario:</label><br>
        <input type="number" id="salario" name="salario"
               min="0" step="0.01"
               value="<?php echo htmlspecialchars($_POST["salario"] ?? ""); ?>"
               required><br><br>

        <button type="submit">Publicar vacante</button>

    </form>

</body>
</html>