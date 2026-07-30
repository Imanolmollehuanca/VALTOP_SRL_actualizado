CREATE TABLE equipos (

    id_equipo INT AUTO_INCREMENT PRIMARY KEY,

    id_trabajo INT NOT NULL,

    cantidad_equipos INT NOT NULL DEFAULT 1,

    contacto VARCHAR(150) NOT NULL,

    telefono_contacto VARCHAR(30) NULL,

    encargado VARCHAR(100) NOT NULL DEFAULT 'Ingrid Castillo',

    fecha_salida DATE NOT NULL,
    hora_salida TIME NOT NULL,

    fecha_regreso DATE NULL,
    hora_regreso TIME NULL,

    tiempo VARCHAR(50) NULL,

    costo DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    pago_1 DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    pago_2 DECIMAL(10,2) NOT NULL DEFAULT 0.00,

    estado ENUM(
        'Pendiente',
        'Devuelto',
        'Cambio de equipo'
    ) NOT NULL DEFAULT 'Pendiente',

    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_equipos_trabajo
        FOREIGN KEY (id_trabajo)
        REFERENCES trabajos(id_trabajo)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;