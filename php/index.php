<?php
session_start(); // Inicia la sesión

$error_message = ""; // Variable para almacenar mensajes de error

// Verificar si se han enviado datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $servername = "localhost"; // Nombre del servidor de la base de datos
    $username = "root"; // Nombre de usuario de la base de datos
    $password = ""; // Contraseña de la base de datos
    $dbname = "gastosviajes"; // Nombre de la base de datos

    $conn = new mysqli($servername, $username, $password, $dbname); // Establece una nueva conexión a la base de datos
    if ($conn->connect_error) { // Verifica si hay un error en la conexión
        die("Error de conexión: " . $conn->connect_error); // Si hay un error en la conexión, muestra un mensaje de error y termina el script
    }

    $nombre_usuario = $_POST["nombre_usuario"]; // Almacena el nombre de usuario recibido del formulario
    $contraseña = $_POST["contraseña"]; // Almacena la contraseña recibida del formulario

    // Preparamos y ejecutamos la consulta SQL para obtener la información del usuario
    $stmt = $conn->prepare("SELECT id, nombre, nombre_usuario, contraseña FROM usuarios WHERE nombre_usuario = ?");
    $stmt->bind_param("s", $nombre_usuario); // Une los parámetros a la declaración preparada
    $stmt->execute(); // Ejecuta la consulta preparada
    $result = $stmt->get_result(); // Obtiene el resultado de la consulta

    if ($result->num_rows == 1) { // Verifica si se encontró un usuario con el nombre de usuario proporcionado
        $row = $result->fetch_assoc(); // Obtiene el resultado de la consulta como un arreglo asociativo
        if ($contraseña == $row["contraseña"]) { // Verifica si la contraseña proporcionada coincide con la contraseña almacenada en la base de datos
            $_SESSION["id_usuario"] = $row["id"]; // Establece el ID de usuario en la sesión
            $_SESSION["nombre_usuario"] = $row["nombre_usuario"]; // Establece el nombre de usuario en la sesión
            $_SESSION["nombre"] = $row["nombre"]; // Establece el nombre en la sesión

            // Verificar si el usuario es administrador
            if ($nombre_usuario === 'admin' && $contraseña === 'admin1234') { // Si el usuario es administrador
                header("Location: resumen_gastos.php"); // Redirige al usuario a la página de resumen de gastos
                exit; // Termina el script
            } else {
                header("Location: registrar_gasto.php"); // Si no es administrador, redirige al usuario a la página de registro de gastos
                exit; // Termina el script
            }
        } else {
            $error_message = "La contraseña es incorrecta"; // Si la contraseña no coincide, establece un mensaje de error
        }
    } else {
        $error_message = "El nombre de usuario no existe"; // Si no se encuentra el nombre de usuario, establece un mensaje de error
    }

    $stmt->close(); // Cierra la declaración preparada
    $conn->close(); // Cierra la conexión a la base de datos
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Agregar fuente externa de google fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Russo+One&family=Tilt+Neon&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">

</head>

<body>
    <div class="background-container">
        <div class="logo-container">
            <img src="../img/logo.png" alt="Logo" class="logo"> <!-- Agrega la imagen del logo encima del formulario -->
        </div>
    </div>

    <div class="container">
        <form action="index.php" method="POST"> <!-- Formulario de inicio de sesión -->
            <h2>Iniciar sesión</h2> <!-- Título dentro del formulario -->
            <?php if (!empty($error_message)): ?> <!-- Muestra un mensaje de error si existe -->
                <p class="error"><?php echo $error_message; ?></p>
            <?php endif; ?>
            <label for="nombre_usuario" class="label-nombreUsuario">Nombre de Usuario:</label> <!-- Campo para ingresar el nombre de usuario -->
            <input type="text" id="nombre_usuario" name="nombre_usuario" required><br><br>
            <label for="contraseña" class="label-contraseña">Contraseña:</label> <!-- Campo para ingresar la contraseña -->
            <input type="password" id="contraseña" name="contraseña" required><br><br>
            <input type="submit" value="Iniciar sesión"> <!-- Botón para enviar el formulario -->
        </form>
        <div class="register-link">
            <p>¿No tienes una cuenta? <a href="registro.php">Regístrate</a></p>
        </div>
    </div>
</body>

</html>