<?php
declare(strict_types=0);

// Configuración de errores
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/logs/error.log');
error_reporting(E_ALL);

// Cargar  archivos
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/DbConnection.php';
require_once __DIR__ . '/src/Exception/NotFoundException.php';
require_once __DIR__ . '/src/Exception/ValidationException.php';
require_once __DIR__ . '/src/Exception/BadRequestException.php';
require_once __DIR__ . '/src/Http/JsonResponse.php';
require_once __DIR__ . '/src/Http/Router.php';
require_once __DIR__ . '/src/Validation/ReservaValidator.php';
require_once __DIR__ . '/src/Repository/ReservaRepository.php';
require_once __DIR__ . '/src/Repository/ReservaEventoRepository.php';
require_once __DIR__ . '/src/Service/ReservaService.php';
require_once __DIR__ . '/src/Controller/ReservaController.php';
// Imports
use App\Controller\ReservaController;
use App\DbConnection;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;
use App\Exception\BadRequestException;
use App\Http\JsonResponse;
use App\Http\Router;
use App\Repository\ReservaEventoRepository;
use App\Repository\ReservaRepository;
use App\Service\ReservaService;
use App\Validation\ReservaValidator;

try {
    // Conexión a BD
    $db = DbConnection::getConnection();

    // Twig 
    $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/templates');
    $twig = new \Twig\Environment($loader, ['cache' => false]);

    $reservaRepo = new ReservaRepository($db);
    $eventoRepo  = new ReservaEventoRepository($db);

    $service = new ReservaService(
        $db,
        $reservaRepo,
        $eventoRepo,
        new ReservaValidator()
    );
    $controlador = new ReservaController($service);

    //  Enrutador
    $router = new Router();

     $router->add('GET', '/', function () {
        header('Location: /vistas/reservas');
        exit;
    });

    //Rutas de la API  
    $router->add('GET',   '/reservas',              [$controlador, 'listar']);
    $router->add('GET',   '/reservas/{id}',         [$controlador, 'ver']);
    $router->add('GET',   '/reservas/{id}/eventos', [$controlador, 'eventos']);
    $router->add('POST',  '/reservas',              [$controlador, 'crear']);
    $router->add('PATCH', '/reservas/{id}/estado',  [$controlador, 'cambiarEstado']);

    // Rutas de las vistas
    $router->add('GET', '/vistas/reservas', function () use ($twig, $reservaRepo) {
        $reservas = $reservaRepo->buscar([]);
        echo $twig->render('reservas/listado.html.twig', ['reservas' => $reservas]);
    });

    $router->add('GET', '/vistas/reservas/{id}', function ($id) use ($twig, $reservaRepo, $eventoRepo) {
        $reserva = $reservaRepo->buscarPorId((int) $id);
        $eventos = $eventoRepo->buscarPorReserva((int) $id);
        echo $twig->render('reservas/detalle.html.twig', [
            'reserva' => $reserva,
            'eventos' => $eventos,
        ]);
    });

        $router->dispatch(
        $_SERVER['REQUEST_METHOD'],
        parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
    );

} catch (ValidationException $e) {
    JsonResponse::send([
        'error'    => 'Datos no válidos',
        'detalles' => $e->getErrores(),
    ], 422);
} catch (BadRequestException $e) {       
    JsonResponse::send(['error' => $e->getMessage()], 400);
} catch (NotFoundException $e) {
    JsonResponse::send(['error' => $e->getMessage()], 404);
} catch (Throwable $e) {               
    error_log($e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
    JsonResponse::send(['error' => 'Error interno del servidor'], 500);
}