<?php
session_start(); // Iniciar sesión para el usuario
include 'conexion.php'; // Incluir la conexión a la base de datos

// Verificar si ya hay una sesión iniciada
if (isset($_SESSION["usuario"])) {
    header("Location: listado.php"); // Redirigir a listado.php si ya hay una sesión activa
    exit(); // Terminar la ejecución del script para evitar que el usuario vea el formulario de login
}

// Esta es la función que validará el login
function validarLogin($usuario, $password) {
    global $pdo; // Acceder a la variable de conexión a la base de datos

    // Buscar el usuario en la base de datos
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ?");
    $stmt->execute([$usuario]); // Ejecutar la consulta para buscar al usuario
    $user = $stmt->fetch(); // Obtener el registro del usuario

    // Verificar si la contraseña es correcta
    if ($user && password_verify($password, $user["password"])) {
        $_SESSION["usuario"] = $usuario; // Iniciar la sesión con el nombre de usuario
        return json_encode(['success' => true, 'message' => 'Login exitoso']); // Respuesta de éxito
    } else {
        return json_encode(['success' => false, 'message' => 'Credenciales incorrectas']); // Respuesta de error si las credenciales no coinciden
    }
}

// Si la solicitud es AJAX, procesamos el login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuario']) && isset($_POST['password'])) {
    echo validarLogin($_POST['usuario'], $_POST['password']); // Procesar y devolver la respuesta en formato JSON
    exit(); // Detener la ejecución después de enviar la respuesta
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pelis Pino: Login</title>
    <link rel="stylesheet" href="styles.css"> <!-- Cargar archivo de estilos -->
    <script>
        // Función para manejar el login usando AJAX
        async function login(event) {
            event.preventDefault(); // Prevenir el comportamiento por defecto del formulario (recarga de página)
            const formData = new FormData(event.target); // Obtener los datos del formulario
            const response = await fetch('login.php', { method: 'POST', body: formData }); // Enviar la solicitud POST con los datos del formulario
            const result = await response.json(); // Convertir la respuesta en formato JSON

            // Si el login es exitoso, redirigir al listado de películas
            if (result.success) {
                window.location.href = 'listado.php'; // Redirigir a la página de listado de películas
            } else {
                alert(result.message); // Si el login falla, mostrar el mensaje de error
            }
        }
    </script>
</head>
<body>
    <header>
        <h1>Pelis Pino</h1>
    </header>
    <main>
        <h2>Iniciar Sesión</h2>
        <!-- Formulario de login con método AJAX -->
        <form class="login-form" onsubmit="login(event)">
            <label>Usuario:</label><br>
            <input type="text" name="usuario" required><br> <!-- Campo de texto para ingresar el usuario -->
            <label>Contraseña:</label><br>
            <input type="password" name="password" required><br><br> <!-- Campo de contraseña para ingresar la clave -->
            <input type="submit" value="Ingresar"> <!-- Botón para enviar el formulario -->
        </form>
        <br><p>¿No tienes cuenta? <a href="registro.php">Regístrate</a></p> <!-- Enlace a la página de registro si no tienen cuenta -->
    </main>
</body>
</html>