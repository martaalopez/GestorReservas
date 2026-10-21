<?php
declare(strict_types=1);

namespace App\Controller;

use App\Exception\ValidationException;
use App\Http\JsonResponse;
use App\Service\ReservaService;

class ReservaController
{
    public function __construct(private ReservaService $service) {}

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

    public function ver(int $id): void
    {
        JsonResponse::send($this->service->obtener($id));
    }

    public function eventos(int $id): void
    {
        JsonResponse::send($this->service->eventos($id));
    }

    public function crear(): void
    {
        $datos = $this->inputJson();

        JsonResponse::send($this->service->crear($datos), 201);
    }

    public function cambiarEstado(int $id): void
    {
        $datos = $this->inputJson();
        $nuevoEstado = $datos['estado'] ?? '';

        if (!is_string($nuevoEstado) || $nuevoEstado === '') {
            throw new ValidationException(['estado' => 'El campo estado es obligatorio']);
        }

        JsonResponse::send($this->service->cambiarEstado($id, $nuevoEstado));
    }

    private function inputJson(): array
    {
        $texto = file_get_contents('php://input');
        $datos = json_decode($texto, true);

        if (!is_array($datos)) {
            throw new ValidationException(['body' => 'JSON inválido']);
        }

        return $datos;
    }
}