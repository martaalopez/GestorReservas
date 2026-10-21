<?php
declare(strict_types=1);

// 1. Configuración de errores
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/error.log');
error_reporting(E_ALL);

// 2. Cargar tus archivos
require_once __DIR__ . '/../src/DbConnection.php';
require_once __DIR__ . '/../src/Exception/NotFoundException.php';
require_once __DIR__ . '/../src/Exception/ValidationException.php';
require_once __DIR__ . '/../src/Http/JsonResponse.php';
require_once __DIR__ . '/../src/Http/Router.php';
require_once __DIR__ . '/../src/Validation/ReservaValidator.php';
require_once __DIR__ . '/../src/Repository/ReservaRepository.php';
require_once __DIR__ . '/../src/Repository/ReservaEventoRepository.php';
require_once __DIR__ . '/../src/Service/ReservaService.php';
require_once __DIR__ . '/../src/Controller/ReservaController.php';

// 3. Imports
use App\Controller\ReservaController;
use App\DbConnection;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;
use App\Http\JsonResponse;
use App\Http\Router;
use App\Repository\ReservaEventoRepository;
use App\Repository\ReservaRepository;
use App\Service\ReservaService;
use App\Validation\ReservaValidator;

try {
    // 4. Conexión a BD
    $db = DbConnection::getConnection();
    
    // 5. Ensamblaje de clases
    $service = new ReservaService(
        $db,
        new ReservaRepository($db),
        new ReservaEventoRepository($db),
        new ReservaValidator()
    );
    $controlador = new ReservaController($service);

    // 6. Enrutador
    $router = new Router();
    
    $router->add('GET',   '/reservas',              [$controlador, 'listar']);
    $router->add('GET',   '/reservas/{id}',         [$controlador, 'ver']);
    $router->add('GET',   '/reservas/{id}/eventos', [$controlador, 'eventos']);
    $router->add('POST',  '/reservas',              [$controlador, 'crear']);
    $router->add('PATCH', '/reservas/{id}/estado',  [$controlador, 'cambiarEstado']);

    // 7. Despachar la petición
    $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

} catch (ValidationException $e) {
    JsonResponse::send([
        'error'    => 'Datos no válidos',
        'detalles' => $e->getErrores(),
    ], 422);
} catch (NotFoundException $e) {
    JsonResponse::send(['error' => $e->getMessage()], 404);
} catch (Throwable $e) {
    error_log($e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
    JsonResponse::send(['error' => $e->getMessage()], 500); // Muestra el error real para que podamos depurar
}