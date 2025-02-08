<?php
// Configuración de la conexión a la base de datos
$host = 'localhost';       // Servidor de la base de datos
$dbname = 'pelis_pino';    // Nombre de la base de datos
$user = 'root';            // Usuario de la base de datos
$pass = '';                // Contraseña (vacía por defecto en XAMPP)

// Manejo de errores con try-catch
try {
    // Crear conexión inicial a MySQL sin seleccionar una base de datos
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Habilitar excepciones en errores

    // Verificar si la base de datos ya existe
    $stmt = $pdo->query("SHOW DATABASES LIKE '$dbname'");
    if ($stmt->rowCount() == 0) {
        // Si no existe, crear la base de datos con codificación UTF-8
        $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8 COLLATE utf8_general_ci");
    }

    // Conectar a la base de datos recién creada o existente
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Habilitar errores en modo excepción

    // Crear las tablas si no existen
    $sql = "
    CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,       -- ID autoincremental como clave primaria
        usuario VARCHAR(50) UNIQUE NOT NULL,     -- Nombre de usuario único y obligatorio
        correo VARCHAR(100) UNIQUE NOT NULL,     -- Correo electrónico único y obligatorio
        password VARCHAR(255) NOT NULL           -- Contraseña encriptada
    );

    CREATE TABLE IF NOT EXISTS peliculas (
        id INT AUTO_INCREMENT PRIMARY KEY,       -- ID de la película autoincremental
        titulo VARCHAR(100) NOT NULL             -- Título de la película obligatorio
    );

    CREATE TABLE IF NOT EXISTS votos (
        id INT AUTO_INCREMENT PRIMARY KEY,       -- ID autoincremental del voto
        id_usuario INT NOT NULL,                 -- ID del usuario que vota
        id_pelicula INT NOT NULL,                -- ID de la película votada
        calificacion INT CHECK(calificacion BETWEEN 1 AND 5), -- Calificación entre 1 y 5
        UNIQUE (id_usuario, id_pelicula),        -- Un usuario solo puede votar una vez por película
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE, -- Si el usuario se borra, su voto también
        FOREIGN KEY (id_pelicula) REFERENCES peliculas(id) ON DELETE CASCADE -- Si la película se borra, su voto también
    );
    ";

    // Ejecutar la creación de tablas
    $pdo->exec($sql);

    // Verificar si hay películas en la base de datos
    $stmt = $pdo->query("SELECT COUNT(*) FROM peliculas");
    $peliculasExistentes = $stmt->fetchColumn();

    if ($peliculasExistentes == 0) {
        // Si no hay películas, insertar algunas por defecto
        $sqlPeliculas = "
        INSERT INTO peliculas (titulo) VALUES
        ('Dog Man'),
        ('Mikaela'),
        ('Flow'),
        ('The Brutalist'),
        ('Sonic 3'),
        ('Mufasa'),
        ('Nosferatu'),
        ('We Live in Time');
        ";

        // Ejecutar la inserción de películas solo si la tabla estaba vacía
        $pdo->exec($sqlPeliculas);
    }

} catch (PDOException $e) {
    // Si ocurre un error, detener la ejecución y mostrar el mensaje de error
    die("Error en la conexión o ejecución SQL: " . $e->getMessage());
}
?>