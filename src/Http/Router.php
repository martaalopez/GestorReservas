<?php
declare(strict_types=1);

namespace App\Http;

use App\Exception\NotFoundException;

class Router
{
    private array $rutas = [];

    public function add(string $metodo, string $patron, callable $handler): void
    {
        $this->rutas[] = [
            'metodo'  => $metodo,
            'patron'  => $patron,
            'handler' => $handler,
        ];
    }
    public function dispatch(string $metodo, string $uri): void
    {

        $ruta = parse_url($uri, PHP_URL_PATH) ?? '/';
        $partesUrl = explode('/', trim($ruta, '/'));

        foreach ($this->rutas as $r) {
            
            if ($r['metodo'] !== $metodo) {
                continue;
            }


            $partesPatron = explode('/', trim($r['patron'], '/'));
            if (count($partesPatron) !== count($partesUrl)) {
                continue;
            }

            $params = [];
            $coincide = true;

            foreach ($partesPatron as $i => $parte) {
                if ($parte === '{id}') {
                    if (!ctype_digit($partesUrl[$i])) {
                        $coincide = false;
                        break;
                    }
                    $params[] = (int) $partesUrl[$i];
                } elseif ($parte !== $partesUrl[$i]) {
                    $coincide = false;
                    break;
                }
            }

            if ($coincide) {
                call_user_func_array($r['handler'], $params);
                return;
            }
        }

        throw new NotFoundException('Ruta no encontrada');
    }
}