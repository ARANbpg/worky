
<?php
require_once "../config/seguridad.php";
exigirRol("candidato");

require_once "../config/conexion.php";

$busqueda = trim($_GET["busqueda"] ?? "");
$modalidad = $_GET["modalidad"] ?? "";

$modalidadesPermitidas = ["", "Remoto", "Hibrido", "Presencial"];

if (!in_array($modalidad, $modalidadesPermitidas, true)) {
    $modalidad = "";
}

$sql = "SELECT
            v.id_vacante,
            v.titulo,
            v.descripcion,
            v.forma_trabajo,
            v.salario,
            v.fecha_publicacion,
            e.nombre_empresa
        FROM vacantes v
        INNER JOIN empresas e
            ON v.id_empresa = e.id_empresa
        WHERE v.estado = 'Activa'
        AND (
            ? = ''
            OR v.titulo LIKE CONCAT('%', ?, '%')
            OR v.descripcion LIKE CONCAT('%', ?, '%')
        )
        AND (
            ? = ''
            OR v.forma_trabajo = ?
        )
        ORDER BY v.fecha_publicacion DESC";

$stmt = $conexion->prepare($sql);

$stmt->bind_param(
    "sssss",
    $busqueda,
    $busqueda,
    $busqueda,
    $modalidad,
    $modalidad
);

$stmt->execute();
$resultado = $stmt->get_result();

function e($valor) {
    return htmlspecialchars(
        (string)($valor ?? ""),
        ENT_QUOTES,
        "UTF-8"
    );
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Buscar empleos - Worky</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <h1>Worky - Bolsa de Empleo</h1>

    <h2>Encuentra tu proximo trabajo</h2>

    <p>
        Bienvenido,
        <?php echo e($_SESSION["nombre"]); ?>
    </p>

    <form method="GET">

        <label>Buscar empleo:</label><br>

        <input
            type="search"
            name="busqueda"
            placeholder="Puesto o descripcion"
            value="<?php echo e($busqueda); ?>"
        >

        <br><br>

        <label>Modalidad:</label><br>

        <select name="modalidad">
            <option value="">Todas</option>

            <option value="Remoto"
                <?php if ($modalidad === "Remoto") echo "selected"; ?>>
                Remoto
            </option>

            <option value="Hibrido"
                <?php if ($modalidad === "Hibrido") echo "selected"; ?>>
                Hibrido
            </option>

            <option value="Presencial"
                <?php if ($modalidad === "Presencial") echo "selected"; ?>>
                Presencial
            </option>
        </select>

        <br><br>

        <button type="submit">Buscar</button>

        <a href="vacantes.php">Limpiar filtros</a>

    </form>

    <hr>

    <h2>Vacantes disponibles</h2>

    <p>
        Resultados encontrados:
        <?php echo $resultado->num_rows; ?>
    </p>

    <?php if ($resultado->num_rows > 0): ?>

        <?php while ($vacante = $resultado->fetch_assoc()): ?>

            <div style="border:1px solid #ccc; padding:15px; margin-bottom:15px;">

                <h3><?php echo e($vacante["titulo"]); ?></h3>

                <p>
                    Empresa:
                    <?php echo e($vacante["nombre_empresa"]); ?>
                </p>

                <p>
                    Modalidad:
                    <?php echo e($vacante["forma_trabajo"]); ?>
                </p>

                <p>
                    Salario estimado:
                    <?php
                    echo $vacante["salario"] !== null
                        ? e(number_format((float)$vacante["salario"], 2))
                        : "A convenir";
                    ?>
                </p>

                <p>
                    Fecha de publicacion:
                    <?php echo e($vacante["fecha_publicacion"]); ?>
                </p>

                <p>
                    <?php
                    $descripcion = $vacante["descripcion"];
                    echo e(mb_strimwidth($descripcion, 0, 180, "...", "UTF-8"));
                    ?>
                </p>

                <a href="detalle_vacante.php?id=<?php
                    echo (int)$vacante["id_vacante"];
                ?>">
                    Ver detalles
                </a>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <p>No se encontraron vacantes disponibles.</p>

    <?php endif; ?>

    <br>

    <a href="perfil.php">Volver a mi perfil</a>

    <br><br>

    <a href="postulaciones.php">Mis postulaciones</a>

    <br><br>

    <a href="../logout.php">Cerrar sesion</a>

</body>
</html>

<?php
$stmt->close();
?>