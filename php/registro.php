<?php
session_start(); // Inicia la sesión

// Verificar si se han enviado datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Conexión a la base de datos
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "gastosviajes";

    $conn = new mysqli($servername, $username, $password, $dbname); // Establece una nueva conexión a la base de datos
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error); // Si hay un error en la conexión, muestra un mensaje de error y termina el script
    }

    // Recibimos los datos del formulario de registro de usuarios
    $nombre = $_POST["nombre"];
    $nombre_usuario = $_POST["nombre_usuario"];
    $contraseña = $_POST["contraseña"];

    // Preparamos y ejecutamos la consulta SQL para insertar un nuevo usuario
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, nombre_usuario, contraseña) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nombre, $nombre_usuario, $contraseña); // Une los parámetros a la declaración preparada

    if ($stmt->execute()) {
        // Si el registro es exitoso, redirigimos a registrar_gasto.php
        $_SESSION["id_usuario"] = $conn->insert_id; // Obtén el ID del usuario recién registrado y lo establece en la sesión
        $_SESSION["nombre_usuario"] = $nombre_usuario; // Establece el nombre de usuario en la sesión
        $_SESSION["nombre"] = $nombre; // Establece el nombre en la sesión
        header("Location: registrar_gasto.php"); // Redirige al usuario a la página de registro de gastos
        exit; // Termina el script
    } else {
        $error_message = "Error al registrar el usuario: " . $stmt->error; // Establece un mensaje de error si hay un problema en el registro
    }

    // Cerramos la conexión y liberamos los recursos
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
    <div class="background-container"></div>

    <div class="container">
        <h2>Registro de Usuarios</h2>
        <?php if (isset($error_message))
            echo "<p>$error_message</p>"; ?> <!-- Muestra un mensaje de error si existe -->
        <form action="registro.php" method="POST"> <!-- Formulario de registro -->
            <label for="nombre">Nombre:</label><br> <!-- Campo para ingresar el nombre -->
            <input type="text" id="nombre" name="nombre" required><br><br>
            <label for="nombre_usuario">Nombre de usuario:</label><br> <!-- Campo para ingresar el nombre de usuario -->
            <input type="text" id="nombre_usuario" name="nombre_usuario" required><br><br>
            <label for="contraseña">Contraseña:</label><br> <!-- Campo para ingresar la contraseña -->
            <input type="password" id="contraseña" name="contraseña" required><br><br>
            <input type="submit" value="Registrarse"> <!-- Botón para enviar el formulario -->
        </form>
        <p>¿Ya tienes una cuenta? <a href="index.php">Inicia sesión aquí</a>.</p>
        <!-- Enlace para iniciar sesión si ya se tiene una cuenta -->

    </div>
</body>

</html>