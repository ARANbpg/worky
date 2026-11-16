
<?php
require_once "../config/seguridad.php";
require_once "../config/conexion.php";

exigirRol("admin");

// Verificar o quitar la verificación de una empresa
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_empresa = (int) $_POST["id_empresa"];
    $verificada = (int) $_POST["verificada"];

    if ($id_empresa > 0 && ($verificada === 0 || $verificada === 1)) {
        $sql = "UPDATE empresas
                SET verificada = ?
                WHERE id_empresa = ?";

        $stmt = $conexion->prepare($sql);

        if (!$stmt) {
            die("Error al preparar la consulta: " . $conexion->error);
        }

        $stmt->bind_param("ii", $verificada, $id_empresa);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: verificar_empresas.php");
    exit;
}

// Obtener las empresas registradas
$sql = "SELECT e.id_empresa, e.nombre_empresa, e.verificada,
               u.nombre, u.email
        FROM empresas e
        INNER JOIN usuarios u ON e.id_usuario = u.id_usuario
        ORDER BY e.id_empresa DESC";

$resultado = $conexion->query($sql);

if (!$resultado) {
    die("Error al consultar las empresas: " . $conexion->error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar empresas | Worky</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="contenedor">
        <h1>Verificar empresas</h1>
        <p>Desde aquí puedes revisar las empresas registradas en Worky.</p>

        <a href="panel.php" class="boton">Volver al panel</a>

        <br><br>

        <table>
            <thead>
                <tr>
                    <th>Empresa</th>
                    <th>Representante</th>
                    <th>Correo</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($empresa = $resultado->fetch_assoc()) { ?>
                    <tr>
                        <td>
                            <?php echo htmlspecialchars($empresa["nombre_empresa"]); ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($empresa["nombre"]); ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($empresa["email"]); ?>
                        </td>
                        <td>
                            <?php
                            echo $empresa["verificada"]
                                ? "Verificada"
                                : "Pendiente";
                            ?>
                        </td>
                        <td>
                            <form method="POST" action="verificar_empresas.php">
                                <input
                                    type="hidden"
                                    name="id_empresa"
                                    value="<?php echo (int) $empresa["id_empresa"]; ?>"
                                >

                                <input
                                    type="hidden"
                                    name="verificada"
                                    value="<?php echo $empresa["verificada"] ? 0 : 1; ?>"
                                >

                                <button type="submit" class="boton">
                                    <?php
                                    echo $empresa["verificada"]
                                        ? "Quitar verificación"
                                        : "Verificar";
                                    ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>