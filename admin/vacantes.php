
<?php
require_once "../config/seguridad.php";
require_once "../config/conexion.php";

exigirRol("admin");

// Cambiar el estado de una vacante
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_vacante = (int) $_POST["id_vacante"];
    $estado = $_POST["estado"] ?? "";

    $estados_permitidos = ["Activa", "Pausada", "Inactiva"];

    if ($id_vacante > 0 && in_array($estado, $estados_permitidos, true)) {
        $sql = "UPDATE vacantes SET estado = ? WHERE id_vacante = ?";
        $stmt = $conexion->prepare($sql);

        if (!$stmt) {
            die("Error en la consulta: " . $conexion->error);
        }

        $stmt->bind_param("si", $estado, $id_vacante);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: vacantes.php");
    exit;
}

// Consultar las vacantes y las empresas que las publicaron
$sql = "SELECT v.id_vacante, v.titulo, v.forma_trabajo,
               v.salario, v.fecha_publicacion, v.estado,
               e.nombre_empresa
        FROM vacantes v
        INNER JOIN empresas e ON v.id_empresa = e.id_empresa
        ORDER BY v.id_vacante DESC";

$resultado = $conexion->query($sql);

if (!$resultado) {
    die("Error al consultar las vacantes: " . $conexion->error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar vacantes | Worky</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="contenedor">
        <h1>Administrar vacantes</h1>
        <p>Consulta las ofertas de empleo y administra su estado.</p>

        <a href="panel.php" class="boton">Volver al panel</a>

        <br><br>

        <table>
            <thead>
                <tr>
                    <th>Vacante</th>
                    <th>Empresa</th>
                    <th>Modalidad</th>
                    <th>Salario</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($vacante = $resultado->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($vacante["titulo"]); ?></td>
                        <td><?php echo htmlspecialchars($vacante["nombre_empresa"]); ?></td>
                        <td><?php echo htmlspecialchars($vacante["forma_trabajo"]); ?></td>
                        <td>
                            <?php
                            echo $vacante["salario"] !== null
                                ? htmlspecialchars($vacante["salario"])
                                : "No indicado";
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($vacante["fecha_publicacion"]); ?></td>
                        <td><?php echo htmlspecialchars($vacante["estado"]); ?></td>
                        <td>
                            <form method="POST" action="vacantes.php">
                                <input
                                    type="hidden"
                                    name="id_vacante"
                                    value="<?php echo (int) $vacante["id_vacante"]; ?>"
                                >

                                <select name="estado" required>
                                    <option value="Activa"
                                        <?php echo $vacante["estado"] === "Activa" ? "selected" : ""; ?>>
                                        Activa
                                    </option>
                                    <option value="Pausada"
                                        <?php echo $vacante["estado"] === "Pausada" ? "selected" : ""; ?>>
                                        Pausada
                                    </option>
                                    <option value="Inactiva"
                                        <?php echo $vacante["estado"] === "Inactiva" ? "selected" : ""; ?>>
                                        Inactiva
                                    </option>
                                </select>

                                <button type="submit" class="boton">
                                    Guardar
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