<?php
session_start(); // Inicia la sesión

// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION["id_usuario"]) || $_SESSION["nombre_usuario"] !== 'admin') {
    header("Location: index.php"); // Redirige al usuario a la página de inicio de sesión si no es un administrador
    exit; // Termina el script
}

// Conexión a la base de datos
$servername = "localhost"; // Nombre del servidor de la base de datos
$username = "root"; // Nombre de usuario de la base de datos
$password = ""; // Contraseña de la base de datos
$dbname = "gastosviajes"; // Nombre de la base de datos

$conn = new mysqli($servername, $username, $password, $dbname); // Establece una nueva conexión a la base de datos
if ($conn->connect_error) { // Verifica si hay un error en la conexión
    die("Error de conexión: " . $conn->connect_error); // Si hay un error en la conexión, muestra un mensaje de error y termina el script
}

// Consulta SQL para obtener el total de gastos por usuario
$sql = "SELECT usuarios.nombre_usuario, SUM(gastos.cantidad) AS total_gastos
        FROM usuarios
        INNER JOIN gastos ON usuarios.id = gastos.id_usuario
        GROUP BY usuarios.id, usuarios.nombre_usuario
        ORDER BY total_gastos DESC";

// Ejecuta la consulta SQL
$result = $conn->query($sql);

// Verifica si la consulta se ejecutó correctamente
if (!$result) {
    die("Error al ejecutar la consulta: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen de Gastos</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Russo+One&family=Tilt+Neon&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
</head>

<body>
    <div class="background-container">
    </div>

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

    <div class="background-container"></div>
    <main class="main-container">
        <div class="container_resumen">
            <table>
                <tr>
                    <th>Usuario</th>
                    <th>Total gastos</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['nombre_usuario']; ?></td>
                        <td><?php echo $row['total_gastos']; ?> euros</td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </main>

    <footer>
        <h3>Desarrollo de Aplicaciones Web</h3>
    </footer>
</body>

</html>

<?php
$conn->close(); // Cierra la conexión a la base de datos
?>
