<?php

declare(strict_types=1);

class Pelicula
{
    private PDO $pdo;
    private string $uploads_dir;

    // Propiedades que mapean a columnas de la base de datos
    private ?int $id_pelicula = null;
    private string $nombre = '';
    private int $anio_estreno = 0;
    private string $director = '';
    private int $id_plataforma = 0;
    private ?int $id_image = null;
    private ?string $image_file = null;
    private ?string $image_alt = null;
    private ?string $plataforma = null;

    // Constructor
    public function __construct(PDO $pdo, string $uploads_dir = '../uploads/')
    {
        $this->pdo = $pdo;
        $this->uploads_dir = $uploads_dir;
    }

    // Métodos para acceder a propiedades básicas (solo los esenciales)
    public function getId(): ?int
    {
        return $this->id_pelicula;
    }

    public function setNombre(string $nombre): self
    {
        $nombre = trim($nombre);
        if (empty($nombre)) {
            throw new InvalidArgumentException("El nombre de la película es obligatorio", 1);
        }
        $this->nombre = $nombre;
        return $this;
    }

    public function setAnioEstreno(int $anio_estreno): self
    {
        $this->anio_estreno = $anio_estreno;
        return $this;
    }

    public function setDirector(string $director): self
    {
        $this->director = $director;
        return $this;
    }

    public function setIdPlataforma(int $id_plataforma): self
    {
        $this->id_plataforma = $id_plataforma;
        return $this;
    }

    // Cargar una película por ID
    public function cargar(int $id): bool
    {
        $sql = "SELECT 
            p.id_pelicula, p.nombre, p.anio_estreno, p.director,
            p.id_plataforma, p.id_image,
            pl.nombre AS plataforma,
            i.archivo AS image_file,
            i.alt AS image_alt
            FROM pelicula AS p
            JOIN plataforma AS pl ON p.id_plataforma = pl.id_plataforma
            LEFT JOIN image AS i ON p.id_image = i.id_image
            WHERE p.id_pelicula = :id_pelicula";

        $pelicula = pdo($this->pdo, $sql, ['id_pelicula' => $id])->fetch();

        if (!$pelicula) {
            return false;
        }

        $this->id_pelicula = (int)$pelicula['id_pelicula'];
        $this->nombre = $pelicula['nombre'];
        $this->anio_estreno = (int)$pelicula['anio_estreno'];
        $this->director = $pelicula['director'];
        $this->id_plataforma = (int)$pelicula['id_plataforma'];
        $this->id_image = $pelicula['id_image'] ? (int)$pelicula['id_image'] : null;
        $this->image_file = $pelicula['image_file'];
        $this->image_alt = $pelicula['image_alt'];
        $this->plataforma = $pelicula['plataforma'];

        return true;
    }

    // Obtener todas las películas
    public static function obtenerTodas(PDO $pdo): array
    {
        $sql = "SELECT p.id_pelicula, p.nombre, p.anio_estreno, p.director,
            pl.nombre AS plataforma, pl.id_plataforma,
            i.archivo AS image_file,
            i.alt AS image_alt
            FROM pelicula AS p
            JOIN plataforma AS pl ON p.id_plataforma = pl.id_plataforma
            LEFT JOIN image AS i ON p.id_image = i.id_image
            ORDER BY p.anio_estreno DESC";

        return pdo($pdo, $sql)->fetchAll();
    }

    // Guardar (crear o actualizar) una película
    public function guardar(array $actores_ids = []): bool
    {
        try {
            // Verificar si ya existe una película con el mismo nombre (solo para nuevas películas)
            if (!$this->id_pelicula) {
                $sql = "SELECT COUNT(*) FROM pelicula WHERE nombre = :nombre";
                $count = pdo($this->pdo, $sql, ['nombre' => $this->nombre])->fetchColumn();

                if ($count > 0) {
                    throw new Exception("Ya existe una película con el nombre '{$this->nombre}'", 1);
                }
            } else {
                // Para actualización, verificar que no exista otra película con el mismo nombre
                $sql = "SELECT COUNT(*) FROM pelicula WHERE nombre = :nombre AND id_pelicula != :id_pelicula";
                $count = pdo($this->pdo, $sql, [
                    'nombre' => $this->nombre,
                    'id_pelicula' => $this->id_pelicula
                ])->fetchColumn();

                if ($count > 0) {
                    throw new Exception("Ya existe otra película con el nombre '{$this->nombre}'", 1);
                }
            }

            $this->pdo->beginTransaction();

            // Manejar imagen si es necesario
            if ($this->image_file && $this->image_alt) {
                if (!$this->id_image) {
                    // Insertar nueva imagen
                    $sql = "INSERT INTO image (archivo, alt) VALUES (:archivo, :alt)";
                    pdo($this->pdo, $sql, [
                        'archivo' => $this->image_file,
                        'alt' => $this->image_alt
                    ]);
                    $this->id_image = (int)$this->pdo->lastInsertId();
                } else {
                    // Actualizar imagen existente
                    $sql = "UPDATE image SET archivo = :archivo, alt = :alt WHERE id_image = :id_image";
                    pdo($this->pdo, $sql, [
                        'archivo' => $this->image_file,
                        'alt' => $this->image_alt,
                        'id_image' => $this->id_image
                    ]);
                }
            }

            // Insertar o actualizar la película
            if ($this->id_pelicula) {
                // Actualizar película existente
                $sql = "UPDATE pelicula
                SET nombre = :nombre, anio_estreno = :anio_estreno, director = :director,
                id_plataforma = :id_plataforma, id_image = :id_image
                WHERE id_pelicula = :id_pelicula";
                $params = [
                    'nombre' => $this->nombre,
                    'anio_estreno' => $this->anio_estreno,
                    'director' => $this->director,
                    'id_plataforma' => $this->id_plataforma,
                    'id_image' => $this->id_image,
                    'id_pelicula' => $this->id_pelicula
                ];
            } else {
                // Insertar nueva película
                $sql = "INSERT INTO pelicula (nombre, anio_estreno, director, id_plataforma, id_image)
                VALUES (:nombre, :anio_estreno, :director, :id_plataforma, :id_image)";
                $params = [
                    'nombre' => $this->nombre,
                    'anio_estreno' => $this->anio_estreno,
                    'director' => $this->director,
                    'id_plataforma' => $this->id_plataforma,
                    'id_image' => $this->id_image
                ];
            }

            pdo($this->pdo, $sql, $params);

            // Si es una nueva película, obtener el ID
            if (!$this->id_pelicula) {
                $this->id_pelicula = (int)$this->pdo->lastInsertId();
            }

            // Manejar actores
            if (!empty($actores_ids)) {
                // Eliminar relaciones existentes
                $sql = "DELETE FROM actor_pelicula WHERE id_pelicula = :id_pelicula";
                pdo($this->pdo, $sql, ['id_pelicula' => $this->id_pelicula]);

                // Insertar nuevas relaciones
                $sql = "INSERT INTO actor_pelicula (id_pelicula, id_actor) VALUES (:id_pelicula, :id_actor)";
                foreach ($actores_ids as $id_actor) {
                    pdo($this->pdo, $sql, [
                        'id_pelicula' => $this->id_pelicula,
                        'id_actor' => $id_actor
                    ]);
                }
            }

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            if ($e->errorInfo[1] === 1062) {
                // Error de entrada duplicada
                throw new Exception("Nombre de película ya en uso", 1);
            }
            throw $e;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    // Eliminar una película
    public function eliminar(): bool
    {
        try {
            $this->pdo->beginTransaction();

            // Si hay una imagen, eliminarla
            if ($this->id_image) {
                // Actualizar la película para eliminar la referencia a la imagen
                $sql = "UPDATE pelicula SET id_image = NULL WHERE id_pelicula = :id_pelicula";
                pdo($this->pdo, $sql, ['id_pelicula' => $this->id_pelicula]);

                // Eliminar el registro de la imagen
                $sql = "DELETE FROM image WHERE id_image = :id_image";
                pdo($this->pdo, $sql, ['id_image' => $this->id_image]);

                // Eliminar el archivo de imagen
                if ($this->image_file) {
                    $path = $this->uploads_dir . $this->image_file;
                    if (file_exists($path)) {
                        unlink($path);
                    }
                }
            }

            // Eliminar relaciones con actores
            $sql = "DELETE FROM actor_pelicula WHERE id_pelicula = :id_pelicula";
            pdo($this->pdo, $sql, ['id_pelicula' => $this->id_pelicula]);

            // Eliminar favoritos de usuarios
            $sql = "DELETE FROM usuario_pelicula WHERE id_pelicula = :id_pelicula";
            pdo($this->pdo, $sql, ['id_pelicula' => $this->id_pelicula]);

            // Eliminar la película
            $sql = "DELETE FROM pelicula WHERE id_pelicula = :id_pelicula";
            pdo($this->pdo, $sql, ['id_pelicula' => $this->id_pelicula]);

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // Obtener actores para esta película
    public function obtenerActores(): array
    {
        if (!$this->id_pelicula) {
            return [];
        }

        $sql = "SELECT a.id_actor, a.nombre, a.apellido, a.picture
            FROM actor_pelicula ap
            JOIN actor a ON ap.id_actor = a.id_actor
            WHERE ap.id_pelicula = :id_pelicula";

        return pdo($this->pdo, $sql, ['id_pelicula' => $this->id_pelicula])->fetchAll();
    }

    // Obtener IDs de actores para esta película
    public function obtenerIdsActores(): array
    {
        if (!$this->id_pelicula) {
            return [];
        }

        $sql = "SELECT id_actor FROM actor_pelicula WHERE id_pelicula = :id_pelicula";
        return pdo($this->pdo, $sql, ['id_pelicula' => $this->id_pelicula])->fetchAll(PDO::FETCH_COLUMN);
    }

    // Verificar si una película está en favoritos del usuario
    public function esFavorita(int $id_usuario): bool
    {
        if (!$this->id_pelicula) {
            return false;
        }

        $sql = "SELECT COUNT(*) FROM usuario_pelicula 
                WHERE id_usuario = :id_usuario AND id_pelicula = :id_pelicula";

        $count = pdo($this->pdo, $sql, [
            'id_usuario' => $id_usuario,
            'id_pelicula' => $this->id_pelicula
        ])->fetchColumn();

        return (bool)$count;
    }

    // Obtener valoración y comentario del usuario para esta película
    public function obtenerValoracionUsuario(int $id_usuario): ?array
    {
        if (!$this->id_pelicula) {
            return null;
        }

        $sql = "SELECT valoracion, comentario FROM usuario_pelicula 
                WHERE id_usuario = :id_usuario AND id_pelicula = :id_pelicula";

        $result = pdo($this->pdo, $sql, [
            'id_usuario' => $id_usuario,
            'id_pelicula' => $this->id_pelicula
        ])->fetch();

        return $result ?: null;
    }

    // Añadir o actualizar favorito del usuario
    public function agregarAFavoritos(int $id_usuario, ?int $valoracion = null, ?string $comentario = null): bool
    {
        if (!$this->id_pelicula) {
            return false;
        }

        try {
            $sql = "INSERT INTO usuario_pelicula (id_usuario, id_pelicula, valoracion, comentario) 
                    VALUES (:id_usuario, :id_pelicula, :valoracion, :comentario)
                    ON DUPLICATE KEY UPDATE valoracion = VALUES(valoracion), comentario = VALUES(comentario)";

            pdo($this->pdo, $sql, [
                'id_usuario' => $id_usuario,
                'id_pelicula' => $this->id_pelicula,
                'valoracion' => $valoracion,
                'comentario' => $comentario
            ]);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // Eliminar de favoritos del usuario
    public function eliminarDeFavoritos(int $id_usuario): bool
    {
        if (!$this->id_pelicula) {
            return false;
        }

        try {
            $sql = "DELETE FROM usuario_pelicula 
                    WHERE id_usuario = :id_usuario AND id_pelicula = :id_pelicula";

            pdo($this->pdo, $sql, [
                'id_usuario' => $id_usuario,
                'id_pelicula' => $this->id_pelicula
            ]);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // Subir y procesar una imagen
    public function subirImagen(array $file, string $alt): bool
    {
        if ($file['error'] !== 0) {
            return false;
        }

        $temp = $file['tmp_name'];

        // Generar nombre basado en el nombre de la película con prefijo "foto_"
        $nombre_limpio = $this->limpiarNombreArchivo($this->nombre);
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'foto_' . $nombre_limpio . '.' . $extension;

        // Asegurar que el nombre sea único
        $contador = 1;
        $filename_original = $filename;
        while (file_exists($this->uploads_dir . $filename) && $filename != $this->image_file) {
            $filename = 'foto_' . $nombre_limpio . '_' . $contador . '.' . $extension;
            $contador++;
        }

        $destination = $this->uploads_dir . $filename;

        try {
            $imagick = new \Imagick($temp);
            $imagick->thumbnailImage(400, 600, true);
            $imagick->writeImage($destination);

            // Si hay una imagen anterior y es diferente a la nueva, eliminarla
            if ($this->image_file && $this->image_file !== $filename && file_exists($this->uploads_dir . $this->image_file)) {
                unlink($this->uploads_dir . $this->image_file);
            }

            $this->image_file = $filename;
            $this->image_alt = $alt;

            return true;
        } catch (Exception $e) {
            if (file_exists($destination) && $destination !== $this->uploads_dir . $this->image_file) {
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
            'id_pelicula' => $this->id_pelicula,
            'nombre' => $this->nombre,
            'anio_estreno' => $this->anio_estreno,
            'director' => $this->director,
            'id_plataforma' => $this->id_plataforma,
            'plataforma' => $this->plataforma,
            'id_image' => $this->id_image,
            'image_file' => $this->image_file,
            'image_alt' => $this->image_alt
        ];
    }

    // Añadir este método a la clase Pelicula
    public static function obtenerUltimas(PDO $pdo, int $limite = 6): array
    {
        $sql = "SELECT p.id_pelicula, p.nombre, p.anio_estreno, p.director,
        pl.nombre AS plataforma,
        i.archivo AS image_file,
        i.alt AS image_alt,
        GROUP_CONCAT(CONCAT(a.nombre, ' ', a.apellido) SEPARATOR '|') AS actores
        FROM pelicula AS p
        JOIN plataforma AS pl ON p.id_plataforma = pl.id_plataforma
        LEFT JOIN image AS i ON p.id_image = i.id_image
        LEFT JOIN actor_pelicula AS ap ON p.id_pelicula = ap.id_pelicula
        LEFT JOIN actor AS a ON ap.id_actor = a.id_actor
        GROUP BY p.id_pelicula
        ORDER BY p.id_pelicula DESC
        LIMIT :limite;";

        return pdo($pdo, $sql, ['limite' => $limite])->fetchAll();
    }

    // Obtener la valoración media de la película
    public function obtenerValoracionMedia(): ?float
    {
        if (!$this->id_pelicula) {
            return null;
        }

        $sql = "SELECT AVG(valoracion) as media 
            FROM usuario_pelicula 
            WHERE id_pelicula = :id_pelicula 
            AND valoracion IS NOT NULL";

        $resultado = pdo($this->pdo, $sql, ['id_pelicula' => $this->id_pelicula])->fetch();

        return $resultado['media'] ? round((float)$resultado['media'], 1) : null;
    }

    // Obtener el número de valoraciones
    public function obtenerNumeroValoraciones(): int
    {
        if (!$this->id_pelicula) {
            return 0;
        }

        $sql = "SELECT COUNT(*) as total 
            FROM usuario_pelicula 
            WHERE id_pelicula = :id_pelicula 
            AND valoracion IS NOT NULL";

        $resultado = pdo($this->pdo, $sql, ['id_pelicula' => $this->id_pelicula])->fetch();

        return (int)$resultado['total'];
    }

    // Obtener los comentarios más recientes
    public function obtenerComentariosRecientes(int $limite = 3): array
    {
        if (!$this->id_pelicula) {
            return [];
        }

        // Consulta modificada para usar solo los campos disponibles
        $sql = "SELECT up.comentario, up.valoracion, up.fecha_valoracion, 
            up.id_usuario
            FROM usuario_pelicula up
            WHERE up.id_pelicula = :id_pelicula 
            AND up.comentario IS NOT NULL AND up.comentario != ''
            ORDER BY up.fecha_valoracion DESC
            LIMIT :limite";

        return pdo($this->pdo, $sql, [
            'id_pelicula' => $this->id_pelicula,
            'limite' => $limite
        ])->fetchAll();
    }

    // Método para buscar por nombre
    public static function buscar(PDO $pdo, string $termino, string $orden = ''): array
    {
        $sql = "SELECT p.id_pelicula, p.nombre, p.anio_estreno, p.director,
            pl.nombre AS plataforma,
            i.archivo AS image_file,
            i.alt AS image_alt
            FROM pelicula AS p
            JOIN plataforma AS pl ON p.id_plataforma = pl.id_plataforma
            LEFT JOIN image AS i ON p.id_image = i.id_image
            WHERE p.nombre LIKE :termino";

        switch ($orden) {
            case 'nombre_asc':
                $sql .= " ORDER BY p.nombre ASC";
                break;
            case 'nombre_desc':
                $sql .= " ORDER BY p.nombre DESC";
                break;
            case 'anio_asc':
                $sql .= " ORDER BY p.anio_estreno ASC";
                break;
            case 'anio_desc':
            default:
                $sql .= " ORDER BY p.anio_estreno DESC";
                break;
        }

        $param = ['termino' => '%' . $termino . '%'];
        return pdo($pdo, $sql, $param)->fetchAll();
    }

    // Método para filtrar y ordenar sin búsqueda
    public static function filtrar(PDO $pdo, string $orden = ''): array
    {
        $sql = "SELECT p.id_pelicula, p.nombre, p.anio_estreno, p.director,
                pl.nombre AS plataforma,
                i.archivo AS image_file,
                i.alt AS image_alt
                FROM pelicula AS p
                JOIN plataforma AS pl ON p.id_plataforma = pl.id_plataforma
                LEFT JOIN image AS i ON p.id_image = i.id_image";

        switch ($orden) {
            case 'nombre_asc':
                $sql .= " ORDER BY p.nombre ASC";
                break;
            case 'nombre_desc':
                $sql .= " ORDER BY p.nombre DESC";
                break;
            case 'anio_asc':
                $sql .= " ORDER BY p.anio_estreno ASC";
                break;
            case 'anio_desc':
            default:
                $sql .= " ORDER BY p.anio_estreno DESC";
                break;
        }

        return pdo($pdo, $sql)->fetchAll();
    }
}
