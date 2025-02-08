<?php
include 'conexion.php'; // Incluir la conexión a la base de datos

// Obtener los datos enviados por el cliente
$data = json_decode(file_get_contents('php://input'), true); // Decodificar el JSON recibido desde la solicitud AJAX
$usuario = $data['usuario']; // Asignar el valor del nombre de usuario recibido al variable $usuario

// Comprobar si el nombre de usuario ya existe
$stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE usuario = ?"); // Preparar la consulta SQL para contar los registros con el nombre de usuario proporcionado
$stmt->execute([$usuario]); // Ejecutar la consulta pasando el nombre de usuario como parámetro
$userCount = $stmt->fetchColumn(); // Obtener el número de registros con ese nombre de usuario

// Retornar el resultado en formato JSON
echo json_encode(['existe' => $userCount > 0]); // Si el nombre de usuario ya existe, devolver 'true'; de lo contrario, 'false'
?>