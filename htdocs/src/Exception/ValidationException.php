<?php
namespace App\Exception;

use Exception;

class ValidationException extends Exception
{
    private array $errores;

    public function __construct(array $errores)
    {
        parent::__construct('Error de validación');
        $this->errores = $errores;
    }

    public function getErrores(): array
    {
        return $this->errores;
    }
}