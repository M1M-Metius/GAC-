<?php
/**
 * GAC - Controlador de API
 * Endpoints para consulta de códigos por dominio
 * 
 * @package Gac\Controllers
 */

namespace Gac\Controllers;

use Gac\Core\Request;

class ApiController
{
    /**
     * Consultar código por dominio
     * 
     * Parámetros:
     * - email (string, requerido): Email del usuario
     * - username (string, requerido): Nombre de usuario
     * - platform (string, requerido): Plataforma (netflix, disney, etc.)
     * - domain (string, opcional): Dominio del email para filtrar
     * 
     * Respuesta exitosa:
     * {
     *   "success": true,
     *   "code": "ABC123",
     *   "platform": "netflix",
     *   "email": "usuario@dominio.com",
     *   "received_at": "2024-01-01 12:00:00"
     * }
     */
    public function consultByDomain(Request $request): void
    {
        if ($request->method() !== 'POST') {
            json_response([
                'success' => false,
                'message' => 'Método no permitido'
            ], 405);
            return;
        }

        $email = $request->input('email', '');
        $username = $request->input('username', '');
        $platform = $request->input('platform', '');
        $domain = $request->input('domain', '');

        // Validación
        if (empty($email) || empty($username) || empty($platform)) {
            json_response([
                'success' => false,
                'message' => 'Los campos email, username y platform son requeridos'
            ], 400);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            json_response([
                'success' => false,
                'message' => 'El email no es válido'
            ], 400);
            return;
        }

        // Extraer dominio del email si no se proporciona
        if (empty($domain)) {
            $emailParts = explode('@', $email);
            $domain = $emailParts[1] ?? '';
        }

        if (empty($domain)) {
            json_response([
                'success' => false,
                'message' => 'No se pudo determinar el dominio del email'
            ], 400);
            return;
        }

        // TODO: Implementar consulta real cuando esté la BD lista
        // Buscar código disponible para:
        // - Email que coincida con el dominio
        // - Plataforma especificada
        // - Estado: available
        
        json_response([
            'success' => false,
            'message' => 'Sistema en desarrollo. La funcionalidad estará disponible pronto.',
            'params' => [
                'email' => $email,
                'username' => $username,
                'platform' => $platform,
                'domain' => $domain
            ]
        ], 503);
    }

    /**
     * Listar códigos disponibles por dominio
     * 
     * Parámetros:
     * - domain (string, requerido): Dominio del email
     * - platform (string, opcional): Filtrar por plataforma
     * - limit (int, opcional): Límite de resultados (default: 10)
     */
    public function listByDomain(Request $request): void
    {
        if ($request->method() !== 'GET') {
            json_response([
                'success' => false,
                'message' => 'Método no permitido'
            ], 405);
            return;
        }

        $domain = $request->get('domain', '');
        $platform = $request->get('platform', '');
        $limit = (int)($request->get('limit', 10));

        if (empty($domain)) {
            json_response([
                'success' => false,
                'message' => 'El parámetro domain es requerido'
            ], 400);
            return;
        }

        // TODO: Implementar listado cuando esté la BD lista
        json_response([
            'success' => false,
            'message' => 'Sistema en desarrollo',
            'params' => [
                'domain' => $domain,
                'platform' => $platform,
                'limit' => $limit
            ]
        ], 503);
    }
}
