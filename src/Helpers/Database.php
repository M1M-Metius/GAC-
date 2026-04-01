<?php
/**
 * GAC - Helper de Base de Datos
 * 
 * Maneja conexiones PDO a las bases de datos operativa y warehouse
 * 
 * @package Gac\Helpers
 */

namespace Gac\Helpers;

use PDO;
use PDOException;

class Database
{
    /**
     * Instancia de conexión operativa
     */
    private static ?PDO $operationalConnection = null;

    /**
     * Instancia de conexión warehouse
     */
    private static ?PDO $warehouseConnection = null;

    /**
     * Obtener conexión a base de datos operativa
     * 
     * @return PDO
     * @throws PDOException
     */
    public static function getConnection(): PDO
    {
        if (self::$operationalConnection === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                DB_HOST,
                DB_PORT,
                DB_NAME,
                DB_CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE " . DB_COLLATE
            ];

            try {
                self::$operationalConnection = new PDO(
                    $dsn,
                    DB_USER,
                    DB_PASSWORD,
                    $options
                );
            } catch (PDOException $e) {
                error_log("Error de conexión a BD operativa: " . $e->getMessage());
                throw new PDOException("Error al conectar con la base de datos operativa", 0, $e);
            }
        }

        return self::$operationalConnection;
    }

    /**
     * Obtener conexión a base de datos warehouse
     * 
     * @return PDO
     * @throws PDOException
     */
    public static function getWarehouseConnection(): PDO
    {
        if (self::$warehouseConnection === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                WAREHOUSE_DB_HOST,
                WAREHOUSE_DB_PORT,
                WAREHOUSE_DB_NAME,
                DB_CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE " . DB_COLLATE
            ];

            try {
                self::$warehouseConnection = new PDO(
                    $dsn,
                    WAREHOUSE_DB_USER,
                    WAREHOUSE_DB_PASSWORD,
                    $options
                );
            } catch (PDOException $e) {
                error_log("Error de conexión a BD warehouse: " . $e->getMessage());
                throw new PDOException("Error al conectar con la base de datos warehouse", 0, $e);
            }
        }

        return self::$warehouseConnection;
    }

    /**
     * Cerrar todas las conexiones
     */
    public static function closeConnections(): void
    {
        self::$operationalConnection = null;
        self::$warehouseConnection = null;
    }
}