<?php

declare(strict_types=1);

class Actor
{
    private PDO $pdo;
    private string $uploads_dir;

    // Propiedades que mapean a columnas de la base de datos
    private ?int $id_actor = null;
    private string $nombre = '';
    private string $apellido = '';
    private string $fecha_nacimiento = '';
    private string $nacionalidad = '';
    private string $genero = '';
    private int $id_plataforma = 0;
    private ?string $picture = null;
    private ?string $fecha_debut = null;
    private ?string $estado_actividad = null;
    private ?string $especialidad = null;

    // Valores válidos para especialidad (según ENUM en la base de datos)
    private array $especialidades_validas = ['Drama', 'Comedia', 'Acción', 'Terror', 'Ciencia Ficción', 'Romance', 'Animación', 'Documental'];

    // Constructor
    public function __construct(PDO $pdo, string $uploads_dir = '../uploads/')
    {
        $this->pdo = $pdo;
        $this->uploads_dir = $uploads_dir;
    }

    // Métodos para acceder a propiedades básicas
    public function getId(): ?int
    {
        return $this->id_actor;
    }

    public function getNombreCompleto(): string
    {
        return $this->nombre . ' ' . $this->apellido;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;
        return $this;
    }

    public function setApellido(string $apellido): self
    {
        $this->apellido = $apellido;
        return $this;
    }

    public function setFechaNacimiento(string $fecha_nacimiento): self
    {
        $this->fecha_nacimiento = $fecha_nacimiento;
        return $this;
    }

    public function setNacionalidad(string $nacionalidad): self
    {
        $this->nacionalidad = $nacionalidad;
        return $this;
    }

    public function setGenero(string $genero): self
    {
        $this->genero = $genero;
        return $this;
    }

    public function setIdPlataforma(int $id_plataforma): self
    {
        $this->id_plataforma = $id_plataforma;
        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    // Nuevos métodos para los campos añadidos
    public function setFechaDebut(?string $fecha_debut): self
    {
        $this->fecha_debut = $fecha_debut;
        return $this;
    }

    public function setEstadoActividad(?string $estado_actividad): self
    {
        $this->estado_actividad = $estado_actividad;
        return $this;
    }

    public function setEspecialidad(?string $especialidad): self
    {
        // Validar que especialidad sea un valor válido del ENUM o null
        if ($especialidad !== null && !in_array($especialidad, $this->especialidades_validas, true)) {
            throw new InvalidArgumentException("Especialidad inválida: $especialidad");
        }
        $this->especialidad = $especialidad;
        return $this;
    }

    // Cargar un actor por ID
    public function cargar(int $id): bool
    {
        $sql = "SELECT id_actor, nombre, apellido, fecha_nacimiento, nacionalidad, genero, id_plataforma, picture, 
        fecha_debut, estado_actividad, especialidad 
        FROM actor 
        WHERE id_actor = :id_actor";

        $actor = pdo($this->pdo, $sql, ['id_actor' => $id])->fetch();

        if (!$actor) {
            return false;
        }

        $this->id_actor = (int)$actor['id_actor'];
        $this->nombre = $actor['nombre'];
        $this->apellido = $actor['apellido'];
        $this->fecha_nacimiento = $actor['fecha_nacimiento'];
        $this->nacionalidad = $actor['nacionalidad'];
        $this->genero = $actor['genero'];
        $this->id_plataforma = (int)$actor['id_plataforma'];
        $this->picture = $actor['picture'];
        $this->fecha_debut = $actor['fecha_debut'];
        $this->estado_actividad = $actor['estado_actividad'];
        $this->especialidad = $actor['especialidad'];

        return true;
    }

    // Obtener todos los actores
    public static function obtenerTodos(PDO $pdo): array
    {
        $sql = "SELECT 
        a.id_actor, 
        a.nombre, 
        a.apellido, 
        a.fecha_nacimiento, 
        a.nacionalidad, 
        a.genero, 
        pl.nombre AS plataforma,
        a.picture,
        a.fecha_debut,
        a.estado_actividad,
        a.especialidad
        FROM actor AS a
        JOIN plataforma AS pl ON a.id_plataforma = pl.id_plataforma
        ORDER BY a.id_actor DESC";

        return pdo($pdo, $sql)->fetchAll();
    }

    // Guardar (crear o actualizar) un actor
    public function guardar(): bool
    {
        try {
            // Verificar si ya existe un actor con el mismo nombre y apellido (solo para nuevos actores)
            if (!$this->id_actor) {
                $sql = "SELECT COUNT(*) FROM actor WHERE nombre = :nombre AND apellido = :apellido";
                $count = pdo($this->pdo, $sql, ['nombre' => $this->nombre, 'apellido' => $this->apellido])->fetchColumn();

                if ($count > 0) {
                    throw new Exception("Ya existe un actor con el nombre '{$this->nombre} {$this->apellido}'", 1);
                }
            } else {
                // Para actualización, verificar que no exista otro actor con el mismo nombre y apellido
                $sql = "SELECT COUNT(*) FROM actor WHERE nombre = :nombre AND apellido = :apellido AND id_actor != :id_actor";
                $count = pdo($this->pdo, $sql, [
                    'nombre' => $this->nombre,
                    'apellido' => $this->apellido,
                    'id_actor' => $this->id_actor
                ])->fetchColumn();

                if ($count > 0) {
                    throw new Exception("Ya existe otro actor con el nombre '{$this->nombre} {$this->apellido}'", 1);
                }
            }

            $this->pdo->beginTransaction();

            if ($this->id_actor) {
                // Actualizar actor existente
                $sql = "UPDATE actor
                SET nombre = :nombre, 
                apellido = :apellido, 
                fecha_nacimiento = :fecha_nacimiento,
                nacionalidad = :nacionalidad, 
                genero = :genero, 
                id_plataforma = :id_plataforma,
                fecha_debut = :fecha_debut,
                estado_actividad = :estado_actividad,
                especialidad = :especialidad";
                $params = [
                    'nombre' => $this->nombre,
                    'apellido' => $this->apellido,
                    'fecha_nacimiento' => $this->fecha_nacimiento,
                    'nacionalidad' => $this->nacionalidad,
                    'genero' => $this->genero,
                    'id_plataforma' => $this->id_plataforma,
                    'fecha_debut' => $this->fecha_debut,
                    'estado_actividad' => $this->estado_actividad,
                    'especialidad' => $this->especialidad,
                    'id_actor' => $this->id_actor
                ];

                // Si hay una imagen, añadirla a la consulta
                if ($this->picture) {
                    $sql .= ", picture = :picture";
                    $params['picture'] = $this->picture;
                }

                $sql .= " WHERE id_actor = :id_actor";
            } else {
                // Insertar nuevo actor
                $sql = "INSERT INTO actor (nombre, apellido, fecha_nacimiento, nacionalidad, genero, id_plataforma, fecha_debut, estado_actividad, especialidad";
                $params = [
                    'nombre' => $this->nombre,
                    'apellido' => $this->apellido,
                    'fecha_nacimiento' => $this->fecha_nacimiento,
                    'nacionalidad' => $this->nacionalidad,
                    'genero' => $this->genero,
                    'id_plataforma' => $this->id_plataforma,
                    'fecha_debut' => $this->fecha_debut,
                    'estado_actividad' => $this->estado_actividad,
                    'especialidad' => $this->especialidad
                ];

                // Si hay una imagen, añadirla a la consulta
                if ($this->picture) {
                    $sql .= ", picture";
                    $params['picture'] = $this->picture;
                }

                $sql .= ") VALUES (:nombre, :apellido, :fecha_nacimiento, :nacionalidad, :genero, :id_plataforma, :fecha_debut, :estado_actividad, :especialidad";
                if ($this->picture) {
                    $sql .= ", :picture";
                }
                $sql .= ")";
            }

            pdo($this->pdo, $sql, $params);

            // Si es un nuevo actor, obtener el ID
            if (!$this->id_actor) {
                $this->id_actor = (int)$this->pdo->lastInsertId();
            }

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            if ($e->errorInfo[1] === 1062) {
                // Error de entrada duplicada
                throw new Exception("Nombre de actor ya en uso", 1);
            }
            throw $e;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    // Eliminar un actor
    public function eliminar(): bool
    {
        try {
            $this->pdo->beginTransaction();

            // Eliminar relaciones con películas
            $sql = "DELETE FROM actor_pelicula WHERE id_actor = :id_actor";
            pdo($this->pdo, $sql, ['id_actor' => $this->id_actor]);

            // Eliminar relaciones con series
            $sql = "DELETE FROM actor_serie WHERE id_actor = :id_actor";
            pdo($this->pdo, $sql, ['id_actor' => $this->id_actor]);

            // Eliminar el actor
            $sql = "DELETE FROM actor WHERE id_actor = :id_actor";
            pdo($this->pdo, $sql, ['id_actor' => $this->id_actor]);

            // Eliminar la imagen si existe
            if ($this->picture) {
                $path = $this->uploads_dir . $this->picture;
                if (file_exists($path)) {
                    unlink($path);
                }
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // Obtener películas de este actor
    public function obtenerPeliculas(): array
    {
        if (!$this->id_actor) {
            return [];
        }

        $sql = "SELECT p.id_pelicula, p.nombre, p.anio_estreno, p.director,
        pl.nombre AS plataforma,
        pl.picture AS plataforma_picture,
        i.archivo AS image_file,
        i.alt AS image_alt,
        GROUP_CONCAT(
            CONCAT(
                a.nombre, ' ', 
                a.apellido, ':', 
                a.picture
            ) SEPARATOR '|'
        ) AS actores
        FROM pelicula AS p
        JOIN plataforma AS pl ON p.id_plataforma = pl.id_plataforma
        LEFT JOIN image AS i ON p.id_image = i.id_image
        LEFT JOIN actor_pelicula AS ap ON p.id_pelicula = ap.id_pelicula
        LEFT JOIN actor AS a ON ap.id_actor = a.id_actor
        WHERE a.id_actor = :id_actor
        GROUP BY p.id_pelicula
        ORDER BY p.id_pelicula DESC";

        return pdo($this->pdo, $sql, ['id_actor' => $this->id_actor])->fetchAll();
    }

    // Obtener series de este actor
    public function obtenerSeries(): array
    {
        if (!$this->id_actor) {
            return [];
        }

        $sql = "SELECT s.id_serie, s.nombre, s.anio_estreno, s.n_temporadas,
        pl.nombre AS plataforma,
        pl.picture AS plataforma_picture,
        i.archivo AS image_file,
        i.alt AS image_alt,
        GROUP_CONCAT(
            CONCAT(
                a.nombre, ' ', 
                a.apellido, ':', 
                a.picture
            ) SEPARATOR '|'
        ) AS actores
        FROM serie AS s
        JOIN plataforma AS pl ON s.id_plataforma = pl.id_plataforma
        LEFT JOIN image AS i ON s.id_image = i.id_image
        LEFT JOIN actor_serie AS as_table ON s.id_serie = as_table.id_serie
        LEFT JOIN actor AS a ON as_table.id_actor = a.id_actor
        WHERE a.id_actor = :id_actor
        GROUP BY s.id_serie
        ORDER BY s.id_serie DESC";

        return pdo($this->pdo, $sql, ['id_actor' => $this->id_actor])->fetchAll();
    }

    // Subir y procesar una imagen
    public function subirImagen(array $file): bool
    {
        if ($file['error'] !== 0) {
            return false;
        }

        $temp = $file['tmp_name'];

        // Generar nombre basado en el nombre del actor con prefijo "foto_"
        $nombre_limpio = $this->limpiarNombreArchivo($this->nombre . '_' . $this->apellido);
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'foto_' . $nombre_limpio . '.' . $extension;

        // Asegurar que el nombre sea único
        $contador = 1;
        $filename_original = $filename;
        while (file_exists($this->uploads_dir . $filename) && $filename != $this->picture) {
            $filename = 'foto_' . $nombre_limpio . '_' . $contador . '.' . $extension;
            $contador++;
        }

        $destination = $this->uploads_dir . $filename;

        try {
            $imagick = new \Imagick($temp);
            $imagick->thumbnailImage(300, 300, true); // Tamaño cuadrado para foto de actor
            $imagick->writeImage($destination);

            // Si hay una imagen anterior y es diferente a la nueva, eliminarla
            if ($this->picture && $this->picture !== $filename && file_exists($this->uploads_dir . $this->picture)) {
                unlink($this->uploads_dir . $this->picture);
            }

            $this->picture = $filename;

            return true;
        } catch (Exception $e) {
            if (file_exists($destination) && $destination !== $this->uploads_dir . $this->picture) {
                unlink($destination);
            }
            return false;
        }
    }

    // Método auxiliar para limpiar nombres de archivo
    private function limpiarNombreArchivo(string $nombre): string
    {
        // Convertir a minúsculas
        $nombre = strtolower($nombre);

        // Reemplazar caracteres especiales y espacios
        $nombre = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü'], ['a', 'e', 'i', 'o', 'u', 'n', 'u'], $nombre);
        $nombre = preg_replace('/[^a-z0-9_-]/', '_', $nombre);

        // Eliminar múltiples guiones bajos consecutivos
        $nombre = preg_replace('/_+/', '_', $nombre);

        // Eliminar guiones bajos al inicio y final
        $nombre = trim($nombre, '_');

        return $nombre;
    }

    // Convertir a array
    public function aArray(): array
    {
        return [
            'id_actor' => $this->id_actor,
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'fecha_nacimiento' => $this->fecha_nacimiento,
            'nacionalidad' => $this->nacionalidad,
            'genero' => $this->genero,
            'id_plataforma' => $this->id_plataforma,
            'picture' => $this->picture,
            'fecha_debut' => $this->fecha_debut,
            'estado_actividad' => $this->estado_actividad,
            'especialidad' => $this->especialidad
        ];
    }
}