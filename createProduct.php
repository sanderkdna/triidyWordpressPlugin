<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
include 'includes/syncProduct.php';

// Establecer la cabecera para indicar que se enviará una respuesta JSON
header('Content-Type: application/json');

// Comprobar el método de la solicitud HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Obtener la ruta de la solicitud HTTP
$request = $_SERVER['REQUEST_URI'];

// Ruta base de la API
$base = '/wp-content/plugins/Triidy_Automation/createProduct.php';

// Ruta de ejemplo para obtener datos
$route = $base;

// Comprobar si la ruta coincide con la ruta de ejemplo
if ($request === $route) {
    // Obtener los datos enviados en el cuerpo de la solicitud
    $datosEntrada = json_decode(file_get_contents('php://input'), true);
    // Procesar los datos recibidos y guardarlos en la base de datos o realizar cualquier otra operación necesaria
    // ...
 	$sync = new syncProduct();
	$x = $sync->execute($datosEntrada);
    
	if ($x) {
    	responderExito(200, $x);
	}else{
		responderError(500, $x);
	}
    // Devolver una respuesta de éxito
} else {
    // Ruta no encontrada
    responderError(404, 'Ruta no encontrada');
}

// Función para devolver una respuesta de éxito
function responderExito($codigo, $mensaje) {
    http_response_code($codigo);
    echo json_encode(array('estado' => 'éxito', 'mensaje' => $mensaje, 'DateTime' => date('Y-m-d H:i:s')));
    exit;
}

// Función para devolver una respuesta de error
function responderError($codigo, $mensaje) {
    http_response_code($codigo);
    echo json_encode(array('estado' => 'error', 'mensaje' => $mensaje, 'DateTime' => date('Y-m-d H:i:s')));
    exit;
}



