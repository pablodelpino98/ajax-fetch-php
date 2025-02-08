<?php 
session_start(); // Iniciar sesión para gestionar usuarios logueados
include 'conexion.php'; // Incluir la conexión a la base de datos

// Obtener todas las películas de la base de datos
$stmt = $pdo->query("SELECT titulo FROM peliculas");
$peliculas = $stmt->fetchAll(PDO::FETCH_ASSOC); // Guardar los resultados en un array asociativo
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pelis Pino: Index</title>
    
    <!-- Librería para iconos de Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css"> <!-- Archivo de estilos CSS -->
</head>
<body>
    <header>
        <h1>Pelis Pino</h1>
        <?php if (isset($_SESSION['usuario'])): // Si el usuario ha iniciado sesión, mostrar su nombre y botón de logout ?>
            <span>Hola, <?php echo $_SESSION['usuario']; ?></span>
            <a href="logout.php"><button>Cerrar Sesión</button></a>
        <?php else: // Si el usuario NO ha iniciado sesión, mostrar botón para iniciar sesión ?>
            <a href="login.php"><button>Iniciar Sesión</button></a>
        <?php endif; ?>
    </header>

    <main>
        <h2>Películas en Cartelera</h2>
        <div class="galeria">
            <?php foreach ($peliculas as $pelicula): 
                // Construir la ruta del archivo de la imagen de la película
                $imagen = 'media/' . $pelicula['titulo'] . '.jpg';
                
                // Si la imagen no existe, usar una imagen por defecto
                if (!file_exists($imagen)) {
                    $imagen = 'media/default.jpg';
                }
            ?>
            <div class="pelicula-item">
                <img src="<?php echo $imagen; ?>" alt="<?php echo htmlspecialchars($pelicula['titulo']); ?>">
                <p><?php echo htmlspecialchars($pelicula['titulo']); ?></p>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (!isset($_SESSION['usuario'])): // Si el usuario no ha iniciado sesión, mostrar mensaje ?>
            <div class="mensaje">
                <p>Para votar las películas, inicie sesión.</p>
                <a href="login.php"><button>Iniciar sesión</button></a>
            </div>
        <?php else: // Si el usuario ha iniciado sesión, mostrar botón para ir a la votación ?>
            <div class="mensaje">
                <a href="listado.php"><button>Votar películas</button></a>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>