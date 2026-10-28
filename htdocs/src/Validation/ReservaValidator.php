<?php

declare(strict_types=1); 

namespace App\Validation;

use App\Exception\ValidationException;

class ReservaValidator
{
    public function validar(array $datos): array
    {
        $errores = [];

        $nombre        = trim((string) ($datos['nombre_huesped'] ?? ''));
        $email         = trim((string) ($datos['email_huesped'] ?? ''));
        $alojamiento   = trim((string) ($datos['nombre_alojamiento'] ?? ''));
        $fecha_entrada = trim((string) ($datos['fecha_entrada'] ?? ''));
        $fecha_salida  = trim((string) ($datos['fecha_salida'] ?? ''));
        $importe       = $datos['importe'] ?? '';
        $notas         = trim((string) ($datos['notas'] ?? ''));

        if ($nombre === '') {
            $errores['nombre_huesped'] = 'El nombre es obligatorio';
        } elseif (strlen($nombre) > 100) {
            $errores['nombre_huesped'] = 'Máximo 100 caracteres';
        }

        if ($email === '') {
            $errores['email_huesped'] = 'El email es obligatorio';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores['email_huesped'] = 'Email no válido';
        }

        if ($alojamiento === '') {
            $errores['nombre_alojamiento'] = 'El alojamiento es obligatorio';
        } elseif (strlen($alojamiento) > 150) {
            $errores['nombre_alojamiento'] = 'Máximo 150 caracteres';
        }

        if (!$this->fechaValida($fecha_entrada)) {
            $errores['fecha_entrada'] = 'Fecha no válida (formato AAAA-MM-DD)';
        }

        if (!$this->fechaValida($fecha_salida)) {
            $errores['fecha_salida'] = 'Fecha no válida (formato AAAA-MM-DD)';
        }

        if ($this->fechaValida($fecha_entrada) && $this->fechaValida($fecha_salida) && $fecha_salida <= $fecha_entrada) {
            $errores['fecha_salida'] = 'La salida debe ser posterior a la entrada';
        }

        if (!is_numeric($importe) || $importe < 0) {
            $errores['importe'] = 'El importe debe ser un número mayor o igual que 0';
        }

        if (strlen($notas) > 500) {
            $errores['notas'] = 'Máximo 500 caracteres';
        }

        if (count($errores) > 0) {
            throw new ValidationException($errores);
        }

        return [
            'nombre_huesped'     => $nombre,
            'email_huesped'      => $email,
            'nombre_alojamiento' => $alojamiento,
            'fecha_entrada'      => $fecha_entrada,
            'fecha_salida'       => $fecha_salida,
            'importe'            => (float) $importe,
            'notas'              => $notas === '' ? null : $notas,
        ];
    }

    private function fechaValida(string $fecha): bool
    {
        $partes = explode('-', $fecha);

        if (count($partes) !== 3) {
            return false;
        }

        return checkdate((int) $partes[1], (int) $partes[2], (int) $partes[0]);
    }
}