<?php
session_start(); // Inicia la sesión

// Verificar si el usuario está autenticado
if (!isset($_SESSION["id_usuario"])) { // Si no hay un ID de usuario en la sesión
    header("Location: index.php"); // Redirige al usuario a la página de inicio de sesión
    exit; // Termina el script
}

// Verificar si el usuario es el administrador
$es_administrador = isset($_SESSION["nombre_usuario"]) && $_SESSION["nombre_usuario"] === 'admin';

// Obtener el id_usuario de la sesión o de la URL si es el administrador
$id_usuario = $es_administrador ? ($_GET["id_usuario"] ?? null) : $_SESSION["id_usuario"];

// Conexión a la base de datos
$servername = "localhost"; // Nombre del servidor de la base de datos
$username = "root"; // Nombre de usuario de la base de datos
$password = ""; // Contraseña de la base de datos
$dbname = "gastosviajes"; // Nombre de la base de datos

$conn = new mysqli($servername, $username, $password, $dbname); // Establece una nueva conexión a la base de datos
if ($conn->connect_error) { // Verifica si hay un error en la conexión
    die("Error de conexión: " . $conn->connect_error); // Si hay un error en la conexión, muestra un mensaje de error y termina el script
}

// Función para obtener el nombre de usuario a partir de su ID
function obtenerNombreUsuario($conn, $id_usuario)
{
    $sql = "SELECT nombre FROM usuarios WHERE id = ?"; // Consulta SQL para obtener el nombre de usuario a partir de su ID
    $stmt = $conn->prepare($sql); // Prepara la consulta SQL
    $stmt->bind_param("i", $id_usuario); // Enlaza los parámetros de la consulta SQL
    $stmt->execute(); // Ejecuta la consulta SQL
    $result = $stmt->get_result(); // Obtiene el resultado de la consulta SQL
    if ($result->num_rows == 1) { // Si se encontró exactamente un resultado
        $row = $result->fetch_assoc(); // Obtiene la fila de resultados como un array asociativo
        return $row["nombre"]; // Retorna el nombre de usuario
    } else {
        return ""; // Si no se encontró ningún resultado, retorna una cadena vacía
    }
}

// Actualizar gasto si se ha enviado el formulario de edición
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["editar_gasto"])) { // Si se ha enviado el formulario de edición
    $id_gasto = $_POST["id_gasto"]; // Obtiene el ID del gasto a editar
    $tipo_gasto = $_POST["tipo_gasto"]; // Obtiene el tipo de gasto del formulario
    $descripcion = $_POST["descripcion"]; // Obtiene la descripción del formulario
    $cantidad = $_POST["cantidad"]; // Obtiene la cantidad del formulario
    $fecha = $_POST["fecha"]; // Obtiene la fecha del formulario

    $sql = "UPDATE gastos SET tipo_gasto=?, descripcion=?, cantidad=?, fecha=? WHERE id=?"; // Consulta SQL para actualizar el gasto en la base de datos
    $stmt = $conn->prepare($sql); // Prepara la consulta SQL
    $stmt->bind_param("ssdsi", $tipo_gasto, $descripcion, $cantidad, $fecha, $id_gasto); // Enlaza los parámetros de la consulta SQL
    $stmt->execute(); // Ejecuta la consulta SQL
}

// Borrar gasto si se ha enviado el formulario de borrado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["borrar_gasto"])) { // Si se ha enviado el formulario de borrado
    $id_gasto = $_POST["id_gasto"]; // Obtiene el ID del gasto a borrar

    $sql = "DELETE FROM gastos WHERE id=?"; // Consulta SQL para borrar el gasto de la base de datos
    $stmt = $conn->prepare($sql); // Prepara la consulta SQL
    $stmt->bind_param("i", $id_gasto); // Enlaza los parámetros de la consulta SQL
    $stmt->execute(); // Ejecuta la consulta SQL
}

// Consulta SQL para obtener los gastos
if ($es_administrador) { // Si el usuario es administrador
    if ($id_usuario === "todos") { // Si se selecciona "Todos los usuarios"
        $sql = "SELECT * FROM gastos"; // Consulta SQL para obtener todos los gastos
        $stmt = $conn->prepare($sql); // Prepara la consulta SQL
    } else { // Si se selecciona un usuario específico
        $sql = "SELECT * FROM gastos WHERE id_usuario = ?"; // Consulta SQL para obtener los gastos de un usuario específico
        $stmt = $conn->prepare($sql); // Prepara la consulta SQL
        $stmt->bind_param("i", $id_usuario); // Enlaza los parámetros de la consulta SQL
    }
} else { // Si el usuario no es administrador
    $sql = "SELECT * FROM gastos WHERE id_usuario = ?"; // Consulta SQL para obtener los gastos del usuario actual
    $stmt = $conn->prepare($sql); // Prepara la consulta SQL
    $stmt->bind_param("i", $_SESSION["id_usuario"]); // Enlaza los parámetros de la consulta SQL
}

$stmt->execute(); // Ejecuta la consulta SQL
$result = $stmt->get_result(); // Obtiene el resultado de la consulta SQL


// Define el número máximo de registros por página
$registros_por_pagina = 5;

// Consulta SQL para obtener el total de registros
$sql = "SELECT COUNT(*) as total FROM gastos";
$result = $conn->query($sql);
$total_registros = $result->fetch_assoc()['total'];
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Asegurarse de que la página actual esté dentro del rango válido
$pagina_actual = isset($_GET['pagina']) ? max(min($_GET['pagina'], $total_paginas), 1) : 1;

// Calcular el offset para la consulta SQL
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Consulta SQL para obtener los gastos paginados
if ($es_administrador) {
    if ($id_usuario === "todos") {
        $sql = "SELECT * FROM gastos LIMIT $registros_por_pagina OFFSET $offset";
    } else {
        $sql = "SELECT * FROM gastos WHERE id_usuario = ? LIMIT $registros_por_pagina OFFSET $offset";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result_gastos = $stmt->get_result();
    }
} else {
    $sql = "SELECT * FROM gastos WHERE id_usuario = ? LIMIT $registros_por_pagina OFFSET $offset";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION["id_usuario"]);
    $stmt->execute();
    $result_gastos = $stmt->get_result();
}


// Verificar si el usuario es administrador

if ($es_administrador) { // Si el usuario es administrador
    if ($id_usuario === "todos") { // Si se selecciona "Todos los usuarios"
        $sql = "SELECT * FROM gastos LIMIT $registros_por_pagina OFFSET $offset"; // Consulta SQL para obtener todos los gastos
        $stmt = $conn->prepare($sql); // Prepara la consulta SQL
        $stmt->execute(); // Ejecuta la consulta SQL
        $result = $stmt->get_result(); // Obtiene el resultado de la consulta SQL
    } else { // Si se selecciona un usuario específico
        $sql = "SELECT * FROM gastos WHERE id_usuario = ? LIMIT $registros_por_pagina OFFSET $offset"; // Consulta SQL para obtener los gastos de un usuario específico
        $stmt = $conn->prepare($sql); // Prepara la consulta SQL
        $stmt->bind_param("i", $id_usuario); // Enlaza los parámetros de la consulta SQL
        $stmt->execute(); // Ejecuta la consulta SQL
        $result = $stmt->get_result(); // Obtiene el resultado de la consulta SQL
    }
} else { // Si el usuario no es administrador
    $sql = "SELECT * FROM gastos WHERE id_usuario = ? LIMIT $registros_por_pagina OFFSET $offset"; // Consulta SQL para obtener los gastos del usuario actual
    $stmt = $conn->prepare($sql); // Prepara la consulta SQL
    $stmt->bind_param("i", $_SESSION["id_usuario"]); // Enlaza los parámetros de la consulta SQL
    $stmt->execute(); // Ejecuta la consulta SQL
    $result = $stmt->get_result(); // Obtiene el resultado de la consulta SQL
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
    <!-- Header -->
    <header class="header">
        <a>
            <img src="../img/logo2.png" alt="Logo" class="logo">
        </a>
        <?php if ($es_administrador): ?>
            <div class="admin-link">
                <a href="total_gastos_por_usuario.php">Ir a suma total de gastos por usuario</a>
            </div>
        <?php endif; ?>
    </header>

    <div class="background-container">
    </div>
    <main class="main-container">
        <!-- Contenedor principal para centrar el contenido -->
        <div class="container_resumen">
            <!-- Contenido principal -->
            <main>
                <h2>Resumen de Gastos</h2>
                <!-- Contenedor para hacer la tabla responsive -->
                <div class="table-container">
                    <div style="overflow-x:auto;"> <!-- Pone scroll horizontal en la tabla -->
                        <table>
                            <tr>
                                <th>Usuario</th>
                                <th>Tipo de Gasto</th>
                                <th>Descripción</th>
                                <th>Cantidad</th>
                                <th>Fecha</th>
                                <?php if ($es_administrador): ?>
                                    <th>Acciones</th>
                                <?php endif; ?>
                            </tr>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo obtenerNombreUsuario($conn, $row["id_usuario"]); ?></td>
                                    <td><?php echo $row["tipo_gasto"]; ?></td>
                                    <td><?php echo $row["descripcion"]; ?></td>
                                    <td><?php echo $row["cantidad"]; ?></td>
                                    <td><?php echo $row["fecha"]; ?></td>
                                    <?php if ($es_administrador || $_SESSION["id_usuario"] == $row["id_usuario"]): ?>
                                        <td>
                                            <button onclick="mostrarFormularioEditar(<?php echo $row['id']; ?>)">Editar</button>
                                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST"
                                                style="display: inline;">
                                                <input type="hidden" name="id_gasto" value="<?php echo $row["id"]; ?>">
                                                <button type="submit" name="borrar_gasto"
                                                    onclick="return confirm('¿Estás seguro de que quieres borrar este gasto?');">Borrar</button>
                                            </form>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                                <tr id="fila_editar_<?php echo $row['id']; ?>" class="hidden">
                                    <td colspan="6">
                                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                                            <input type="hidden" name="id_gasto" value="<?php echo $row["id"]; ?>">
                                            <select name="tipo_gasto">
                                                <option value="Transporte" <?php if ($row["tipo_gasto"] == "Transporte")
                                                    echo "selected"; ?>>Transporte</option>
                                                <option value="Comida" <?php if ($row["tipo_gasto"] == "Comida")
                                                    echo "selected"; ?>>Comida</option>
                                                <option value="Alojamiento" <?php if ($row["tipo_gasto"] == "Alojamiento")
                                                    echo "selected"; ?>>Alojamiento</option>
                                                <option value="Entretenimiento" <?php if ($row["tipo_gasto"] == "Entretenimiento")
                                                    echo "selected"; ?>>Entretenimiento</option>
                                                <option value="Compras" <?php if ($row["tipo_gasto"] == "Compras")
                                                    echo "selected"; ?>>Compras</option>
                                            </select>
                                            <input type="text" name="descripcion" value="<?php echo $row["descripcion"]; ?>">
                                            <input type="number" name="cantidad" value="<?php echo $row["cantidad"]; ?>">
                                            <input type="date" name="fecha" value="<?php echo $row["fecha"]; ?>">
                                            <button type="submit" name="editar_gasto">Guardar Cambios</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </table>
                    </div>
                </div>

                <!-- Agrega controles de navegación para cambiar entre páginas -->
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                        <?php if ($total_paginas > 1): ?>
                            <a href="resumen_gastos.php?pagina=<?php echo $i; ?>&id_usuario=<?php echo $id_usuario; ?>"
                                class="<?php if ($pagina_actual == $i)
                                    echo 'active'; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    <!-- Agrega enlace para registrar un nuevo gasto -->
                    <br><br>
                    <a href="registrar_gasto.php">Registrar gasto</a>
                    <br><br>
                </div>

                <?php if ($es_administrador): ?>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="GET">
                        <label for="id_usuario" class="label_mostrargastos">Mostrar gastos de:</label>
                        <select name="id_usuario" id="id_usuario">
                            <option value="todos">Todos los usuarios</option>
                            <?php
                            $sql = "SELECT id, nombre FROM usuarios";
                            $result = $conn->query($sql);
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='" . $row['id'] . "'>" . $row['nombre'] . "</option>";
                            }
                            ?>
                        </select>
                        <input type="submit" value="Filtrar">
                    </form>
                    <br>
                    <a href="cerrar_sesion.php">Cerrar Sesión de Administrador</a>
                <?php else: ?>
                    <br>
                    <a href="cerrar_sesion.php">Cerrar Sesión</a>
                <?php endif; ?>
            </main>
            <script>
                function mostrarTotalGastos() {
                    var totalGastosDiv = document.getElementById("totalGastos");
                    if (totalGastosDiv.style.display === "none") {
                        totalGastosDiv.style.display = "block";
                    } else {
                        totalGastosDiv.style.display = "none";
                    }
                }
            </script>

            <script>
                function mostrarFormularioEditar(id) {
                    document.getElementById('fila_editar_' + id).classList.toggle('hidden');
                }
            </script>
    </main>
    </div>

    <!-- Footer -->
    <footer>
        <h3>Desarrollo de Aplicaciones Web</h3>
    </footer>
</body>

</html>

<?php
$conn->close(); // Cierra la conexión a la base de datos
?>