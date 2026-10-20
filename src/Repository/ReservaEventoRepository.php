<?php

namespace App\Repository;

use mysqli;

class ReservaEventoRepository
{
    public function __construct(private mysqli $db) {}

   public function crear(int $reservaId, string $tipo, string $descripcion): void
{
    $sql = 'INSERT INTO reserva_evento (reserva_id, tipo, descripcion) VALUES (?, ?, ?)';
    $consulta = $this->db->prepare($sql);
    $consulta->execute([$reservaId, $tipo, $descripcion]);
}

    public function buscarPorReserva(int $reservaId): array
{
    $sql = 'SELECT * FROM reserva_evento
            WHERE reserva_id = ?
            ORDER BY fecha_creacion ASC, id ASC';
    $consulta = $this->db->prepare($sql);
    $consulta->execute([$reservaId]);

    return $consulta->get_result()->fetch_all(MYSQLI_ASSOC);
}
}