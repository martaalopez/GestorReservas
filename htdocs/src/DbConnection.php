<?php

namespace App;

use RuntimeException;

class DbConnection
{
    public static function getConnection(): \mysqli
    {
        $config = parse_ini_file(__DIR__ . '/../.env');

        $con = @mysqli_connect(
            $config['DB_HOST'],
            $config['DB_USER'],
            $config['DB_PASS'],
            $config['DB_NAME'],
            (int) $config['DB_PORT']
        );

        if (mysqli_connect_errno()) {
            error_log('Error de conexión: ' . mysqli_connect_error());
            throw new RuntimeException('No se pudo conectar con la base de datos.');
        }

        mysqli_set_charset($con, 'utf8mb4');

        return $con;
    }
}