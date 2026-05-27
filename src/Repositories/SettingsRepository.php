<?php
/**
 * GAC - Repositorio de Settings
 * 
 * Maneja el acceso a datos de configuraciones
 * 
 * @package Gac\Repositories
 */

namespace Gac\Repositories;

use Gac\Helpers\Database;
use PDO;
use PDOException;

class SettingsRepository
{
    /**
     * Cache de settings
     */
    private static array $cache = [];

    /**
     * Obtener un setting por nombre
     * 
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get(string $name, $default = null)
    {
        // Verificar cache
        if (isset(self::$cache[$name])) {
            return self::$cache[$name];
        }

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                SELECT value, type
                FROM settings
                WHERE name = :name
            ");
            
            $stmt->execute(['name' => $name]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $value = $this->castValue($result['value'], $result['type'] ?? 'string');
                self::$cache[$name] = $value;
                return $value;
            }
            
            return $default;
        } catch (PDOException $e) {
            error_log("Error al obtener setting '{$name}': " . $e->getMessage());
            return $default;
        }
    }

    /**
     * Obtener múltiples settings por patrón
     * 
     * @param string $pattern Patrón SQL LIKE (ej: 'NETFLIX_%')
     * @return array Array asociativo [name => value]
     */
    public function getByPattern(string $pattern): array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                SELECT name, value, type
                FROM settings
                WHERE name LIKE :pattern
                ORDER BY name ASC
            ");
            
            $stmt->execute(['pattern' => $pattern]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $settings = [];
            foreach ($results as $result) {
                $value = $this->castValue($result['value'], $result['type'] ?? 'string');
                $settings[$result['name']] = $value;
                // Actualizar cache
                self::$cache[$result['name']] = $value;
            }
            
            return $settings;
        } catch (PDOException $e) {
            error_log("Error al obtener settings por patrón '{$pattern}': " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener todos los asuntos de email para una plataforma
     * 
     * @param string $platform Slug de la plataforma (ej: 'netflix', 'disney')
     * @return array Array de asuntos
     */
    public function getEmailSubjectsForPlatform(string $platform): array
    {
        $platformUpper = strtoupper($platform);
        $pattern = "{$platformUpper}_%";
        
        $settings = $this->getByPattern($pattern);
        
        // Filtrar solo claves de asunto: PLATAFORMA_N (N = número)
        $subjects = [];
        foreach ($settings as $name => $value) {
            if (preg_match('/^' . preg_quote($platformUpper, '/') . '_(\d+)$/', $name)) {
                $subjects[] = $value;
            }
        }
        
        return $subjects;
    }

    /**
     * Obtener asuntos con nombre de setting (para administración)
     *
     * @return array<int, array{name: string, value: string}>
     */
    public function getEmailSubjectsDetailed(string $platform): array
    {
        $platformUpper = strtoupper($platform);
        $pattern = "{$platformUpper}_%";
        $settings = $this->getByPattern($pattern);
        $subjects = [];

        foreach ($settings as $name => $value) {
            if (preg_match('/^' . preg_quote($platformUpper, '/') . '_(\d+)$/', $name, $matches)) {
                $subjects[(int) $matches[1]] = [
                    'name' => $name,
                    'value' => $value,
                ];
            }
        }

        ksort($subjects);

        return array_values($subjects);
    }

    /**
     * Generar el siguiente nombre de setting para un asunto (ej. NETFLIX_5)
     */
    public function getNextSubjectKey(string $platform): string
    {
        $platformUpper = strtoupper($platform);
        $settings = $this->getByPattern("{$platformUpper}_%");
        $max = 0;

        foreach (array_keys($settings) as $name) {
            if (preg_match('/^' . preg_quote($platformUpper, '/') . '_(\d+)$/', $name, $matches)) {
                $max = max($max, (int) $matches[1]);
            }
        }

        return "{$platformUpper}_" . ($max + 1);
    }

    /**
     * Guardar o actualizar un setting
     */
    public function save(string $name, string $value, string $type = 'string', ?string $description = null): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                INSERT INTO settings (name, value, type, description)
                VALUES (:name, :value, :type, :description)
                ON DUPLICATE KEY UPDATE
                    value = VALUES(value),
                    type = VALUES(type),
                    description = COALESCE(VALUES(description), description)
            ");

            $stmt->execute([
                'name' => $name,
                'value' => $value,
                'type' => $type,
                'description' => $description,
            ]);

            self::$cache[$name] = $this->castValue($value, $type);

            return true;
        } catch (PDOException $e) {
            error_log("Error al guardar setting '{$name}': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar un setting por nombre
     */
    public function delete(string $name): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("DELETE FROM settings WHERE name = :name");
            $stmt->execute(['name' => $name]);
            unset(self::$cache[$name]);

            return true;
        } catch (PDOException $e) {
            error_log("Error al eliminar setting '{$name}': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Normalizar texto de asunto para comparación (solo espacios, sin fuzzy)
     */
    public static function normalizeSubjectValue(string $value): string
    {
        $value = trim($value);
        $value = preg_replace('/\s+/u', ' ', $value);

        return $value;
    }

    /**
     * Buscar asunto con el mismo texto (coincidencia exacta, opcionalmente sin distinguir mayúsculas)
     *
     * @return array{name: string, value: string}|null
     */
    public function findSubjectByExactValue(string $platform, string $value, ?string $excludeName = null): ?array
    {
        $normalized = self::normalizeSubjectValue($value);
        if ($normalized === '') {
            return null;
        }

        foreach ($this->getEmailSubjectsDetailed($platform) as $subject) {
            if ($excludeName !== null && strcasecmp($subject['name'], $excludeName) === 0) {
                continue;
            }

            $existing = self::normalizeSubjectValue((string) $subject['value']);
            if (strcasecmp($existing, $normalized) === 0) {
                return $subject;
            }
        }

        return null;
    }

    /**
     * Verificar que un nombre de setting pertenece a una plataforma
     */
    public function isSubjectKeyForPlatform(string $name, string $platform): bool
    {
        $platformUpper = strtoupper($platform);

        return (bool) preg_match('/^' . preg_quote($platformUpper, '/') . '_\d+$/', $name);
    }

    /**
     * Obtener todos los asuntos de email para todas las plataformas
     * 
     * @return array Array asociativo [platform => [subjects]]
     */
    public function getAllEmailSubjects(): array
    {
        $platforms = ['netflix', 'disney', 'prime', 'spotify', 'crunchyroll', 'paramount', 'chatgpt', 'canva'];
        $allSubjects = [];
        
        foreach ($platforms as $platform) {
            $subjects = $this->getEmailSubjectsForPlatform($platform);
            if (!empty($subjects)) {
                $allSubjects[$platform] = $subjects;
            }
        }
        
        return $allSubjects;
    }

    /**
     * Verificar si una plataforma está habilitada
     * 
     * @param string $platform
     * @return bool
     */
    public function isPlatformEnabled(string $platform): bool
    {
        $platformUpper = strtoupper($platform);
        $settingName = "HABILITAR_{$platformUpper}";
        
        $value = $this->get($settingName, '0');
        
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Convertir valor según tipo
     * 
     * @param string $value
     * @param string $type
     * @return mixed
     */
    private function castValue(string $value, string $type)
    {
        switch ($type) {
            case 'boolean':
            case 'bool':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
            case 'int':
                return (int) $value;
            case 'float':
            case 'double':
                return (float) $value;
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    /**
     * Limpiar cache
     */
    public static function clearCache(): void
    {
        self::$cache = [];
    }
}