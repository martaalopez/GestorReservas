<?php

namespace App\Repository;

use mysqli;

class ReservaRepository
{
    public function __construct(private mysqli $db) {}

    public function buscarPorId(int $id): ?array
{
    $sql = 'SELECT * FROM reserva WHERE id = ?';
    $consulta = $this->db->prepare($sql);
    $consulta->execute([$id]);

    $resultado = $consulta->get_result();
    $reserva = $resultado->fetch_assoc();

    if ($reserva) {
        return $reserva;
    } else {
        return null;
    }
}


public function buscar(array $filtros): array
{
    $sql = 'SELECT * FROM reserva WHERE 1=1';
    $valores = [];

    if (!empty($filtros['estado'])) {
        $sql .= ' AND estado = ?';
        $valores[] = $filtros['estado'];
    }
    if (!empty($filtros['desde'])) {
        $sql .= ' AND fecha_entrada >= ?';
        $valores[] = $filtros['desde'];
    }
    if (!empty($filtros['hasta'])) {
        $sql .= ' AND fecha_salida <= ?';
        $valores[] = $filtros['hasta'];
    }
    if (!empty($filtros['huesped'])) {
        $sql .= ' AND nombre_huesped LIKE ?';
        $valores[] = '%' . $filtros['huesped'] . '%';
    }

    $sql .= ' ORDER BY fecha_creacion DESC LIMIT 100';

    $consulta = $this->db->prepare($sql);
    $consulta->execute($valores);

    return $consulta->get_result()->fetch_all(MYSQLI_ASSOC);
}

   public function crear(array $datos): int
{
    $sql='INSERT INTO reserva
            (nombre_huesped, email_huesped, nombre_alojamiento,
             fecha_entrada, fecha_salida, estado, importe, notas)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
 $consulta = $this->db->prepare($sql);
     $consulta->execute([
        $datos['nombre_huesped'],
        $datos['email_huesped'],
        $datos['nombre_alojamiento'],
        $datos['fecha_entrada'],
        $datos['fecha_salida'],
        $datos['estado'],
        $datos['importe'],
        $datos['notas'] ?? null,
     ]); 

    return $this->db->insert_id;
}

public function actualizarEstado(int $id, string $estado): void
{
    $sql ='UPDATE reserva SET estado = ? WHERE id = ?';
    $consulta = $this->db->prepare($sql);
    $consulta->execute([$estado, $id]);
}


}