<?php
session_start(); // Iniciar sesión del usuario
include 'conexion.php'; // Incluir la conexión a la base de datos

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php'); // Redirigir a login si no está autenticado
    exit();
}

// Obtener todas las películas desde la base de datos
$stmt = $pdo->query("SELECT * FROM peliculas");
$peliculas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Función para pintar estrellas y obtener el total de votos
function pintarEstrellas($pdo, $peliculaId) {
    // Obtener promedio de calificación y total de votos de la base de datos
    $stmt = $pdo->prepare("SELECT AVG(calificacion) as media, COUNT(*) as total_votos FROM votos WHERE id_pelicula = ?");
    $stmt->execute([$peliculaId]);
    $result = $stmt->fetch();

    // Redondear la media y calcular el número de estrellas llenas, medias y vacías
    $media = $result['media'] ? round($result['media'], 1) : 0;
    $totalVotos = $result['total_votos'] ?? 0;
    $fullStars = floor($media); // Estrellas llenas según la media
    $halfStar = ($media - $fullStars) >= 0.5 ? 1 : 0; // Media estrella si la diferencia es mayor o igual a 0.5
    $emptyStars = 5 - $fullStars - $halfStar; // El resto de las estrellas vacías

    // Generar el HTML para mostrar las estrellas (llenadas, medias y vacías)
    $html = str_repeat('<i class="fas fa-star"></i>', $fullStars); // Estrellas llenas
    $html .= $halfStar ? '<i class="fas fa-star-half-alt"></i>' : ''; // Media estrella si corresponde
    $html .= str_repeat('<i class="far fa-star"></i>', $emptyStars); // Estrellas vacías

    return ['html' => $html, 'totalVotos' => $totalVotos]; // Devolver el HTML generado y el total de votos
}

// Obtener el voto de un usuario para una película
function obtenerVoto($pdo, $peliculaId, $usuario) {
    // Consultar si el usuario ya ha votado para la película específica
    $stmt = $pdo->prepare("SELECT calificacion FROM votos WHERE id_pelicula = ? AND id_usuario = (SELECT id FROM usuarios WHERE usuario = ?)");
    $stmt->execute([$peliculaId, $usuario]);
    return $stmt->fetchColumn() ?? false; // Si no votó, retornar false
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pelis Pino: Listado</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"> <!-- Cargar iconos de estrellas -->
    <link rel="stylesheet" href="styles.css"> <!-- Cargar el archivo de estilos -->
    <script>
        // Función para registrar el voto de un usuario
        async function votar(peliculaId) {
            const calificacion = document.getElementById('calificacion-' + peliculaId).value; // Obtener la calificación seleccionada

            // Verificar si se ha seleccionado una calificación
            if (!calificacion) {
                alert('Por favor, selecciona una calificación.'); // Si no se seleccionó una calificación, mostrar alerta
                return;
            }

            // Enviar la calificación al servidor usando fetch y JSON
            const response = await fetch('votar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ peliculaId, calificacion }) // Enviar ID de la película y calificación al servidor
            });

            // Obtener respuesta del servidor
            const result = await response.json();

            if (result.success) {
                // Si el voto se registró correctamente, actualizar el HTML con las nuevas estrellas y el número de votos
                document.getElementById('estrellas-' + peliculaId).innerHTML = result.media.html;
                document.getElementById('votos-' + peliculaId).innerText = `Votado por ${result.media.totalVotos} usuarios`;
                alert('Voto registrado con éxito.'); // Mostrar mensaje de éxito
            } else {
                alert(result.message); // Si hubo un error, mostrar el mensaje de error
            }
        }
    </script>
</head>
<body>
    <header>
        <h1>Pelis Pino</h1>
        <?php if (isset($_SESSION['usuario'])): ?>
            <span>Hola, <?php echo htmlspecialchars($_SESSION['usuario']); ?></span> <!-- Mostrar el nombre del usuario -->
            <a href="logout.php"><button>Cerrar Sesión</button></a> <!-- Opción para cerrar sesión -->
        <?php else: ?>
            <a href="login.php"><button>Iniciar Sesión</button></a> <!-- Opción para iniciar sesión si no está autenticado -->
        <?php endif; ?>
    </header>

    <h3>Películas en Cartelera</h3>

    <table id="listado-table">
        <thead>
            <tr>
                <th>Imagen</th>
                <th>Película</th>
                <th>Puntuación de Usuarios</th>
                <th>Tu Voto</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($peliculas as $pelicula): 
                // Obtener las estrellas y el total de votos para la película
                $estrellas = pintarEstrellas($pdo, $pelicula['id']);
                // Obtener el voto del usuario, si ya ha votado
                $votoUsuario = obtenerVoto($pdo, $pelicula['id'], $_SESSION['usuario']);
                // Verificar si la imagen de la película existe en el directorio
                $imagen = 'media/' . $pelicula['titulo'] . '.jpg';
                $imagenSrc = file_exists($imagen) ? $imagen : 'media/default.jpg'; // Si no existe, usar imagen predeterminada
            ?>
                <tr>
                    <!-- Mostrar la imagen de la película, verificando si existe la imagen correspondiente -->
                    <td>
                        <img src="<?php echo $imagenSrc; ?>" alt="<?php echo htmlspecialchars($pelicula['titulo']); ?>" width="100">
                    </td>
                    <!-- Mostrar el título de la película -->
                    <td>
                        <?php echo htmlspecialchars($pelicula['titulo']); ?>
                    </td>
                    <td>
                        <!-- Mostrar las estrellas de calificación obtenidas -->
                        <div id="estrellas-<?php echo $pelicula['id']; ?>" class="estrellas">
                            <?php echo $estrellas['html']; ?>
                        </div>
                        <!-- Mostrar el total de votos registrados para la película -->
                        <div id="votos-<?php echo $pelicula['id']; ?>">
                            Votado por <?php echo $estrellas['totalVotos']; ?> usuarios
                        </div>
                    </td>
                    <td>
                        <!-- Si el usuario ya votó, mostrar el selector con su calificación actual -->
                        <label for="calificacion-<?php echo $pelicula['id']; ?>">Selecciona tu voto:</label>
                        <select id="calificacion-<?php echo $pelicula['id']; ?>" name="calificacion">
                            <!-- Opción predeterminada si el usuario no ha votado, está deshabilitada -->
                            <option value="" disabled <?php echo $votoUsuario === false ? 'selected' : ''; ?>>Selecciona...</option>

                            <!-- Generar opciones de voto (1 a 5 estrellas), y marcar como seleccionada la opción correspondiente -->
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo $votoUsuario == $i ? 'selected' : ''; ?>>
                                    <?php echo $i; ?> estrella<?php echo $i > 1 ? 's' : ''; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                        <!-- Botón para votar o actualizar el voto -->
                        <button onclick="votar(<?php echo $pelicula['id']; ?>)">
                            <?php echo $votoUsuario !== false ? 'Actualizar Voto' : 'Votar'; ?>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>