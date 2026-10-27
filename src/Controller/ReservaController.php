<?php
declare(strict_types=1);

namespace App\Controller;

use App\Exception\ValidationException;
use App\Http\JsonResponse;
use App\Service\ReservaService;
use App\Exception\BadRequestException;

class ReservaController
{
    public function __construct(private ReservaService $service) {}


    // Lista reservas
    public function listar(): void
    {
        $filtros = [
            'estado'  => $_GET['estado']  ?? '',
            'desde'   => $_GET['desde']   ?? '',
            'hasta'   => $_GET['hasta']   ?? '',
            'huesped' => $_GET['huesped'] ?? '',
        ];

        JsonResponse::send($this->service->listar($filtros));
    }

    // Devuelve el detalle de una reserva por su ID.
    public function ver(int $id): void
    {
        JsonResponse::send($this->service->obtener($id));
    }

    // Devuelve el historial de eventos de una reserva.
    public function eventos(int $id): void
    {
        JsonResponse::send($this->service->eventos($id));
    }

     // Crea una nueva reserva 
    public function crear(): void
    {
        $datos = $this->inputJson();

        JsonResponse::send($this->service->crear($datos), 201);
    }

    // Cambia el estado (confirmar/cancelar) de una reserva.
    public function cambiarEstado(int $id): void
    {
        $datos = $this->inputJson();
        $nuevoEstado = $datos['estado'] ?? '';

        if (!is_string($nuevoEstado) || $nuevoEstado === '') {
            throw new ValidationException(['estado' => 'El campo estado es obligatorio']);
        }

        JsonResponse::send($this->service->cambiarEstado($id, $nuevoEstado));
    }

     // Lee el JSON de entrada
    private function inputJson(): array
    {
        $texto = file_get_contents('php://input');
        $datos = json_decode($texto, true);

        if (!is_array($datos)) {
            throw new ValidationException('JSON inválido');
        }

        return $datos;
    }
}