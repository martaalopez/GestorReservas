CREATE DATABASE IF NOT EXISTS reservas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE reservas;

DROP TABLE IF EXISTS reserva_evento;

DROP TABLE IF EXISTS reserva;

CREATE TABLE reserva (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre_huesped VARCHAR(100) NOT NULL,
    email_huesped VARCHAR(150) NOT NULL,
    nombre_alojamiento VARCHAR(150) NOT NULL,
    fecha_entrada DATE NOT NULL,
    fecha_salida DATE NOT NULL,
    estado ENUM(
        'pendiente',
        'confirmada',
        'cancelada'
    ) NOT NULL DEFAULT 'pendiente',
    importe DECIMAL(10, 2) NOT NULL,
    notas VARCHAR(500) NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_reserva_estado (estado),
    INDEX idx_reserva_fecha_entrada (fecha_entrada),
    INDEX idx_reserva_nombre_huesped (nombre_huesped)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE reserva_evento (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    reserva_id INT UNSIGNED NOT NULL,
    tipo VARCHAR(30) NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_evento_reserva (reserva_id),
    CONSTRAINT fk_evento_reserva FOREIGN KEY (reserva_id) REFERENCES reserva (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

INSERT INTO
    reserva (
        id,
        nombre_huesped,
        email_huesped,
        nombre_alojamiento,
        fecha_entrada,
        fecha_salida,
        estado,
        importe,
        notas,
        fecha_creacion
    )
VALUES (
        1,
        'Ana García López',
        'ana.garcia@gmail.com',
        'Hotel Sol y Mar',
        '2026-11-05',
        '2026-11-09',
        'pendiente',
        480.00,
        'Llegada después de las 22:00',
        '2026-10-10 09:15:00'
    ),
    (
        2,
        'Carlos Martínez Ruiz',
        'carlos.martinez@gmail.com',
        'Apartamentos Plaza Mayor',
        '2026-11-12',
        '2026-11-15',
        'confirmada',
        315.50,
        NULL,
        '2026-10-02 11:30:00'
    ),
    (
        3,
        'Lucía Fernández',
        'lucia.fernandez@gmail.com',
        'Casa Rural El Olivar',
        '2026-09-10',
        '2026-09-13',
        'confirmada',
        410.00,
        'Estancia ya disfrutada',
        '2026-08-20 09:00:00'
    ),
    (
        4,
        'Javier Sánchez',
        'javier.sanchez@gmail.com',
        'Hotel Sol y Mar',
        '2026-10-25',
        '2026-10-27',
        'cancelada',
        210.00,
        'Cancelada por enfermedad',
        '2026-09-20 10:00:00'
    ),
    (
        5,
        'María Torres Gil',
        'maria.torres@gmail.com',
        'Hostal La Alameda',
        '2026-12-20',
        '2026-12-22',
        'pendiente',
        130.00,
        NULL,
        '2026-10-15 16:20:00'
    ),
    (
        6,
        'David Jiménez',
        'david.jimenez@gmail.com',
        'Casa Rural El Olivar',
        '2026-12-06',
        '2026-12-08',
        'cancelada',
        340.00,
        'Cambio de planes',
        '2026-10-01 14:10:00'
    );

INSERT INTO
    reserva_evento (
        reserva_id,
        tipo,
        descripcion,
        fecha_creacion
    )
VALUES (
        1,
        'creada',
        'Reserva creada en estado pendiente',
        '2026-10-10 09:15:00'
    ),
    (
        2,
        'creada',
        'Reserva creada en estado pendiente',
        '2026-10-02 11:30:00'
    ),
    (
        2,
        'confirmada',
        'Reserva confirmada',
        '2026-10-03 09:00:00'
    ),
    (
        3,
        'creada',
        'Reserva creada en estado pendiente',
        '2026-08-20 09:00:00'
    ),
    (
        3,
        'confirmada',
        'Reserva confirmada',
        '2026-08-21 12:00:00'
    ),
    (
        4,
        'creada',
        'Reserva creada en estado pendiente',
        '2026-09-20 10:00:00'
    ),
    (
        4,
        'cancelada',
        'Reserva cancelada',
        '2026-09-30 17:20:00'
    ),
    (
        5,
        'creada',
        'Reserva creada en estado pendiente',
        '2026-10-15 16:20:00'
    ),
    (
        6,
        'creada',
        'Reserva creada en estado pendiente',
        '2026-10-01 14:10:00'
    ),
    (
        6,
        'confirmada',
        'Reserva confirmada',
        '2026-10-02 09:30:00'
    ),
    (
        6,
        'cancelada',
        'Reserva cancelada',
        '2026-10-14 11:00:00'
    );