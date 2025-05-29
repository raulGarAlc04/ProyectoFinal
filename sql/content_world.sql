create database content_world;

use content_world;

CREATE TABLE plataforma (
    id_plataforma int auto_increment primary key,
    nombre varchar(30) NOT NULL,
    precio_mensual DECIMAL(6,2),
    pais_origen varchar(30),
    anio_lanzamiento INT,
    usuarios_activos INT,
    picture varchar(500),
    UNIQUE KEY idx_nombre_plataforma (nombre)
);

CREATE TABLE actor (
    id_actor int auto_increment primary key,
    nombre varchar(40) NOT NULL,
    apellido varchar(40) NOT NULL,
    fecha_nacimiento DATE,
    nacionalidad varchar(30),
    genero varchar(20),
    picture varchar(500),
    fecha_debut DATE,
    estado_actividad ENUM('Activo', 'Retirado', 'Fallecido') DEFAULT 'Activo',
    especialidad ENUM('Cine', 'Television', 'Teatro', 'Voz', 'Accion', 'Comedia', 'Drama'),
    UNIQUE KEY idx_nombre_apellido_actor (nombre, apellido)
);

CREATE TABLE image (
    id_image int auto_increment primary key,
    archivo varchar(500),
    alt varchar(500)
);

CREATE TABLE pelicula (
    id_pelicula int auto_increment primary key,
    nombre varchar(30),
    anio_estreno int,
    director varchar(35),
    id_plataforma INT,
    id_image INT UNIQUE,
    FOREIGN KEY (id_plataforma) REFERENCES plataforma(id_plataforma),
    FOREIGN KEY (id_image) REFERENCES image(id_image),
    UNIQUE KEY idx_nombre_pelicula (nombre)
);

CREATE TABLE serie (
    id_serie int auto_increment primary key,
    nombre varchar(40),
    anio_estreno int,
    n_temporadas int,
    id_plataforma INT,
    id_image INT UNIQUE,
    FOREIGN KEY (id_plataforma) REFERENCES plataforma(id_plataforma),
    FOREIGN KEY (id_image) REFERENCES image(id_image),
    UNIQUE KEY idx_nombre_serie (nombre)
);

CREATE TABLE actor_pelicula (
    id_actor INT,
    id_pelicula INT,
    rol varchar(50),
    PRIMARY KEY (id_actor, id_pelicula),
    FOREIGN KEY (id_actor) REFERENCES actor(id_actor),
    FOREIGN KEY (id_pelicula) REFERENCES pelicula(id_pelicula)
);

CREATE TABLE actor_serie (
    id_actor INT,
    id_serie INT,
    rol varchar(50),
    PRIMARY KEY (id_actor, id_serie),
    FOREIGN KEY (id_actor) REFERENCES actor(id_actor),
    FOREIGN KEY (id_serie) REFERENCES serie(id_serie)
);

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    foto_perfil varchar(500),
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    es_admin BOOLEAN DEFAULT FALSE
);

CREATE TABLE usuario_serie (
    id_usuario INT,
    id_serie INT,
    valoracion INT CHECK (valoracion >= 1 AND valoracion <= 10),
    comentario TEXT,
    fecha_valoracion DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_usuario, id_serie),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id),
    FOREIGN KEY (id_serie) REFERENCES serie(id_serie)
);

CREATE TABLE usuario_pelicula (
    id_usuario INT,
    id_pelicula INT,
    valoracion INT CHECK (valoracion >= 1 AND valoracion <= 10),
    comentario TEXT,
    fecha_valoracion DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_usuario, id_pelicula),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id),
    FOREIGN KEY (id_pelicula) REFERENCES pelicula(id_pelicula)
);

CREATE TABLE usuarios_plataformas (
    id_usuario int,
    id_plataforma int,
    primary key (id_usuario, id_plataforma),
    foreign key (id_usuario) references usuarios(id),
    foreign key (id_plataforma) references plataforma(id_plataforma)
);

INSERT INTO `actor` (`id_actor`, `nombre`, `apellido`, `fecha_nacimiento`, `nacionalidad`, `genero`, `picture`, `fecha_debut`, `estado_actividad`, `especialidad`) VALUES
(21, 'Ryan', 'Gosling', '1980-11-12', 'Canadá', 'Masculino', 'foto_ryan_gosling.JPG', '2000-11-12', 'Activo', 'Acción'),
(22, 'Emily', 'Blunt', '1983-02-23', 'Inglaterra', 'Femenino', 'foto_emily_blunt.JPG', '2000-02-23', 'Activo', 'Comedia'),
(23, 'Aaron', 'Taylor-Johnson', '1990-06-13', 'Inglaterra', 'Masculino', 'foto_aaron_taylor-johnson.JPG', '2000-06-13', 'Activo', 'Acción'),
(24, 'Russell', 'Crowe', '1964-04-07', 'Nueva Zelanda', 'Masculino', 'foto_russell_crowe.JPG', '2000-04-07', 'Activo', 'Ciencia Ficción'),
(25, 'Pepe', 'Viyuela', '1963-06-02', 'España', 'Masculino', 'foto_pepe_viyuela.JPG', '2000-06-02', 'Activo', 'Comedia'),
(26, 'Leo', 'Harlem', '1962-11-16', 'España', 'Masculino', 'foto_leo_harlem.JPG', '2000-11-16', 'Activo', 'Comedia'),
(27, 'Ryan', 'Reynolds', '1976-10-23', 'Canadá', 'Masculino', 'foto_ryan_reynolds.JPG', '2000-11-11', 'Activo', 'Comedia'),
(28, 'Cailey', 'Fleming', '2007-03-28', 'Estados Unidos', 'Femenino', 'foto_cailey_fleming.JPG', '2010-11-11', 'Activo', 'Drama'),
(29, 'Scarlett', 'Johansson', '1990-11-11', 'Estados Unidos', 'Femenino', 'foto_scarlett_johansson.JPG', '2000-11-11', 'Retirado', 'Romance'),
(30, 'Chris', 'Hemsworth', '1985-10-21', 'Australia', 'Masculino', 'foto_chris_hemsworth.JPG', '2000-10-20', 'Retirado', 'Acción'),
(31, 'Robert', 'Downey Jr', '1970-04-17', 'Estados Unidos', 'Masculino', 'foto_robert_downey_jr.JPG', '2000-10-20', 'Activo', 'Animación'),
(32, 'Zoe', 'Saldana', '1980-03-12', 'Estados Unidos', 'Femenino', 'foto_zoe_saldana.JPG', '2000-11-20', 'Activo', 'Comedia'),
(33, 'Edward', 'Norton', '1967-11-20', 'Inglaterra', 'Masculino', 'foto_edward_norton.JPG', '2000-10-02', 'Activo', 'Acción'),
(34, 'Dave', 'Bautista', '1980-01-01', 'Estadounidense', 'Masculino', 'foto_dave_bautista.JPG', '2010-10-20', 'Retirado', 'Comedia'),
(35, 'Maximilian', 'Mundt', '2000-10-20', 'Alemania', 'Masculino', 'foto_maximilian_mundt.JPG', '2010-02-20', 'Activo', 'Drama'),
(36, 'Lena', 'Klenke', '2000-08-20', 'Alemania', 'Femenino', 'foto_lena_klenke.JPG', '2010-07-07', 'Activo', 'Comedia'),
(37, 'Alan', 'Ritchson', '1970-10-10', 'Canadá', 'Masculino', 'foto_alan_ritchson.JPG', '2001-10-10', 'Retirado', 'Acción'),
(38, 'Maria', 'Sten', '1980-10-10', 'Estados Unidos', 'Femenino', 'foto_maria_sten.JPG', '2003-03-03', 'Retirado', 'Drama'),
(39, 'Carla', 'Sehn', '1995-07-07', 'Suecia', 'Femenino', 'foto_carla_sehn.JPG', '2015-03-20', 'Retirado', 'Ciencia Ficción'),
(40, 'Kardo', 'Razzazi', '1991-04-05', 'Suecia', 'Masculino', 'foto_kardo_razzazi.JPG', '1999-08-09', 'Activo', 'Terror'),
(41, 'Tom', 'Schilling', '1989-09-08', 'Alemania', 'Masculino', 'foto_tom_schilling.JPG', '2000-10-20', 'Activo', 'Comedia'),
(42, 'Emily', 'Cox', '1990-03-03', 'Alemania', 'Femenino', 'foto_emily_cox.JPG', '2018-03-31', 'Retirado', 'Terror'),
(43, 'Pedro', 'Alonso', '1987-02-28', 'España', 'Masculino', 'foto_pedro_alonso.JPG', '2002-09-04', 'Activo', 'Acción'),
(44, 'Michelle', 'Jenner', '1988-03-29', 'España', 'Femenino', 'foto_michelle_jenner.JPG', '2005-05-04', 'Retirado', 'Comedia'),
(45, 'Adam', 'Scott', '1978-05-04', 'Estados Unidos', 'Masculino', 'foto_adam_scott.JPG', '2004-07-06', 'Activo', 'Comedia'),
(46, 'Britt', 'Lower', '1988-05-31', 'Estados Unidos', 'Femenino', 'foto_britt_lower.JPG', '2007-06-05', 'Activo', 'Comedia'),
(47, 'Peter', 'Griffin', '1960-02-22', 'Estados Unidos', 'Masculino', 'foto_peter_griffin.JPG', '1999-12-30', 'Retirado', 'Comedia');

--
-- Volcado de datos para la tabla `actor_pelicula`
--

INSERT INTO `actor_pelicula` (`id_actor`, `id_pelicula`, `rol`) VALUES
(21, 27, NULL),
(22, 27, NULL),
(23, 26, NULL),
(24, 26, NULL),
(25, 25, NULL),
(26, 25, NULL),
(27, 24, NULL),
(28, 24, NULL),
(29, 22, NULL),
(29, 23, NULL),
(30, 22, NULL),
(30, 23, NULL),
(31, 22, NULL),
(31, 23, NULL),
(32, 22, NULL),
(32, 23, NULL),
(33, 28, NULL),
(34, 28, NULL);

--
-- Volcado de datos para la tabla `actor_serie`
--

INSERT INTO `actor_serie` (`id_actor`, `id_serie`, `rol`) VALUES
(35, 14, NULL),
(36, 14, NULL),
(37, 15, NULL),
(38, 15, NULL),
(39, 16, NULL),
(40, 16, NULL),
(41, 17, NULL),
(42, 17, NULL),
(43, 18, NULL),
(44, 18, NULL),
(45, 19, NULL),
(46, 19, NULL),
(47, 13, NULL);

--
-- Volcado de datos para la tabla `image`
--

INSERT INTO `image` (`id_image`, `archivo`, `alt`) VALUES
(28, 'foto_vengadores_endgame.JPG', 'Cartel Vengadores: Endgame'),
(29, 'foto_vengadores_infinity_war.JPG', 'Cartel Vengadores: Infinity War'),
(30, 'foto_amigos_imaginarios.JPG', 'Cartel Amigos Imaginarios'),
(31, 'foto_la_familia_beneton.JPG', 'Cartel La Familia Benetón'),
(32, 'foto_kraven_the_hunter.JPG', 'Cartel Kraven The Hunter'),
(33, 'foto_el_especialista.JPG', 'Cartel El Especialista'),
(34, 'foto_punales_por_la_espalda_glass_onion.JPG', 'Cartel Puñales Por La Espalda: Glass Onion'),
(35, 'foto_family_guy.JPG', 'Cartel Family Guy'),
(36, 'foto_como_vender_drogas_online_rapido.jpg', 'Cartel Como Vender Drogas Online (rapido)'),
(37, 'foto_reacher.JPG', 'Cartel Reacher'),
(38, 'foto_los_crimenes_de_are.JPG', 'Cartel Los Crimenes de Are'),
(39, 'foto_mindfullness_para_asesinos.JPG', 'Cartel Mindfullness para Asesinos'),
(40, 'foto_la_casa_de_papel_berlin.JPG', 'Cartel La Casa De Papel: Berlín'),
(41, 'foto_separacion.JPG', 'Cartel Separacion');

--
-- Volcado de datos para la tabla `pelicula`
--

INSERT INTO `pelicula` (`id_pelicula`, `nombre`, `anio_estreno`, `director`, `id_plataforma`, `id_image`) VALUES
(22, 'Vengadores: Endgame', 2019, 'Director', 12, 28),
(23, 'Vengadores: Infinity War', 2018, 'Director', 12, 29),
(24, 'Amigos Imaginarios', 2024, 'Director', 12, 30),
(25, 'La Familia Benetón', 2023, 'Director', 17, 31),
(26, 'Kraven The Hunter', 2024, 'Paco Barba', 18, 32),
(27, 'El Especialista', 2024, 'James Cameron', 13, 33),
(28, 'Puñales Por La Espalda: Glass ', 2022, 'Justin Lin', 16, 34);

--
-- Volcado de datos para la tabla `plataforma`
--

INSERT INTO `plataforma` (`id_plataforma`, `nombre`, `precio_mensual`, `pais_origen`, `anio_lanzamiento`, `usuarios_activos`, `picture`) VALUES
(11, 'Netflix', 12.99, 'Estados Unidos', 2008, 2147483647, 'foto_netflix.jpg'),
(12, 'Disney Plus', 9.99, 'Estados Unidos', 2018, 2147483647, 'foto_disney_plus.webp'),
(13, 'Max', 8.99, 'Estados Unidos', 2024, 4000000, 'foto_max.png'),
(14, 'Amazon Prime Video', 5.99, 'Estados Unidos', 2020, 120000000, 'foto_amazon_prime_video.png'),
(15, 'Apple TV+', 9.99, 'Estados Unidos', 2016, 2147483647, 'foto_apple_tv.png'),
(16, 'Sky Showtime', 7.99, 'Estados Unidos', 2022, 900000, 'foto_sky_showtime.webp'),
(17, 'Hulu', 3.99, 'Estados Unidos', 2023, 150000, 'foto_hulu.png'),
(18, 'Filmin', 2.99, 'Estados Unidos', 2024, 2000, 'foto_filmin.png');

--
-- Volcado de datos para la tabla `serie`
--

INSERT INTO `serie` (`id_serie`, `nombre`, `anio_estreno`, `n_temporadas`, `id_plataforma`, `id_image`) VALUES
(13, 'Family Guy', 1999, 23, 11, 35),
(14, 'Como Vender Drogas Online (rapido)', 2019, 4, 13, 36),
(15, 'Reacher', 2022, 3, 14, 37),
(16, 'Los Crimenes de Are', 2025, 1, 17, 38),
(17, 'Mindfullness para Asesinos', 2024, 1, 17, 39),
(18, 'La Casa De Papel: Berlín', 2024, 1, 12, 40),
(19, 'Separacion', 2022, 3, 15, 41);

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellidos`, `email`, `password`, `foto_perfil`, `fecha_registro`, `activo`, `es_admin`) VALUES
(1, 'Raul', 'Garcia Alcantara', 'raulgarciaalcantara7@gmail.com', '12345678', 'perfil_1_68348f8a8be6b.jpg', '2025-05-02 09:44:34', 0, 1),
(2, 'Cristina', 'Garrido', 'lacristi@gmail.com', '12345678', 'perfil_2_683497ee16726.jpg', '2025-05-11 18:50:52', 1, 0),
(11, 'Paco', 'Barba Alvarez', 'paco@gmail.com', '12345678', 'blank.png', '2025-05-29 16:06:02', 0, 0);