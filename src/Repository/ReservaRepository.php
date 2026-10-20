<?php

namespace App\Repository;

use mysqli;

class ReservaRepository
{
    public function __construct(private mysqli $db) {}

    public function buscarPorId(int $id): ?array
    {
        $sql='SELECT * FROM reserva WHERE id = ?';
        $consulta = $this->db->prepare($sql);
        $consulta->execute([$id]);

        $resultado = $consulta->get_result();
        $reserva = $consulta->fetch_assoc();

        if($reserva){
            return $reserva;
        }else{
            return null;
        }
    }


public function buscarPorEstado(string $estado): array
{
    $sql='SELECT * FROM reserva WHERE estado = ?';
    $consulta = $this->db->prepare($sql);
    $consulta->execute([$estado]);

    return $consulta->get_result()->fetch_all(MYSQLI_ASSOC);
}


public function buscarPorHuesped(string $nombre): array
{
    $sql='SELECT * FROM reserva WHERE nombre_huesped LIKE ?';
    $consulta = $this->db->prepare($sql);
    $consulta->execute(['%' . $nombre . '%']);

    return $consulta->get_result()->fetch_all(MYSQLI_ASSOC);
}


public function buscarPorFechas(string $desde, string $hasta): array
{
    $sql= 'SELECT * FROM reserva WHERE fecha_entrada >= ? AND fecha_salida <= ?';
    $consulta = $this->db->prepare($sql);
    $consulta->execute([$desde, $hasta]);

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