<?php 
namespace App\Controlller;

use mysqli;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;
use App\Validation\ReservaValidator;

class ReservaController{
    public function __construct(
        private ReservaService $service,
    ){}

    public function listar():void{
        try {
            $filtros = $_GET; 
            $reservas = $this->service->listar($filtros);
            $this->json($reservas, 200);
        } catch (Throwable $e) {
            $this->error($e);
        }
    }
    public function ver(int $id):void{
        try{
            $reservas=$this->service->obtener($id);
            $this->json($reservas,200);
        }catch(Throwable $e){
            $this->error($e);
        }
    }
    public function eventos(int $id):void{
        try{
            $reservas=$this->service->eventos($id);
            $this->json($reservas,200);           
        }catch(Throwable $e){
            $this->error($e);
        }
    }
    public function crear():void{
         try {
            $datos = $this->inputJson();
            $reserva = $this->service->crear($datos);
            $this->json($reserva, 201);
        } catch (Throwable $e) {
            $this->error($e);
        }
    }
    public function cambiarEstado(int $id): void
    {
        try {
            $datos = $this->inputJson();
            $nuevoEstado = $datos['estado'] ?? '';
            if ($nuevoEstado === '') {
                throw new ValidationException(['estado' => 'El campo estado es obligatorio']);
            }
            $reserva = $this->service->cambiarEstado($id, $nuevoEstado);
            $this->json($reserva, 200);
        } catch (Throwable $e) {
            $this->error($e);
        }
    }
private function inputJson(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw ?: '[]', true);
        if (!is_array($data)) {
            throw new ValidationException(['body' => 'JSON inválido']);
        }
        return $data;
    }

    private function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    private function error(Throwable $e): void
    {
        if ($e instanceof NotFoundException) {
            $this->json(['error' => $e->getMessage()], 404);
            return;
        }
        if ($e instanceof ValidationException) {
            // Asumiendo que ValidationException expone los errores
            $errores = method_exists($e, 'getErrores') ? $e->getErrores() : [];
            $this->json([
                'error'   => $e->getMessage(),
                'detalle' => $errores,
            ], 422);
            return;
        }
        // Error inesperado
        error_log((string) $e);
        $this->json(['error' => 'Error interno del servidor'], 500);
    }
    }
