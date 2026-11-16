
<?php
require_once "../config/seguridad.php";
exigirRol("empresa");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de empresa</title>
</head>
<body>

    <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION["nombre"]); ?></h1>

    <h2>Panel de empresa</h2>

    <p>Desde aquí podrás administrar tus vacantes.</p>

    <ul>
        <li><a href="publicar_vacante.php">Publicar vacante</a></li>
        <li><a href="candidatos.php">Ver candidatos</a></li>
    </ul>

    <a href="../logout.php">Cerrar sesion</a>

</body>
</html>

<?php
require_once "../config/seguridad.php";
require_once "../config/conexion.php";

exigirRol("empresa");

$id_usuario = $_SESSION["usuario_id"];
$nombre = $_SESSION["nombre"];

// Comprobar si la empresa ya tiene un perfil
$sql = "SELECT id_empresa, nombre_empresa
        FROM empresas
        WHERE id_usuario = ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$stmt->bind_result($id_empresa, $nombre_empresa);

$existe = $stmt->fetch();
$stmt->close();

// Si no existe, crear el perfil de empresa
if (!$existe) {
    $nombre_empresa = $nombre;

    $sql = "INSERT INTO empresas (id_usuario, nombre_empresa)
            VALUES (?, ?)";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("is", $id_usuario, $nombre_empresa);
    $stmt->execute();
    $id_empresa = $stmt->insert_id;
    $stmt->close();
}

// Buscar las vacantes de esta empresa
$sql = "SELECT id_vacante, titulo, forma_trabajo, salario,
               fecha_publicacion, estado
        FROM vacantes
        WHERE id_empresa = ?
        ORDER BY fecha_publicacion DESC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_empresa);
$stmt->execute();
$stmt->bind_result(
    $id_vacante, $titulo, $forma_trabajo,
    $salario, $fecha_publicacion, $estado
);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de empresa - Worky</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <h1>Panel de empresa</h1>

    <p>Bienvenido, <?php echo htmlspecialchars($nombre_empresa); ?></p>

    <p>
        <a href="../logout.php">Cerrar sesion</a>
    </p>

    <hr>

    <h2>Administrar vacantes</h2>

    <p>
        <a href="publicar_vacante.php">Publicar una vacante</a>
    </p>

    <p>
        <a href="postulaciones.php">Ver candidatos postulados</a>
    </p>
    <h3>Mis vacantes</h3>

    <?php
    $hay_vacantes = false;

    while ($stmt->fetch()) {
        $hay_vacantes = true;
    ?>

        <div>
            <h4><?php echo htmlspecialchars($titulo); ?></h4>

            <p>Modalidad:
                <?php echo htmlspecialchars($forma_trabajo); ?>
            </p>

            <p>Salario:
                <?php echo htmlspecialchars((string)$salario); ?>
            </p>

            <p>Fecha:
                <?php echo htmlspecialchars($fecha_publicacion); ?>
            </p>

            <p>Estado:
                <?php echo htmlspecialchars($estado); ?>
            </p>
        </div>

        <hr>

    <?php
    }

    if (!$hay_vacantes) {
        echo "<p>Aun no has publicado ninguna vacante.</p>";
    }

    $stmt->close();
    ?>

</body>
</html>1