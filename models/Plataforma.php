<?php

declare(strict_types=1);

class Plataforma
{
    private PDO $pdo;
    private string $uploads_dir;

    // Propiedades que mapean a columnas de la base de datos
    private ?int $id_plataforma = null;
    private string $nombre = '';
    private float $precio_mensual = 0.0;
    private string $pais_origen = '';
    private int $anio_lanzamiento = 0;
    private int $usuarios_activos = 0;
    private ?string $picture = null;

    // Constructor
    public function __construct(PDO $pdo, string $uploads_dir = '../uploads/')
    {
        $this->pdo = $pdo;
        $this->uploads_dir = $uploads_dir;
    }

    // Métodos para acceder a propiedades básicas
    public function getId(): ?int
    {
        return $this->id_plataforma;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;
        return $this;
    }

    public function setPrecioMensual(float $precio_mensual): self
    {
        $this->precio_mensual = $precio_mensual;
        return $this;
    }

    public function setPaisOrigen(string $pais_origen): self
    {
        $this->pais_origen = $pais_origen;
        return $this;
    }

    public function setAnioLanzamiento(int $anio_lanzamiento): self
    {
        $this->anio_lanzamiento = $anio_lanzamiento;
        return $this;
    }

    public function setUsuariosActivos(int $usuarios_activos): self
    {
        $this->usuarios_activos = $usuarios_activos;
        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    // Cargar una plataforma por ID
    public function cargar(int $id): bool
    {
        $sql = "SELECT id_plataforma, nombre, precio_mensual, pais_origen, 
                anio_lanzamiento, usuarios_activos, picture 
                FROM plataforma 
                WHERE id_plataforma = :id_plataforma";

        $plataforma = pdo($this->pdo, $sql, ['id_plataforma' => $id])->fetch();

        if (!$plataforma) {
            return false;
        }

        $this->id_plataforma = (int)$plataforma['id_plataforma'];
        $this->nombre = $plataforma['nombre'];
        $this->precio_mensual = (float)$plataforma['precio_mensual'];
        $this->pais_origen = $plataforma['pais_origen'];
        $this->anio_lanzamiento = (int)$plataforma['anio_lanzamiento'];
        $this->usuarios_activos = (int)$plataforma['usuarios_activos'];
        $this->picture = $plataforma['picture'];

        return true;
    }

    // Obtener todas las plataformas
    public static function obtenerTodas(PDO $pdo): array
    {
        $sql = "SELECT p.id_plataforma, p.nombre, p.precio_mensual, p.pais_origen, 
                p.anio_lanzamiento, p.usuarios_activos, p.picture,
                (SELECT COUNT(*) FROM pelicula WHERE id_plataforma = p.id_plataforma) AS num_peliculas,
                (SELECT COUNT(*) FROM serie WHERE id_plataforma = p.id_plataforma) AS num_series
                FROM plataforma AS p
                ORDER BY p.nombre ASC";

        return pdo($pdo, $sql)->fetchAll();
    }

    // Obtener todas las plataformas con conteo de contenido (para admin)
    public static function obtenerTodasConContenido(PDO $pdo): array
    {
        $sql = "SELECT 
                p.id_plataforma, 
                p.nombre, 
                p.precio_mensual, 
                p.pais_origen,
                p.anio_lanzamiento,
                p.usuarios_activos,
                p.picture,
                COUNT(DISTINCT pe.id_pelicula) AS num_peliculas,
                COUNT(DISTINCT s.id_serie) AS num_series
                FROM plataforma AS p
                LEFT JOIN pelicula AS pe ON p.id_plataforma = pe.id_plataforma
                LEFT JOIN serie AS s ON p.id_plataforma = s.id_plataforma
                GROUP BY p.id_plataforma
                ORDER BY p.id_plataforma DESC";

        return pdo($pdo, $sql)->fetchAll();
    }

    // Guardar (crear o actualizar) una plataforma
    public function guardar(): bool
    {
        try {
            // Verificar si ya existe una plataforma con el mismo nombre (solo para nuevas plataformas)
            if (!$this->id_plataforma) {
                $sql = "SELECT COUNT(*) FROM plataforma WHERE nombre = :nombre";
                $count = pdo($this->pdo, $sql, ['nombre' => $this->nombre])->fetchColumn();

                if ($count > 0) {
                    throw new Exception("Ya existe una plataforma con el nombre '{$this->nombre}'", 1);
                }
            } else {
                // Para actualización, verificar que no exista otra plataforma con el mismo nombre
                $sql = "SELECT COUNT(*) FROM plataforma WHERE nombre = :nombre AND id_plataforma != :id_plataforma";
                $count = pdo($this->pdo, $sql, [
                    'nombre' => $this->nombre,
                    'id_plataforma' => $this->id_plataforma
                ])->fetchColumn();

                if ($count > 0) {
                    throw new Exception("Ya existe otra plataforma con el nombre '{$this->nombre}'", 1);
                }
            }

            $this->pdo->beginTransaction();

            if ($this->id_plataforma) {
                // Actualizar plataforma existente
                if ($this->picture) {
                    $sql = "UPDATE plataforma
                SET nombre = :nombre, 
                precio_mensual = :precio_mensual, 
                pais_origen = :pais_origen,
                anio_lanzamiento = :anio_lanzamiento, 
                usuarios_activos = :usuarios_activos,
                picture = :picture
                WHERE id_plataforma = :id_plataforma";
                    $params = [
                        'nombre' => $this->nombre,
                        'precio_mensual' => $this->precio_mensual,
                        'pais_origen' => $this->pais_origen,
                        'anio_lanzamiento' => $this->anio_lanzamiento,
                        'usuarios_activos' => $this->usuarios_activos,
                        'picture' => $this->picture,
                        'id_plataforma' => $this->id_plataforma
                    ];
                } else {
                    $sql = "UPDATE plataforma
                SET nombre = :nombre, 
                precio_mensual = :precio_mensual, 
                pais_origen = :pais_origen,
                anio_lanzamiento = :anio_lanzamiento, 
                usuarios_activos = :usuarios_activos
                WHERE id_plataforma = :id_plataforma";
                    $params = [
                        'nombre' => $this->nombre,
                        'precio_mensual' => $this->precio_mensual,
                        'pais_origen' => $this->pais_origen,
                        'anio_lanzamiento' => $this->anio_lanzamiento,
                        'usuarios_activos' => $this->usuarios_activos,
                        'id_plataforma' => $this->id_plataforma
                    ];
                }
            } else {
                // Insertar nueva plataforma
                if ($this->picture) {
                    $sql = "INSERT INTO plataforma (nombre, precio_mensual, pais_origen, anio_lanzamiento, usuarios_activos, picture)
                VALUES (:nombre, :precio_mensual, :pais_origen, :anio_lanzamiento, :usuarios_activos, :picture)";
                    $params = [
                        'nombre' => $this->nombre,
                        'precio_mensual' => $this->precio_mensual,
                        'pais_origen' => $this->pais_origen,
                        'anio_lanzamiento' => $this->anio_lanzamiento,
                        'usuarios_activos' => $this->usuarios_activos,
                        'picture' => $this->picture
                    ];
                } else {
                    $sql = "INSERT INTO plataforma (nombre, precio_mensual, pais_origen, anio_lanzamiento, usuarios_activos)
                VALUES (:nombre, :precio_mensual, :pais_origen, :anio_lanzamiento, :usuarios_activos)";
                    $params = [
                        'nombre' => $this->nombre,
                        'precio_mensual' => $this->precio_mensual,
                        'pais_origen' => $this->pais_origen,
                        'anio_lanzamiento' => $this->anio_lanzamiento,
                        'usuarios_activos' => $this->usuarios_activos
                    ];
                }
            }

            pdo($this->pdo, $sql, $params);

            // Si es una nueva plataforma, obtener el ID
            if (!$this->id_plataforma) {
                $this->id_plataforma = (int)$this->pdo->lastInsertId();
            }

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            if ($e->errorInfo[1] === 1062) {
                // Error de entrada duplicada
                throw new Exception("Nombre de plataforma ya en uso", 1);
            }
            throw $e;
        }
    }

    // Eliminar una plataforma
    public function eliminar(): bool
    {
        try {
            $this->pdo->beginTransaction();

            // Verificar si hay contenido asociado
            $sql = "SELECT 
                    (SELECT COUNT(*) FROM pelicula WHERE id_plataforma = :id_plataforma1) +
                    (SELECT COUNT(*) FROM serie WHERE id_plataforma = :id_plataforma2) AS total";
            $contenido = pdo($this->pdo, $sql, [
                'id_plataforma1' => $this->id_plataforma,
                'id_plataforma2' => $this->id_plataforma
            ])->fetch();

            if ((int)$contenido['total'] > 0) {
                throw new Exception("No se puede eliminar la plataforma porque tiene contenido asociado", 2);
            }

            // Eliminar relaciones con usuarios
            $sql = "DELETE FROM usuarios_plataformas WHERE id_plataforma = :id_plataforma";
            pdo($this->pdo, $sql, ['id_plataforma' => $this->id_plataforma]);

            // Eliminar la plataforma
            $sql = "DELETE FROM plataforma WHERE id_plataforma = :id_plataforma";
            pdo($this->pdo, $sql, ['id_plataforma' => $this->id_plataforma]);

            // Eliminar la imagen si existe
            if ($this->picture) {
                $path = $this->uploads_dir . $this->picture;
                if (file_exists($path)) {
                    unlink($path);
                }
            }

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            if ($e->getCode() == '23000') { // Error de clave foránea
                throw new Exception("No se puede eliminar la plataforma porque tiene contenido asociado", 2);
            }
            throw $e;
        }
    }

    // Obtener películas de esta plataforma
    public function obtenerPeliculas(): array
    {
        if (!$this->id_plataforma) {
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
                WHERE p.id_plataforma = :id_plataforma
                GROUP BY p.id_pelicula
                ORDER BY p.id_pelicula DESC";

        return pdo($this->pdo, $sql, ['id_plataforma' => $this->id_plataforma])->fetchAll();
    }

    // Obtener series de esta plataforma
    public function obtenerSeries(): array
    {
        if (!$this->id_plataforma) {
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
                WHERE s.id_plataforma = :id_plataforma
                GROUP BY s.id_serie
                ORDER BY s.id_serie DESC";

        return pdo($this->pdo, $sql, ['id_plataforma' => $this->id_plataforma])->fetchAll();
    }

    // Verificar si una plataforma es favorita para un usuario
    public function esFavorita(int $id_usuario): bool
    {
        if (!$this->id_plataforma) {
            return false;
        }

        $sql = "SELECT 1 FROM usuarios_plataformas 
                WHERE id_usuario = :id_usuario AND id_plataforma = :id_plataforma";

        $result = pdo($this->pdo, $sql, [
            'id_usuario' => $id_usuario,
            'id_plataforma' => $this->id_plataforma
        ])->fetchColumn();

        return (bool)$result;
    }

    // Añadir a favoritos del usuario
    public function agregarAFavoritos(int $id_usuario): bool
    {
        if (!$this->id_plataforma) {
            return false;
        }

        try {
            $sql = "INSERT IGNORE INTO usuarios_plataformas (id_usuario, id_plataforma) 
                    VALUES (:id_usuario, :id_plataforma)";

            pdo($this->pdo, $sql, [
                'id_usuario' => $id_usuario,
                'id_plataforma' => $this->id_plataforma
            ]);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // Eliminar de favoritos del usuario
    public function eliminarDeFavoritos(int $id_usuario): bool
    {
        if (!$this->id_plataforma) {
            return false;
        }

        try {
            $sql = "DELETE FROM usuarios_plataformas 
                    WHERE id_usuario = :id_usuario AND id_plataforma = :id_plataforma";

            pdo($this->pdo, $sql, [
                'id_usuario' => $id_usuario,
                'id_plataforma' => $this->id_plataforma
            ]);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // Subir y procesar una imagen
    public function subirImagen(array $file): bool
    {
        if ($file['error'] !== 0) {
            return false;
        }

        $temp = $file['tmp_name'];

        // Generar nombre basado en el nombre de la plataforma con prefijo "foto_"
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
            $imagick->thumbnailImage(1200, 900, true);
            $imagick->writeImage($destination);

            $this->picture = $filename;

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
            'id_plataforma' => $this->id_plataforma,
            'nombre' => $this->nombre,
            'precio_mensual' => $this->precio_mensual,
            'pais_origen' => $this->pais_origen,
            'anio_lanzamiento' => $this->anio_lanzamiento,
            'usuarios_activos' => $this->usuarios_activos,
            'picture' => $this->picture
        ];
    }

    // Añadir este método a la clase Plataforma
    public static function obtenerFavoritasUsuario(PDO $pdo, int $id_usuario): array
    {
        $sql = "SELECT pl.id_plataforma, pl.nombre, pl.picture
    FROM plataforma pl
    INNER JOIN usuarios_plataformas up ON pl.id_plataforma = up.id_plataforma
    WHERE up.id_usuario = :user_id";

        return pdo($pdo, $sql, ['user_id' => $id_usuario])->fetchAll(PDO::FETCH_ASSOC);
    }

    // Método para buscar por nombre con orden opcional
    public static function buscar(PDO $pdo, string $termino, string $orden = ''): array
    {
        $sql = "SELECT p.id_plataforma, p.nombre, p.precio_mensual, p.pais_origen, 
            p.anio_lanzamiento, p.usuarios_activos, p.picture,
            (SELECT COUNT(*) FROM pelicula WHERE id_plataforma = p.id_plataforma) AS num_peliculas,
            (SELECT COUNT(*) FROM serie WHERE id_plataforma = p.id_plataforma) AS num_series
            FROM plataforma AS p
            WHERE p.nombre LIKE :termino";

        switch ($orden) {
            case 'nombre_asc':
                $sql .= " ORDER BY p.nombre ASC";
                break;
            case 'nombre_desc':
                $sql .= " ORDER BY p.nombre DESC";
                break;
            case 'anio_asc':
                $sql .= " ORDER BY p.anio_lanzamiento ASC";
                break;
            case 'anio_desc':
            default:
                $sql .= " ORDER BY p.anio_lanzamiento DESC";
                break;
        }

        $param = ['termino' => '%' . $termino . '%'];
        return pdo($pdo, $sql, $param)->fetchAll();
    }

    // Método para filtrar y ordenar sin búsqueda
    public static function filtrar(PDO $pdo, string $orden = ''): array
    {
        $sql = "SELECT p.id_plataforma, p.nombre, p.precio_mensual, p.pais_origen, 
            p.anio_lanzamiento, p.usuarios_activos, p.picture,
            (SELECT COUNT(*) FROM pelicula WHERE id_plataforma = p.id_plataforma) AS num_peliculas,
            (SELECT COUNT(*) FROM serie WHERE id_plataforma = p.id_plataforma) AS num_series
            FROM plataforma AS p";

        switch ($orden) {
            case 'nombre_asc':
                $sql .= " ORDER BY p.nombre ASC";
                break;
            case 'nombre_desc':
                $sql .= " ORDER BY p.nombre DESC";
                break;
            case 'anio_asc':
                $sql .= " ORDER BY p.anio_lanzamiento ASC";
                break;
            case 'anio_desc':
            default:
                $sql .= " ORDER BY p.anio_lanzamiento DESC";
                break;
        }

        return pdo($pdo, $sql)->fetchAll();
    }
}
