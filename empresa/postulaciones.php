
<?php
require_once "../config/seguridad.php";
require_once "../config/conexion.php";

exigirRol("empresa");

$id_usuario = $_SESSION["usuario_id"];
$mensaje = "";
$error = "";

// Buscar el ID de la empresa
$sql = "SELECT id_empresa FROM empresas WHERE id_usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$stmt->bind_result($id_empresa);
$existe = $stmt->fetch();
$stmt->close();

if (!$existe) {
    exit("No se encontro el perfil de la empresa.");
}

// Actualizar el estado de una postulacion
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_postulacion = filter_input(
        INPUT_POST, "id_postulacion", FILTER_VALIDATE_INT
    );
    $nuevo_estado = $_POST["estado"] ?? "";

    $estados_validos = [
        "Postulado",
        "En Revision",
        "Entrevista",
        "Rechazado"
    ];

    if (!$id_postulacion || $id_postulacion < 1 ||
        !in_array($nuevo_estado, $estados_validos, true)) {
        $error = "Los datos enviados no son validos.";
    } else {
        // Solo permite modificar postulaciones de esta empresa
        $sql = "UPDATE postulaciones p
                INNER JOIN vacantes v
                    ON p.id_vacante = v.id_vacante
                SET p.estado_postulacion = ?
                WHERE p.id_postulacion = ?
                  AND v.id_empresa = ?";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param(
            "sii", $nuevo_estado, $id_postulacion, $id_empresa
        );

        if ($stmt->execute() && $stmt->affected_rows >= 0) {
            $mensaje = "Se actualizo el estado de la postulacion.";
        } else {
            $error = "No se pudo actualizar el estado.";
        }

        $stmt->close();
    }
}

// Consultar las postulaciones de esta empresa
$sql = "SELECT v.titulo, u.nombre, u.email,
               p.fecha_postulacion, p.estado_postulacion,
               p.id_postulacion
        FROM postulaciones p
        INNER JOIN vacantes v ON p.id_vacante = v.id_vacante
        INNER JOIN candidatos c ON p.id_candidato = c.id_candidato
        INNER JOIN usuarios u ON c.id_usuario = u.id_usuario
        WHERE v.id_empresa = ?
        ORDER BY p.fecha_postulacion DESC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_empresa);
$stmt->execute();
$stmt->bind_result(
    $titulo, $nombre, $email,
    $fecha, $estado, $id_postulacion
);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Postulaciones - Worky</title>
   <link rel="stylesheet" href="../style.css">
</head>
<body>
    <h1>Candidatos postulados</h1>

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

    <hr>

    <?php
    $hay_postulaciones = false;

    while ($stmt->fetch()) {
        $hay_postulaciones = true;
    ?>

        <div>
            <h3><?php echo htmlspecialchars($titulo); ?></h3>

            <p>
                <strong>Candidato:</strong>
                <?php echo htmlspecialchars($nombre); ?>
            </p>

            <p>
                <strong>Correo:</strong>
                <?php echo htmlspecialchars($email); ?>
            </p>

            <p>
                <strong>Fecha de postulacion:</strong>
                <?php echo htmlspecialchars($fecha); ?>
            </p>

            <p>
                <strong>Estado actual:</strong>
                <?php echo htmlspecialchars($estado); ?>
            </p>

            <form method="POST" action="">
                <input type="hidden" name="id_postulacion"
                       value="<?php echo (int)$id_postulacion; ?>">

                <label>Actualizar estado:</label><br>

                <select name="estado" required>
                    <?php
                    $estados = [
                        "Postulado",
                        "En Revision",
                        "Entrevista",
                        "Rechazado"
                    ];

                    foreach ($estados as $opcion) {
                        $seleccionado = ($estado === $opcion)
                            ? "selected" : "";

                        echo '<option value="' .
                            htmlspecialchars($opcion) . '" ' .
                            $seleccionado . '>' .
                            htmlspecialchars($opcion) .
                            '</option>';
                    }
                    ?>
                </select>

                <button type="submit">Guardar estado</button>
            </form>
        </div>

        <hr>

    <?php
    }

    if (!$hay_postulaciones) {
        echo "<p>Aun no hay candidatos postulados a tus vacantes.</p>";
    }

    $stmt->close();
    ?>

</body>
</html>