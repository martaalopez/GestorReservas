<?php
namespace App\Exception;

class BadRequestException extends \Exception {

// Excepción para peticiones mal formadas
     public function getStatusCode(): int { return 400; }
}