<?php
namespace App\Exception;

use Exception;

class NotFoundException extends Exception
{

// Excepción para cuando un recurso no existe 
    public function __construct(string $message = 'Recurso no encontrado', int $code = 404)
{
    parent::__construct($message, $code);
}
}