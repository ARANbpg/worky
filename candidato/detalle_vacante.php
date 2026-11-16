
<?php
require_once "../config/seguridad.php";
require_once "../config/conexion.php";

exigirRol("candidato");

$id_usuario = $_SESSION["usuario_id"];
$id_vacante = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id_vacante || $id_vacante < 1) {
    exit("La vacante no es valida.");
}

$mensaje = "";
$error = "";

// Buscar el perfil del candidato
$sql = "SELECT id_candidato FROM candidatos WHERE id_usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$stmt->bind_result($id_candidato);
$tiene_perfil = $stmt->fetch();
$stmt->close();

// Consultar la vacante
$sql = "SELECT v.titulo, v.descripcion, v.forma_trabajo,
               v.salario, v.fecha_publicacion, e.nombre_empresa
        FROM vacantes v
        INNER JOIN empresas e ON v.id_empresa = e.id_empresa
        WHERE v.id_vacante = ? AND v.estado = 'Activa'";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_vacante);
$stmt->execute();
$stmt->bind_result(
    $titulo, $descripcion, $modalidad,
    $salario, $fecha_publicacion, $nombre_empresa
);

$existe = $stmt->fetch();
$stmt->close();

// Procesar la postulacion
if ($_SERVER["REQUEST_METHOD"] === "POST" && $existe) {
    if (!$tiene_perfil) {
        $error = "Primero debes completar tu perfil de candidato.";
    } else {
        // Comprobar si ya se postulo
        $sql = "SELECT id_postulacion FROM postulaciones
                WHERE id_vacante = ? AND id_candidato = ?";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ii", $id_vacante, $id_candidato);
        $stmt->execute();
        $stmt->store_result();
        $ya_postulado = $stmt->num_rows > 0;
        $stmt->close();

        if ($ya_postulado) {
            $mensaje = "Ya te has postulado a esta vacante.";
        } else {
            $sql = "INSERT INTO postulaciones
                    (id_vacante, id_candidato, fecha_postulacion,
                     estado_postulacion)
                    VALUES (?, ?, CURDATE(), 'Postulado')";

            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("ii", $id_vacante, $id_candidato);

            if ($stmt->execute()) {
                $mensaje = "Tu postulacion se envio correctamente.";
            } else {
                $error = "No se pudo enviar la postulacion.";
            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalles de vacante - Worky</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <h1>Detalles de la vacante</h1>

    <p><a href="vacantes.php">Volver a las vacantes</a></p>
    <?php if ($existe) { ?>

        <h2><?php echo htmlspecialchars($titulo); ?></h2>

        <p>
            <strong>Empresa:</strong>
            <?php echo htmlspecialchars($nombre_empresa); ?>
        </p>

        <p>
            <strong>Descripcion:</strong><br>
            <?php echo nl2br(htmlspecialchars($descripcion)); ?>
        </p>

        <p>
            <strong>Modalidad:</strong>
            <?php echo htmlspecialchars($modalidad); ?>
        </p>

        <p>
            <strong>Salario:</strong>
            <?php echo htmlspecialchars((string)$salario); ?>
        </p>

        <p>
            <strong>Fecha de publicacion:</strong>
            <?php echo htmlspecialchars($fecha_publicacion); ?>
        </p>

        <hr>

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

        <?php if (!$tiene_perfil) { ?>

            <p>Debes completar tu perfil antes de postularte.</p>
            <a href="perfil.php">Completar mi perfil</a>

        <?php } elseif ($mensaje === "") { ?>

            <form method="POST" action="">
                <button type="submit">Postularme a esta vacante</button>
            </form>

        <?php } ?>

    <?php } else { ?>

        <p>La vacante no existe o ya no esta disponible.</p>

    <?php } ?>

</body>
</html>