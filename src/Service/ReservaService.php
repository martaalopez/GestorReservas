<?php
declare(strict_types=1);

namespace App\Service;

use mysqli;
use Throwable;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;
use App\Repository\ReservaRepository;
use App\Repository\ReservaEventoRepository;
use App\Validation\ReservaValidator;

class ReservaService
{

public function __construct(
    private mysqli $db,
    private ReservaRepository $reservaRepo,
    private ReservaEventoRepository $eventoRepo,
    private ReservaValidator $validator,
    ) {}
    public function listar(array $filtros):array{
        return $this->reservaRepo->buscar($filtros);
    }

    public function obtener(int $id):array{
        $reserva=$this->reservaRepo->buscarPorId($id);
        if($reserva===null){
            throw new NotFoundException('Reserva no encontrada');
        }
        return $reserva;
    }
    public function eventos(int $id):array{
        $this->obtener($id);
         return $this->eventoRepo->buscarPorReserva($id);

    }
      public function crear(array $datos): array
    {
        $limpios = $this->validator->validar($datos);
        $limpios['estado'] = 'pendiente';

        $this->db->begin_transaction();
        try {
            $id = $this->reservaRepo->crear($limpios);
            $this->eventoRepo->crear($id, 'creada', 'Reserva creada en estado pendiente');
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollback();
            throw $e;
        }

        return $this->obtener($id);
    }
   public function cambiarEstado(int $id, string $nuevoEstado): array
{
  
    $reserva = $this->obtener($id);
    $estadoActual = $reserva['estado'];

    if ($nuevoEstado !== 'pendiente' && $nuevoEstado !== 'confirmada' && $nuevoEstado !== 'cancelada') {
        throw new ValidationException(['estado' => 'Estado no válido']);
    }

    if ($estadoActual === 'cancelada') {
        throw new ValidationException(['estado' => 'La reserva está cancelada y no se puede modificar']);
    }
   
    if ($nuevoEstado === $estadoActual || $nuevoEstado === 'pendiente') {
        throw new ValidationException(['estado' => 'Cambio de estado no permitido']);
    }
  
    $this->db->begin_transaction();
    try {
        $this->reservaRepo->actualizarEstado($id, $nuevoEstado);
        $this->eventoRepo->crear($id, $nuevoEstado, 'Reserva ' . $nuevoEstado);
        $this->db->commit();
    } catch (Throwable $e) {
        $this->db->rollback();
        throw $e;
    }


    return $this->obtener($id);
}
}