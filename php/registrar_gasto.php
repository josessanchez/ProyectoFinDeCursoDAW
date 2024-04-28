<?php
session_start(); // Inicia la sesión

// Verificar si el usuario está autenticado
if (!isset($_SESSION["id_usuario"])) { // Si no hay un ID de usuario en la sesión
    header("Location: index.php"); // Redirige al usuario a la página de inicio de sesión
    exit; // Termina el script
}

// Obtener el id_usuario de la sesión
$id_usuario = $_SESSION["id_usuario"]; // Obtiene el ID de usuario de la sesión

// Verificar si se han enviado datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") { // Si se ha enviado una solicitud HTTP POST
    // Recibir los datos del formulario de registro de gastos
    $tipo_gasto = $_POST["tipo_gasto"]; // Almacena el tipo de gasto recibido del formulario
    $descripcion = $_POST["descripcion"]; // Almacena la descripción recibida del formulario
    $cantidad = $_POST["cantidad"]; // Almacena la cantidad recibida del formulario
    $fecha = $_POST["fecha"]; // Almacena la fecha recibida del formulario (ya viene en el formato correcto)

    // Conexión a la base de datos
    $servername = "localhost"; // Nombre del servidor de la base de datos
    $username = "root"; // Nombre de usuario de la base de datos
    $password = ""; // Contraseña de la base de datos
    $dbname = "gastosviajes"; // Nombre de la base de datos

    $conn = new mysqli($servername, $username, $password, $dbname); // Establece una nueva conexión a la base de datos
    if ($conn->connect_error) { // Verifica si hay un error en la conexión
        die("Error de conexión: " . $conn->connect_error); // Si hay un error en la conexión, muestra un mensaje de error y termina el script
    }

    // Verificar si el id_usuario existe en la tabla usuarios
    $stmt_verify = $conn->prepare("SELECT id FROM usuarios WHERE id = ?"); // Prepara una consulta para verificar si el ID de usuario existe
    $stmt_verify->bind_param("i", $id_usuario); // Une los parámetros a la declaración preparada
    $stmt_verify->execute(); // Ejecuta la consulta preparada
    $result_verify = $stmt_verify->get_result(); // Obtiene el resultado de la consulta

    if ($result_verify->num_rows > 0) { // Si se encontró el ID de usuario en la tabla usuarios
        // El usuario existe en la tabla usuarios, procedemos a insertar el gasto

        // Preparar y ejecutar la consulta SQL para insertar un nuevo gasto
        $stmt = $conn->prepare("INSERT INTO gastos (id_usuario, tipo_gasto, descripcion, cantidad, fecha) VALUES (?, ?, ?, ?, ?)"); // Prepara una consulta para insertar un nuevo gasto
        $stmt->bind_param("issss", $id_usuario, $tipo_gasto, $descripcion, $cantidad, $fecha); // Une los parámetros a la declaración preparada

        if ($stmt->execute()) { // Si la ejecución de la consulta es exitosa
            $gasto_registrado_exitosamente = true;
        } else {
            $gasto_registrado_exitosamente = false;
        }

        $stmt->close(); // Cierra la declaración preparada
    } else {
        echo "El usuario no existe en la base de datos."; // Si el ID de usuario no existe en la tabla usuarios, muestra un mensaje de error
    }

    $stmt_verify->close(); // Cierra la declaración preparada
    $conn->close(); // Cierra la conexión a la base de datos
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Gasto</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Russo+One&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
</head>

<body>
    
    <header class="header">
        <div class="alinearElementosHeader">
            <a class="logoHeader">
                <img src="../img/logo2.png" alt="Perfil" class="logo">
            </a>
            <a href="resumen_gastos.php?id_usuario=<?php echo $_SESSION['id_usuario']; ?>" class="header-link">
                <img src="../img/perfil.png" alt="Logo" class="perfil">
            </a>
        </div>
    </header>

    <div class="background-container">
    </div>
    <main class="main-container">
        <div class="container">
            <h2>Registrar Gasto</h2>
            <?php if (isset($gasto_registrado_exitosamente) && $gasto_registrado_exitosamente): ?>
                <p>Gasto registrado exitosamente!</p>
            <?php elseif (isset($gasto_registrado_exitosamente) && !$gasto_registrado_exitosamente): ?>
                <p>Error al registrar el gasto.</p>
            <?php endif; ?>
            <form action="registrar_gasto.php" method="POST"> <!-- Formulario para registrar un nuevo gasto -->
                <label for="tipo_gasto">Tipo de Gasto:</label><br> <!-- Campo para seleccionar el tipo de gasto -->
                <select id="tipo_gasto" name="tipo_gasto" required> <!-- Campo de selección obligatorio -->
                    <option value="Transporte">Transporte</option> <!-- Opción de tipo de gasto: Transporte -->
                    <option value="Comida">Comida</option> <!-- Opción de tipo de gasto: Comida -->
                    <option value="Alojamiento">Alojamiento</option> <!-- Opción de tipo de gasto: Alojamiento -->
                    <option value="Entretenimiento">Entretenimiento</option>
                    <!-- Opción de tipo de gasto: Entretenimiento -->
                    <option value="Compras">Compras</option> <!-- Opción de tipo de gasto: Compras -->
                </select><br><br>
                <label for="descripcion">Descripción:</label><br> <!-- Campo para ingresar la descripción del gasto -->
                <input type="text" id="descripcion" name="descripcion"><br><br>
                <label for="cantidad">Cantidad:</label><br> <!-- Campo para ingresar la cantidad del gasto -->
                <input type="number" id="cantidad" name="cantidad" min="0" step="0.01" required><br><br>
                <!-- Campo numérico obligatorio -->
                <label for="fecha">Fecha:</label><br> <!-- Campo para seleccionar la fecha del gasto -->
                <input type="date" id="fecha" name="fecha" required><br><br> <!-- Campo de fecha obligatorio -->
                <input type="submit" value="Registrar Gasto"> <!-- Botón para enviar el formulario -->
            </form>



            <br>
            <!-- Enlace para cerrar sesión -->
            <a href="cerrar_sesion.php">Cerrar sesión</a>
            <br><br>
            <?php
            // Mostrar el mensaje de éxito si el gasto se registró correctamente
            if (isset($gasto_registrado_exitosamente) && $gasto_registrado_exitosamente) {
                echo '<div id="mensaje_exito">Gasto registrado exitosamente</div>';
            }
            ?>
        </div>
    </main>
    <footer>
        <h3>Desarrollo de Aplicaciones Web</h3>
    </footer>
</body>

</html>