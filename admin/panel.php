
<?php
require_once "../config/seguridad.php";
exigirRol("admin");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de administracion</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <h1>Panel de administracion</h1>

    <p>Bienvenido, <?php echo htmlspecialchars($_SESSION["nombre"]); ?></p>

    <ul>
        <a href="usuarios.php" class="boton">Gestionar usuarios</a>
        <a href="empresas.php" class="boton">Verificar empresas</a>
        <a href="vacantes.php" class="boton">Administrar vacantes</a>
        <li><a href="reportes.php">Gestionar reportes</a><li>
    </ul>

    <a href="../logout.php">Cerrar sesin</a>

</body>
</html>

<?php
require_once "../config/seguridad.php";
require_once "../config/conexion.php";

exigirRol("admin");

// Consultar las empresas registradas
$sql = "SELECT e.id_empresa, e.nombre_empresa, e.verificada,
               u.email
        FROM empresas e
        INNER JOIN usuarios u ON e.id_usuario = u.id_usuario
        ORDER BY e.id_empresa DESC";

$stmtEmpresas = $conexion->prepare($sql);
$stmtEmpresas->execute();
$stmtEmpresas->bind_result(
    $id_empresa, $nombre_empresa, $verificada, $email
);

// Consultar las vacantes publicadas
$sql = "SELECT v.id_vacante, v.titulo, v.forma_trabajo,
               v.fecha_publicacion, v.estado, e.nombre_empresa
        FROM vacantes v
        INNER JOIN empresas e ON v.id_empresa = e.id_empresa
        ORDER BY v.fecha_publicacion DESC";

$consulta = $conexion->prepare($sql);

if (!$consulta) {
    die("Error al preparar la consulta: " . $conexion->error);
}

$consulta->execute();
$stmtVacantes->bind_result(
    $id_vacante, $titulo, $modalidad,
    $fecha, $estado, $empresa
);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de administracion - Worky</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

    <a href="../index.php">
        <img src="../logo.png" alt="Logo de Worky" class="logo">
    </a>

    <h1>Panel de administracion</h1>

    <p>Bienvenido, administrador
        <?php echo htmlspecialchars($_SESSION["nombre"]); ?>
    </p>

    <p><a href="../logout.php">Cerrar sesion</a></p>

    <hr>

    <h2>Empresas registradas</h2>

    <?php
    $hay_empresas = false;

    while ($stmtEmpresas->fetch()) {
        $hay_empresas = true;
    ?>

        <div>
            <h3><?php echo htmlspecialchars($nombre_empresa); ?></h3>

            <p>
                <strong>Correo:</strong>
                <?php echo htmlspecialchars($email); ?>
            </p>

            <p>
                <strong>Verificacion:</strong>
                <?php
                echo ((int)$verificada === 1)
                    ? "Verificada"
                    : "Pendiente";
                ?>
            </p>
        </div>

        <hr>

    <?php
    }

    if (!$hay_empresas) {
        echo "<p>No hay empresas registradas.</p>";
    }

    $stmtEmpresas->close();
    ?>

    <h2>Vacantes publicadas</h2>

    <?php
    $hay_vacantes = false;

    while ($stmtVacantes->fetch()) {
        $hay_vacantes = true;
    ?>

        <div>
            <h3><?php echo htmlspecialchars($titulo); ?></h3>

            <p>
                <strong>Empresa:</strong>
                <?php echo htmlspecialchars($empresa); ?>
            </p>

            <p>
                <strong>Modalidad:</strong>
                <?php echo htmlspecialchars($modalidad); ?>
            </p>

            <p>
                <strong>Fecha de publicación:</strong>
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

    if (!$hay_vacantes) {
        echo "<p>No hay vacantes publicadas.</p>";
    }

    $stmtVacantes->close();
    ?>

</body>
</html>