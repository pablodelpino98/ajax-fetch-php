<?php
include 'conexion.php'; // Incluir la conexión a la base de datos

// Obtener los datos enviados en formato JSON
$data = json_decode(file_get_contents("php://input"), true); // Decodificar el JSON recibido desde la solicitud AJAX

// Comprobar si el campo "correo" está presente en los datos
if (isset($data["correo"])) {
    $correo = $data["correo"]; // Asignar el valor del correo recibido al variable $correo

    // Verificar si el correo ya existe en la base de datos
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE correo = ?"); // Preparar la consulta SQL
    $stmt->execute([$correo]); // Ejecutar la consulta con el correo proporcionado
    $emailCount = $stmt->fetchColumn(); // Obtener el número de registros con el correo especificado

    // Devolver un JSON con el resultado
    echo json_encode(["existe" => $emailCount > 0]); // Si el correo ya existe, enviar 'true'; sino, 'false'
}
?>