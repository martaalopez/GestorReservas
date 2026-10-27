<?php
declare(strict_types=1);

namespace App\Http;

class JsonResponse
{
    public static function send(mixed $datos, int $codigo = 200): void
    {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');

        echo json_encode($datos, JSON_UNESCAPED_UNICODE);
        exit;
    }
}