<?php
// Verificamos si se recibieron datos del formulario
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

    // Recibimos los datos del formulario
    $nombre = $_POST["nombre"]; // Almacena el nombre del usuario recibido del formulario
    $nombre_usuario = $_POST["nombre_usuario"]; // Almacena el nombre de usuario recibido del formulario
    $contraseña = $_POST["contraseña"]; // Almacena la contraseña recibida del formulario

    // Preparamos y ejecutamos la consulta SQL para insertar un nuevo usuario
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, nombre_usuario, contraseña) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nombre, $nombre_usuario, $contraseña); // Une los parámetros a la declaración preparada

    if ($stmt->execute()) { // Si la ejecución de la consulta es exitosa
        echo "Usuario registrado exitosamente."; // Muestra un mensaje de éxito
    } else {
        echo "Error al registrar el usuario: " . $stmt->error; // Si hay un error en la ejecución de la consulta, muestra un mensaje de error
    }

    // Cerramos la conexión y liberamos los recursos
    $stmt->close(); // Cierra la declaración preparada
    $conn->close(); // Cierra la conexión a la base de datos
} else {
    // Si no se recibieron datos del formulario, redirigimos a registro.php
    header("Location: registro.php"); // Redirige al usuario a la página de registro
    exit; // Termina el script
}
?>