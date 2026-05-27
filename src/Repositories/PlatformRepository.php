<?php
/**
 * GAC - Repositorio de Plataformas
 * 
 * Maneja el acceso a datos de plataformas
 * 
 * @package Gac\Repositories
 */

namespace Gac\Repositories;

use Gac\Helpers\Database;
use PDO;
use PDOException;

class PlatformRepository
{
    /**
     * Obtener todas las plataformas
     *
     * @return array
     */
    public function findAll(): array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                SELECT id, name, display_name, enabled, config
                FROM platforms
                ORDER BY display_name ASC
            ");

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener plataformas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualizar estado habilitado de una plataforma
     */
    public function setEnabled(string $name, bool $enabled): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                UPDATE platforms
                SET enabled = :enabled
                WHERE name = :name
            ");

            $stmt->execute([
                'name' => $name,
                'enabled' => $enabled ? 1 : 0,
            ]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error al actualizar plataforma '{$name}': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener todas las plataformas habilitadas
     * 
     * @return array
     */
    public function findAllEnabled(): array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                SELECT 
                    id,
                    name,
                    display_name,
                    enabled,
                    config
                FROM platforms
                WHERE enabled = 1
                ORDER BY display_name ASC
            ");
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener plataformas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener plataforma por nombre (slug)
     * 
     * @param string $name Nombre/slug de la plataforma
     * @return array|null
     */
    public function findByName(string $name): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                SELECT 
                    id,
                    name,
                    display_name,
                    enabled,
                    config
                FROM platforms
                WHERE name = :name
            ");
            
            $stmt->execute(['name' => $name]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Error al obtener plataforma por nombre: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener plataforma por ID
     * 
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                SELECT 
                    id,
                    name,
                    display_name,
                    enabled,
                    config
                FROM platforms
                WHERE id = :id
            ");
            
            $stmt->execute(['id' => $id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Error al obtener plataforma por ID: " . $e->getMessage());
            return null;
        }
    }
}