<?php
session_start(); // Iniciar la sesión para acceder a los datos del usuario logueado
include 'conexion.php'; // Asegúrate de incluir la conexión a la base de datos

// Verificar si se ha enviado la petición con el método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true); // Obtener los datos enviados en formato JSON
    
    // Recoger los datos del voto desde el cuerpo de la solicitud
    $peliculaId = $data['peliculaId']; // ID de la película que está siendo votada
    $calificacion = $data['calificacion']; // Calificación que el usuario asigna a la película
    $usuario = $_SESSION['usuario']; // Obtener el usuario logueado desde la sesión

    // Verificar si el usuario ya ha votado esta película
    $stmt = $pdo->prepare("SELECT calificacion FROM votos WHERE id_pelicula = ? AND id_usuario = (SELECT id FROM usuarios WHERE usuario = ?)"); 
    $stmt->execute([$peliculaId, $usuario]); // Ejecutar la consulta para buscar si el usuario ya ha votado
    $votoExistente = $stmt->fetchColumn(); // Si hay un voto existente, se retorna la calificación

    if ($votoExistente === false) {
        // Si el usuario no ha votado aún, insertar un nuevo voto en la base de datos
        $stmt = $pdo->prepare("INSERT INTO votos (id_usuario, id_pelicula, calificacion) VALUES ((SELECT id FROM usuarios WHERE usuario = ?), ?, ?)");
        $stmt->execute([$usuario, $peliculaId, $calificacion]); // Ejecutar la consulta para registrar el voto
        $mensaje = 'Voto registrado correctamente'; // Mensaje que se enviará al cliente
    } else {
        // Si el usuario ya ha votado, actualizar el voto con la nueva calificación
        $stmt = $pdo->prepare("UPDATE votos SET calificacion = ? WHERE id_pelicula = ? AND id_usuario = (SELECT id FROM usuarios WHERE usuario = ?)");
        $stmt->execute([$calificacion, $peliculaId, $usuario]); // Ejecutar la consulta para actualizar el voto
        $mensaje = 'Voto actualizado correctamente'; // Mensaje que se enviará al cliente
    }

    // Obtener la nueva media de votos para la película
    $stmt = $pdo->prepare("SELECT AVG(calificacion) as media, COUNT(*) as total_votos FROM votos WHERE id_pelicula = ?");
    $stmt->execute([$peliculaId]); // Ejecutar la consulta para obtener la media y el número total de votos
    $result = $stmt->fetch(); // Obtener los resultados de la consulta

    // Calcular el número de estrellas basadas en la media
    $media = round($result['media'], 1); // Redondear la media de votos a 1 decimal
    $totalVotos = $result['total_votos']; // Obtener el número total de votos

    // Calcular las estrellas (llenas, medias y vacías)
    $fullStars = floor($media); // Estrellas completas (entero)
    $halfStar = ($media - $fullStars) >= 0.5 ? 1 : 0; // Verificar si hay una estrella media (0.5 o más)
    $emptyStars = 5 - $fullStars - $halfStar; // Calcular las estrellas vacías restantes

    // Construir el HTML de las estrellas
    $htmlEstrellas = ''; // Variable para almacenar el HTML de las estrellas
    for ($i = 0; $i < $fullStars; $i++) {
        $htmlEstrellas .= '<i class="fas fa-star"></i>'; // Añadir una estrella llena
    }
    if ($halfStar) {
        $htmlEstrellas .= '<i class="fas fa-star-half-alt"></i>'; // Añadir una estrella media si corresponde
    }
    for ($i = 0; $i < $emptyStars; $i++) {
        $htmlEstrellas .= '<i class="far fa-star"></i>'; // Añadir estrellas vacías
    }

    // Devolver la respuesta con la media de votos, las estrellas y el número total de votos
    echo json_encode([
        'success' => true, // Indicar que la operación fue exitosa
        'message' => $mensaje, // Mensaje con la acción realizada (registrado o actualizado)
        'media' => [
            'html' => $htmlEstrellas, // HTML de las estrellas para mostrar
            'totalVotos' => $totalVotos // Total de votos recibidos, asegurándonos que el nombre sea 'totalVotos'
        ]
    ]);
}
?>