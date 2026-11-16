
<?php
require_once "../config/seguridad.php";
exigirRol("candidato");

require_once "../config/conexion.php";

$idUsuario = $_SESSION["usuario_id"];
$mensaje = "";

//busca el perfil del candidato
$sql = "SELECT * FROM candidatos WHERE id_usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$resultado = $stmt->get_result();
$candidato = $resultado->fetch_assoc();
$stmt->close();

// Crear el perfil si todavia no existe
if (!$candidato) {
    $sql = "INSERT INTO candidatos (id_usuario) VALUES (?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $idUsuario);
    $stmt->execute();
    $stmt->close();

    $stmt = $conexion->prepare(
        "SELECT * FROM candidatos WHERE id_usuario = ?"
    );
    $stmt->bind_param("i", $idUsuario);
    $stmt->execute();
    $candidato = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Guardar los cambios del perfil
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $telefono = trim($_POST["telefono"] ?? "");
    $titulacion = trim($_POST["titulacion"] ?? "");
    $habilidades = trim($_POST["habilidades"] ?? "");
    $biografia = trim($_POST["biografia"] ?? "");

    $cv = $candidato["url_cv"] ?? "";

    // Procesar el curriculum si se selecciono uno
    if (isset($_FILES["cv"]) &&
        $_FILES["cv"]["error"] !== UPLOAD_ERR_NO_FILE) {

        if ($_FILES["cv"]["error"] !== UPLOAD_ERR_OK) {
            $mensaje = "Error al subir el archivo.";

        } elseif ($_FILES["cv"]["size"] > 5 * 1024 * 1024) {
            $mensaje = "El CV no puede superar los 5 MB.";

        } else {
            $nombreOriginal = $_FILES["cv"]["name"];
            $extension = strtolower(
                pathinfo($nombreOriginal, PATHINFO_EXTENSION)
            );

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $tipo = $finfo->file($_FILES["cv"]["tmp_name"]);

            if ($extension !== "pdf" || $tipo !== "application/pdf") {
                $mensaje = "Solo se permiten archivos PDF.";

            } else {
                $carpeta = __DIR__ . "/../uploads/cv/";

                if (!is_dir($carpeta)) {
                    mkdir($carpeta, 0755, true);
                }

                $nuevoNombre = "cv_" . bin2hex(random_bytes(16)) . ".pdf";
                $destino = $carpeta . $nuevoNombre;

                if (move_uploaded_file(
                    $_FILES["cv"]["tmp_name"],
                    $destino
                )) {
                    $cv = $nuevoNombre;
                } else {
                    $mensaje = "No se pudo guardar el CV.";
                }
            }
        }
    }

    if ($mensaje === "") {
        $sql = "UPDATE candidatos
                SET telefono = ?,
                    titulacion = ?,
                    habilidades = ?,
                    biografia = ?,
                    url_cv = ?
                WHERE id_candidato = ?";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param(
            "sssssi",
            $telefono,
            $titulacion,
            $habilidades,
            $biografia,
            $cv,
            $candidato["id_candidato"]
        );

        if ($stmt->execute()) {
            $mensaje = "Perfil actualizado correctamente.";

            $candidato["telefono"] = $telefono;
            $candidato["titulacion"] = $titulacion;
            $candidato["habilidades"] = $habilidades;
            $candidato["biografia"] = $biografia;
            $candidato["url_cv"] = $cv;
        } else {
            $mensaje = "Error al guardar el perfil.";
        }

        $stmt->close();
    }
}

function e($valor) {
    return htmlspecialchars($valor ?? "", ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi perfil - Worky</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <h1>Mi perfil profesional</h1>
     <p>
     <a href="mis_postulaciones.php">Ver mis postulaciones</a>
     </p>
    <h2>Bienvenido, <?php echo e($_SESSION["nombre"]); ?></h2>

    <?php if ($mensaje !== ""): ?>
        <p><?php echo e($mensaje); ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <label>Nombre completo:</label><br>
        <input type="text"
               value="<?php echo e($_SESSION["nombre"]); ?>"
               disabled>
        <br><br>

        <label>Correo electronico:</label><br>
        <input type="email"
               value="<?php echo e($_SESSION["email"] ?? ""); ?>"
               disabled>
        <br><br>

        <label>Telefono:</label><br>
        <input type="text"
               name="telefono"
               maxlength="20"
               value="<?php echo e($candidato["telefono"]); ?>">
        <br><br>

        <label>Estudios o titulacion:</label><br>
        <input type="text"
               name="titulacion"
               maxlength="150"
               value="<?php echo e($candidato["titulacion"]); ?>">
        <br><br>

        <label>Habilidades:</label><br>
        <textarea name="habilidades"
                  rows="4"
                  placeholder="Ejemplo: HTML, PHP, MySQL"><?php
            echo e($candidato["habilidades"]);
        ?></textarea>
        <br><br>

        <label>Sobre mi:</label><br>
        <textarea name="biografia"
                  rows="5"
                  placeholder="Escribe una breve descripcion profesional"><?php
            echo e($candidato["biografia"]);
        ?></textarea>
        <br><br>

        <label>Curriculum (PDF, maximo 5 MB):</label><br>
        <input type="file"
               name="cv"
               accept=".pdf,application/pdf">
        <br>

        <?php if (!empty($candidato["url_cv"])): ?>
            <p>Ya tienes un CV guardado. Puedes subir otro para reemplazarlo.</p>
        <?php endif; ?>

        <br>
        <button type="submit">Guardar perfil</button>

    </form>

    <br>
    <a href="vacantes.php">Buscar empleos</a>
    <br><br>
    <a href="postulaciones.php">Mis postulaciones</a>
    <br><br>
    <a href="../logout.php">Cerrar sesion</a>

</body>
</html>