<?php

ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Cargamos las clases
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Exception/ValidationException.php';
require_once __DIR__ . '/../src/Exception/NotFoundException.php';
require_once __DIR__ . '/../src/Http/JsonResponse.php';
require_once __DIR__ . '/../src/Http/Router.php';
require_once __DIR__ . '/../src/Validation/ReservaValidator.php';
require_once __DIR__ . '/../src/Repository/ReservaRepository.php';
require_once __DIR__ . '/../src/Repository/ReservaEventoRepository.php';
require_once __DIR__ . '/../src/Service/ReservaService.php';
require_once __DIR__ . '/../src/Controller/ReservaController.php';

use App\Controller\ReservaController;
use App\Database;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;
use App\Http\JsonResponse;
use App\Http\Router;
use App\Repository\ReservaEventoRepository;
use App\Repository\ReservaRepository;
use App\Service\ReservaService;
use App\Validation\ReservaValidator;

try {
    $db = Database::getConnection();
    $service = new ReservaService(
        $db,
        new ReservaRepository($db),
        new ReservaEventoRepository($db),
        new ReservaValidator()
    );
    $controlador = new ReservaController($service);


    $router = new Router();
    $router->add('GET',   '/api/reservas',              [$controlador, 'listar']);
    $router->add('GET',   '/api/reservas/{id}',         [$controlador, 'ver']);
    $router->add('GET',   '/api/reservas/{id}/eventos', [$controlador, 'eventos']);
    $router->add('POST',  '/api/reservas',              [$controlador, 'crear']);
    $router->add('PATCH', '/api/reservas/{id}/estado',  [$controlador, 'cambiarEstado']);

    $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

} catch (ValidationException $e) {
    JsonResponse::send([
        'error'    => 'Datos no válidos',
        'detalles' => $e->getErrores(),
    ], 400);

} catch (NotFoundException $e) {
    JsonResponse::send(['error' => $e->getMessage()], 404);

} catch (Throwable $e) {
    error_log($e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
    JsonResponse::send(['error' => 'Error interno del servidor'], 500);
}