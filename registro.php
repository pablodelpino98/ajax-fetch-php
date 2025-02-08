<?php
session_start(); // Iniciar sesión para el usuario
include 'conexion.php'; // Incluir la conexión a la base de datos

// Verificar si ya hay una sesión iniciada
if (isset($_SESSION["usuario"])) {
    header("Location: listado.php"); // Redirigir a listado.php si ya hay una sesión activa
    exit(); // Detener la ejecución del script para evitar que el usuario vea el formulario de registro
}

// Definir variables y errores
$usuario = $correo = $password = $confirmar_password = ""; // Inicializar las variables del formulario
$usuario_error = $correo_error = $password_error = $confirmar_password_error = ""; // Inicializar los mensajes de error

// Procesar el formulario cuando se envíe
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario']; // Obtener el valor del nombre de usuario
    $correo = $_POST['correo']; // Obtener el valor del correo electrónico
    $password = $_POST['password']; // Obtener el valor de la contraseña
    $confirmar_password = $_POST['confirmar_password']; // Obtener el valor de confirmar la contraseña

    // Validar el nombre de usuario
    if (empty($usuario)) {
        $usuario_error = "El nombre de usuario es obligatorio"; // Validar que el usuario no esté vacío
    } elseif (strlen($usuario) < 5) {
        $usuario_error = "El nombre de usuario debe tener mínimo 5 caracteres"; // Validar que el usuario tenga al menos 5 caracteres
    } else {
        // Verificar si el nombre de usuario ya existe
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE usuario = ?");
        $stmt->execute([$usuario]);
        $userCount = $stmt->fetchColumn(); // Contar los usuarios que coinciden con el nombre
        if ($userCount > 0) {
            $usuario_error = "Nombre de usuario ya existente"; // Si el usuario ya existe, mostrar error
        }
    }

    // Validar correo
    if (empty($correo)) {
        $correo_error = "El correo electrónico es obligatorio"; // Validar que el correo no esté vacío
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $correo_error = "Correo electrónico no válido"; // Validar que el correo tenga el formato correcto
    } else {
        // Verificar si el correo ya existe
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE correo = ?");
        $stmt->execute([$correo]);
        $emailCount = $stmt->fetchColumn(); // Contar los correos que coinciden
        if ($emailCount > 0) {
            $correo_error = "El correo ya está en uso"; // Si el correo ya está en uso, mostrar error
        }
    }

    // Validar contraseñas
    if (empty($password)) {
        $password_error = "La contraseña es obligatoria"; // Validar que la contraseña no esté vacía
    } elseif (strlen($password) < 6) {
        $password_error = "La contraseña debe tener al menos 6 caracteres"; // Validar que la contraseña tenga mínimo 6 caracteres
    }

    if (empty($confirmar_password)) {
        $confirmar_password_error = "Debes confirmar la contraseña"; // Validar que la contraseña confirmada no esté vacía
    } elseif ($password !== $confirmar_password) {
        $confirmar_password_error = "Las contraseñas no coinciden"; // Validar que ambas contraseñas coincidan
    }

    // Si no hay errores, registrar el usuario
    if (empty($usuario_error) && empty($correo_error) && empty($password_error) && empty($confirmar_password_error)) {
        // Hashear la contraseña antes de guardarla en la base de datos
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insertar el nuevo usuario en la base de datos
        $stmt = $pdo->prepare("INSERT INTO usuarios (usuario, correo, password) VALUES (?, ?, ?)");
        $stmt->execute([$usuario, $correo, $hashed_password]);

        // Alert indicando que se ha registrado el usuario correctamente
        echo "<script>
                alert('¡Usuario registrado con éxito! Ahora puedes iniciar sesión.');
                window.location.href = 'login.php'; // Redirigir al login después del mensaje
              </script>";
        exit(); // Evitar que el script siga ejecutándose
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pelis Pino: Registro</title>
    <link rel="stylesheet" href="styles.css"> <!-- Incluir archivo de estilos -->
</head>
<body>
    <header>
        <h1>Pelis Pino</h1> <!-- Título de la página -->
    </header>

    <main>
        <h2>Registro</h2>
        <!-- Formulario de registro -->
        <form id="registro-form" method="POST" action="registro.php">
            <label>Usuario:</label>
            <input type="text" name="usuario" id="usuario" value="<?php echo $usuario; ?>"><br> <!-- Campo para el nombre de usuario -->
            <span id="usuario-error" class="error-message"><?php echo $usuario_error; ?></span><br> <!-- Mostrar mensaje de error si hay -->

            <label>Correo:</label>
            <input type="email" name="correo" id="correo" value="<?php echo $correo; ?>"><br> <!-- Campo para el correo electrónico -->
            <span id="correo-error" class="error-message"><?php echo $correo_error; ?></span><br> <!-- Mostrar mensaje de error si hay -->

            <label>Contraseña:</label>
            <input type="password" name="password" id="password" value="<?php echo $password; ?>"><br> <!-- Campo para la contraseña -->
            <span id="password-error" class="error-message"><?php echo $password_error; ?></span><br> <!-- Mostrar mensaje de error si hay -->

            <label>Confirmar Contraseña:</label>
            <input type="password" name="confirmar_password" id="confirmar_password" value="<?php echo $confirmar_password; ?>"><br> <!-- Campo para confirmar la contraseña -->
            <span id="confirmar-password-error" class="error-message"><?php echo $confirmar_password_error; ?></span><br><br> <!-- Mostrar mensaje de error si hay -->

            <input type="submit" value="Registrar"> <!-- Botón de envío -->
        </form>
    </main>

    <script>
    // Añadir eventos de validación en tiempo real
    document.getElementById("usuario").addEventListener("input", validarUsuario);
    document.getElementById("correo").addEventListener("input", validarCorreo);
    document.getElementById("password").addEventListener("input", validarContraseña);
    document.getElementById("confirmar_password").addEventListener("input", validarContraseñas);

    // Función para validar el nombre de usuario (AJAX)
    async function validarUsuario() {
        let usuario = document.getElementById('usuario').value;
        let errorDiv = document.getElementById('usuario-error');
        let submitButton = document.querySelector("input[type='submit']");

        if (usuario.length < 5) {
            errorDiv.textContent = "El nombre de usuario debe tener mínimo 5 caracteres";
            errorDiv.style.color = "red";
            submitButton.disabled = true; // Deshabilitar el botón si hay error
            return;
        }

        const response = await fetch('validar_usuario.php', { // Enviar la solicitud AJAX
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ usuario: usuario })
        });

        const result = await response.json(); // Convertir la respuesta en formato JSON

        // Si el nombre de usuario ya existe, mostrar error
        if (result.existe) {
            errorDiv.textContent = "Nombre de usuario ya existente";
            errorDiv.style.color = "red";
            submitButton.disabled = true;
        } else {
            errorDiv.textContent = "";
            submitButton.disabled = false; // Habilitar el botón si no hay error
        }
    }

    // Función para validar el correo electrónico (AJAX)
    async function validarCorreo() {
        let correo = document.getElementById('correo').value;
        let errorDiv = document.getElementById('correo-error');
        let submitButton = document.querySelector("input[type='submit']");

        if (correo.length === 0) {
            errorDiv.textContent = "";
            submitButton.disabled = false; // Habilitar el botón si no hay correo
            return;
        }

        const response = await fetch('validar_correo.php', { // Enviar la solicitud AJAX
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ correo: correo })
        });

        const result = await response.json(); // Convertir la respuesta en formato JSON

        // Si el correo ya está en uso, mostrar error
        if (result.existe) {
            errorDiv.textContent = "Correo electrónico ya en uso";
            errorDiv.style.color = "red";
            submitButton.disabled = true;
        } else {
            errorDiv.textContent = "";
            submitButton.disabled = false; // Habilitar el botón si no hay error
        }
    }

    // Función para validar la contraseña (mínimo 6 caracteres)
    function validarContraseña() {
        let password = document.getElementById('password').value;
        let errorDiv = document.getElementById('password-error');
        let submitButton = document.querySelector("input[type='submit']");

        // Verificar si la contraseña tiene al menos 6 caracteres
        if (password.length < 6) {
            errorDiv.textContent = "La contraseña debe tener al menos 6 caracteres";
            errorDiv.style.color = "red";
            submitButton.disabled = true; // Deshabilitar el botón si hay error
        } else {
            errorDiv.textContent = ""; // Si es válida, quitar el error
            submitButton.disabled = false; // Habilitar el botón si la contraseña es válida
        }
    }

    // Función para validar que las contraseñas coincidan
    function validarContraseñas() {
        let password = document.getElementById('password').value;
        let confirmar_password = document.getElementById('confirmar_password').value;
        let errorDiv = document.getElementById('confirmar-password-error');

        // Si las contraseñas no coinciden, mostrar error
        if (password !== confirmar_password) {
            errorDiv.textContent = "Las contraseñas no coinciden";
            errorDiv.style.color = "red";
        } else {
            errorDiv.textContent = ""; // Si coinciden, quitar el error
        }
    }
</script>

</body>
</html>