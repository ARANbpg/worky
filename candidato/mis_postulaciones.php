
<?php
require_once "../config/seguridad.php";
require_once "../config/conexion.php";

exigirRol("candidato");

$id_usuario = $_SESSION["usuario_id"];

// Buscar el perfil del candidato
$sql = "SELECT id_candidato
        FROM candidatos
        WHERE id_usuario = ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$stmt->bind_result($id_candidato);

$tiene_perfil = $stmt->fetch();
$stmt->close();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis postulaciones - Worky</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <h1>Mis postulaciones</h1>

    <p><a href="vacantes.php">Volver a las vacantes</a></p>
    <hr>

    <?php
    if (!$tiene_perfil) {
        echo "<p>Primero debes completar tu perfil.</p>";
        echo '<a href="perfil.php">Completar perfil</a>';
    } else {

        // Consultar las postulaciones del candidato
        $sql = "SELECT v.titulo, e.nombre_empresa,
                       p.fecha_postulacion, p.estado_postulacion
                FROM postulaciones p
                INNER JOIN vacantes v
                    ON p.id_vacante = v.id_vacante
                INNER JOIN empresas e
                    ON v.id_empresa = e.id_empresa
                WHERE p.id_candidato = ?
                ORDER BY p.fecha_postulacion DESC";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id_candidato);
        $stmt->execute();

        $stmt->bind_result(
            $titulo,
            $nombre_empresa,
            $fecha,
            $estado
        );

        $hay_postulaciones = false;

        while ($stmt->fetch()) {
            $hay_postulaciones = true;
    ?>

            <div>
                <h3><?php echo htmlspecialchars($titulo); ?></h3>

                <p>
                    <strong>Empresa:</strong>
                    <?php echo htmlspecialchars($nombre_empresa); ?>
                </p>

                <p>
                    <strong>Fecha de postulacion:</strong>
                    <?php echo htmlspecialchars($fecha); ?>
                </p>

                <p>
                    <strong>Estado:</strong>
                    <?php echo htmlspecialchars($estado); ?>
                </p>
            </div>

            <hr>

    <?php
        }

        if (!$hay_postulaciones) {
            echo "<p>Aun no te has postulado a ninguna vacante.</p>";
        }

        $stmt->close();
    }
    ?>

</body>
</html>