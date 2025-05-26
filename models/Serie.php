<?php

declare(strict_types=1);

class Serie
{
    private PDO $pdo;
    private string $uploads_dir;

    // Propiedades que mapean a columnas de la base de datos
    private ?int $id_serie = null;
    private string $nombre = '';
    private int $anio_estreno = 0;
    private int $n_temporadas = 0;
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
        return $this->id_serie;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;
        return $this;
    }

    public function setAnioEstreno(int $anio_estreno): self
    {
        $this->anio_estreno = $anio_estreno;
        return $this;
    }

    public function setNTemporadas(int $n_temporadas): self
    {
        $this->n_temporadas = $n_temporadas;
        return $this;
    }

    public function setIdPlataforma(int $id_plataforma): self
    {
        $this->id_plataforma = $id_plataforma;
        return $this;
    }

    // Cargar una serie por ID
    public function cargar(int $id): bool
    {
        $sql = "SELECT 
        s.id_serie, s.nombre, s.anio_estreno, s.n_temporadas,
        s.id_plataforma, s.id_image,
        pl.nombre AS plataforma,
        i.archivo AS image_file,
        i.alt AS image_alt
        FROM serie AS s
        JOIN plataforma AS pl ON s.id_plataforma = pl.id_plataforma
        LEFT JOIN image AS i ON s.id_image = i.id_image
        WHERE s.id_serie = :id_serie";

        $serie = pdo($this->pdo, $sql, ['id_serie' => $id])->fetch();

        if (!$serie) {
            return false;
        }

        $this->id_serie = (int)$serie['id_serie'];
        $this->nombre = $serie['nombre'];
        $this->anio_estreno = (int)$serie['anio_estreno'];
        $this->n_temporadas = (int)$serie['n_temporadas'];
        $this->id_plataforma = (int)$serie['id_plataforma'];
        $this->id_image = $serie['id_image'] ? (int)$serie['id_image'] : null;
        $this->image_file = $serie['image_file'];
        $this->image_alt = $serie['image_alt'];
        $this->plataforma = $serie['plataforma'];

        return true;
    }

    // Obtener todas las series
    public static function obtenerTodas(PDO $pdo): array
    {
        $sql = "SELECT s.id_serie, s.nombre, s.anio_estreno, s.n_temporadas,
        pl.nombre AS plataforma, pl.id_plataforma,
        i.archivo AS image_file,
        i.alt AS image_alt
        FROM serie AS s
        JOIN plataforma AS pl ON s.id_plataforma = pl.id_plataforma
        LEFT JOIN image AS i ON s.id_image = i.id_image
        ORDER BY s.anio_estreno DESC";

        return pdo($pdo, $sql)->fetchAll();
    }

    // Guardar (crear o actualizar) una serie
    public function guardar(array $actores_ids = []): bool
    {
        try {
            // Verificar si ya existe una serie con el mismo nombre (solo para nuevas series)
            if (!$this->id_serie) {
                $sql = "SELECT COUNT(*) FROM serie WHERE nombre = :nombre";
                $count = pdo($this->pdo, $sql, ['nombre' => $this->nombre])->fetchColumn();

                if ($count > 0) {
                    throw new Exception("Ya existe una serie con el nombre '{$this->nombre}'", 1);
                }
            }

            $this->pdo->beginTransaction();

            // Manejar imagen si es necesario
            if ($this->image_file && $this->image_alt && !$this->id_image) {
                $sql = "INSERT INTO image (archivo, alt) VALUES (:archivo, :alt)";
                pdo($this->pdo, $sql, [
                    'archivo' => $this->image_file,
                    'alt' => $this->image_alt
                ]);
                $this->id_image = (int)$this->pdo->lastInsertId();
            }

            // Insertar o actualizar la serie
            if ($this->id_serie) {
                // Verificar si el nuevo nombre ya existe para otra serie
                $sql = "SELECT COUNT(*) FROM serie WHERE nombre = :nombre AND id_serie != :id_serie";
                $count = pdo($this->pdo, $sql, [
                    'nombre' => $this->nombre,
                    'id_serie' => $this->id_serie
                ])->fetchColumn();

                if ($count > 0) {
                    throw new Exception("Ya existe otra serie con el nombre '{$this->nombre}'", 1);
                }

                // Actualizar serie existente
                $sql = "UPDATE serie
                SET nombre = :nombre, anio_estreno = :anio_estreno, n_temporadas = :n_temporadas,
                id_plataforma = :id_plataforma, id_image = :id_image
                WHERE id_serie = :id_serie";
                $params = [
                    'nombre' => $this->nombre,
                    'anio_estreno' => $this->anio_estreno,
                    'n_temporadas' => $this->n_temporadas,
                    'id_plataforma' => $this->id_plataforma,
                    'id_image' => $this->id_image,
                    'id_serie' => $this->id_serie
                ];
            } else {
                // Insertar nueva serie
                $sql = "INSERT INTO serie (nombre, anio_estreno, n_temporadas, id_plataforma, id_image)
                VALUES (:nombre, :anio_estreno, :n_temporadas, :id_plataforma, :id_image)";
                $params = [
                    'nombre' => $this->nombre,
                    'anio_estreno' => $this->anio_estreno,
                    'n_temporadas' => $this->n_temporadas,
                    'id_plataforma' => $this->id_plataforma,
                    'id_image' => $this->id_image
                ];
            }

            pdo($this->pdo, $sql, $params);

            // Si es una nueva serie, obtener el ID
            if (!$this->id_serie) {
                $this->id_serie = (int)$this->pdo->lastInsertId();
            }

            // Manejar actores
            if (!empty($actores_ids)) {
                // Eliminar relaciones existentes
                $sql = "DELETE FROM actor_serie WHERE id_serie = :id_serie";
                pdo($this->pdo, $sql, ['id_serie' => $this->id_serie]);

                // Insertar nuevas relaciones
                $sql = "INSERT INTO actor_serie (id_serie, id_actor) VALUES (:id_serie, :id_actor)";
                foreach ($actores_ids as $id_actor) {
                    pdo($this->pdo, $sql, [
                        'id_serie' => $this->id_serie,
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
                throw new Exception("Nombre de serie ya en uso", 1);
            }
            throw $e;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    // Eliminar una serie
    public function eliminar(): bool
    {
        try {
            $this->pdo->beginTransaction();

            // Si hay una imagen, eliminarla
            if ($this->id_image) {
                // Actualizar la serie para eliminar la referencia a la imagen
                $sql = "UPDATE serie SET id_image = NULL WHERE id_serie = :id_serie";
                pdo($this->pdo, $sql, ['id_serie' => $this->id_serie]);

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
            $sql = "DELETE FROM actor_serie WHERE id_serie = :id_serie";
            pdo($this->pdo, $sql, ['id_serie' => $this->id_serie]);

            // Eliminar favoritos de usuarios
            $sql = "DELETE FROM usuario_serie WHERE id_serie = :id_serie";
            pdo($this->pdo, $sql, ['id_serie' => $this->id_serie]);

            // Eliminar la serie
            $sql = "DELETE FROM serie WHERE id_serie = :id_serie";
            pdo($this->pdo, $sql, ['id_serie' => $this->id_serie]);

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // Obtener actores para esta serie
    public function obtenerActores(): array
    {
        if (!$this->id_serie) {
            return [];
        }

        $sql = "SELECT a.id_actor, a.nombre, a.apellido, a.picture
        FROM actor_serie asr
        JOIN actor a ON asr.id_actor = a.id_actor
        WHERE asr.id_serie = :id_serie";

        return pdo($this->pdo, $sql, ['id_serie' => $this->id_serie])->fetchAll();
    }

    // Obtener IDs de actores para esta serie
    public function obtenerIdsActores(): array
    {
        if (!$this->id_serie) {
            return [];
        }

        $sql = "SELECT id_actor FROM actor_serie WHERE id_serie = :id_serie";
        return pdo($this->pdo, $sql, ['id_serie' => $this->id_serie])->fetchAll(PDO::FETCH_COLUMN);
    }

    // Verificar si una serie está en favoritos del usuario
    public function esFavorita(int $id_usuario): bool
    {
        if (!$this->id_serie) {
            return false;
        }

        $sql = "SELECT COUNT(*) FROM usuario_serie 
        WHERE id_usuario = :id_usuario AND id_serie = :id_serie";

        $count = pdo($this->pdo, $sql, [
            'id_usuario' => $id_usuario,
            'id_serie' => $this->id_serie
        ])->fetchColumn();

        return (bool)$count;
    }

    // Obtener valoración y comentario del usuario para esta serie
    public function obtenerValoracionUsuario(int $id_usuario): ?array
    {
        if (!$this->id_serie) {
            return null;
        }

        $sql = "SELECT valoracion, comentario FROM usuario_serie 
        WHERE id_usuario = :id_usuario AND id_serie = :id_serie";

        $result = pdo($this->pdo, $sql, [
            'id_usuario' => $id_usuario,
            'id_serie' => $this->id_serie
        ])->fetch();

        return $result ?: null;
    }

    // Añadir o actualizar favorito del usuario
    public function agregarAFavoritos(int $id_usuario, ?int $valoracion = null, ?string $comentario = null): bool
    {
        if (!$this->id_serie) {
            return false;
        }

        try {
            $sql = "INSERT INTO usuario_serie (id_usuario, id_serie, valoracion, comentario) 
            VALUES (:id_usuario, :id_serie, :valoracion, :comentario)
            ON DUPLICATE KEY UPDATE valoracion = VALUES(valoracion), comentario = VALUES(comentario)";

            pdo($this->pdo, $sql, [
                'id_usuario' => $id_usuario,
                'id_serie' => $this->id_serie,
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
        if (!$this->id_serie) {
            return false;
        }

        try {
            $sql = "DELETE FROM usuario_serie 
            WHERE id_usuario = :id_usuario AND id_serie = :id_serie";

            pdo($this->pdo, $sql, [
                'id_usuario' => $id_usuario,
                'id_serie' => $this->id_serie
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

        // Generar nombre basado en el nombre de la serie con prefijo "foto_"
        $nombre_limpio = $this->limpiarNombreArchivo($this->nombre);
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'foto_' . $nombre_limpio . '.' . $extension;

        // Asegurar que el nombre sea único
        $contador = 1;
        $filename_original = $filename;
        while (file_exists($this->uploads_dir . $filename)) {
            $filename = 'foto_' . $nombre_limpio . '_' . $contador . '.' . $extension;
            $contador++;
        }

        $destination = $this->uploads_dir . $filename;

        try {
            $imagick = new \Imagick($temp);
            $imagick->thumbnailImage(400, 600, true);
            $imagick->writeImage($destination);

            $this->image_file = $filename;
            $this->image_alt = $alt;

            return true;
        } catch (Exception $e) {
            if (file_exists($destination)) {
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
            'id_serie' => $this->id_serie,
            'nombre' => $this->nombre,
            'anio_estreno' => $this->anio_estreno,
            'n_temporadas' => $this->n_temporadas,
            'id_plataforma' => $this->id_plataforma,
            'plataforma' => $this->plataforma,
            'id_image' => $this->id_image,
            'image_file' => $this->image_file,
            'image_alt' => $this->image_alt
        ];
    }

    // Añadir este método a la clase Serie
    public static function obtenerUltimas(PDO $pdo, int $limite = 6): array
    {
        $sql = "SELECT s.id_serie, s.nombre, s.anio_estreno, s.n_temporadas,
        pl.nombre AS plataforma,
        pl.picture AS plataforma_picture,
        i.archivo AS image_file,
        i.alt AS image_alt,
        GROUP_CONCAT(CONCAT(a.nombre, ' ', a.apellido) SEPARATOR '|') AS actores
        FROM serie AS s
        JOIN plataforma AS pl ON s.id_plataforma = pl.id_plataforma
        LEFT JOIN image AS i ON s.id_image = i.id_image
        LEFT JOIN actor_serie AS as_table ON s.id_serie = as_table.id_serie
        LEFT JOIN actor AS a ON as_table.id_actor = a.id_actor
        GROUP BY s.id_serie
        ORDER BY s.id_serie DESC
        LIMIT :limite;";

        return pdo($pdo, $sql, ['limite' => $limite])->fetchAll();
    }

    // Obtener la valoración media de la serie
    public function obtenerValoracionMedia(): ?float
    {
        if (!$this->id_serie) {
            return null;
        }

        $sql = "SELECT AVG(valoracion) as media 
        FROM usuario_serie 
        WHERE id_serie = :id_serie 
        AND valoracion IS NOT NULL";

        $resultado = pdo($this->pdo, $sql, ['id_serie' => $this->id_serie])->fetch();

        return $resultado['media'] ? round((float)$resultado['media'], 1) : null;
    }

    // Obtener el número de valoraciones
    public function obtenerNumeroValoraciones(): int
    {
        if (!$this->id_serie) {
            return 0;
        }

        $sql = "SELECT COUNT(*) as total 
        FROM usuario_serie 
        WHERE id_serie = :id_serie 
        AND valoracion IS NOT NULL";

        $resultado = pdo($this->pdo, $sql, ['id_serie' => $this->id_serie])->fetch();

        return (int)$resultado['total'];
    }

    // Obtener los comentarios más recientes
    public function obtenerComentariosRecientes(int $limite = 3): array
    {
        if (!$this->id_serie) {
            return [];
        }

        // Consulta modificada para usar solo los campos disponibles
        $sql = "SELECT us.comentario, us.valoracion, us.id_usuario
        FROM usuario_serie us
        WHERE us.id_serie = :id_serie 
        AND us.comentario IS NOT NULL AND us.comentario != ''
        ORDER BY us.id_usuario DESC
        LIMIT :limite";

        return pdo($this->pdo, $sql, [
            'id_serie' => $this->id_serie,
            'limite' => $limite
        ])->fetchAll();
    }

    // Método para buscar por nombre con orden opcional
    public static function buscar(PDO $pdo, string $termino, string $orden = ''): array
    {
        $sql = "SELECT s.id_serie, s.nombre, s.anio_estreno, s.n_temporadas,
            pl.nombre AS plataforma,
            i.archivo AS image_file,
            i.alt AS image_alt
            FROM serie AS s
            JOIN plataforma AS pl ON s.id_plataforma = pl.id_plataforma
            LEFT JOIN image AS i ON s.id_image = i.id_image
            WHERE s.nombre LIKE :termino";

        switch ($orden) {
            case 'nombre_asc':
                $sql .= " ORDER BY s.nombre ASC";
                break;
            case 'nombre_desc':
                $sql .= " ORDER BY s.nombre DESC";
                break;
            case 'anio_asc':
                $sql .= " ORDER BY s.anio_estreno ASC";
                break;
            case 'anio_desc':
            default:
                $sql .= " ORDER BY s.anio_estreno DESC";
                break;
        }

        $param = ['termino' => '%' . $termino . '%'];
        return pdo($pdo, $sql, $param)->fetchAll();
    }

    // Método para filtrar y ordenar sin búsqueda
    public static function filtrar(PDO $pdo, string $orden = ''): array
    {
        $sql = "SELECT s.id_serie, s.nombre, s.anio_estreno, s.n_temporadas,
            pl.nombre AS plataforma,
            i.archivo AS image_file,
            i.alt AS image_alt
            FROM serie AS s
            JOIN plataforma AS pl ON s.id_plataforma = pl.id_plataforma
            LEFT JOIN image AS i ON s.id_image = i.id_image";

        switch ($orden) {
            case 'nombre_asc':
                $sql .= " ORDER BY s.nombre ASC";
                break;
            case 'nombre_desc':
                $sql .= " ORDER BY s.nombre DESC";
                break;
            case 'anio_asc':
                $sql .= " ORDER BY s.anio_estreno ASC";
                break;
            case 'anio_desc':
            default:
                $sql .= " ORDER BY s.anio_estreno DESC";
                break;
        }

        return pdo($pdo, $sql)->fetchAll();
    }
}
