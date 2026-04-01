<?php
/**
 * GAC - Servicio de Extracción de Códigos
 * 
 * Extrae códigos de verificación de emails usando expresiones regulares
 * específicas para cada plataforma
 * 
 * @package Gac\Services\Code
 */

namespace Gac\Services\Code;

class CodeExtractorService
{
    /**
     * Patrones regex para extraer códigos por plataforma
     * 
     * @var array
     */
    private array $codePatterns = [
        'netflix' => [
            // Código de 6 dígitos (formato común: 123456 o 123 456)
            '/\b(\d{6})\b/',
            // Código con espacios: 123 456
            '/\b(\d{3}\s?\d{3})\b/',
            // Código en texto: "Tu código es: 123456"
            '/código[:\s]+(\d{6})/i',
            // Código entre etiquetas HTML
            '/<[^>]*>(\d{6})<\/[^>]*>/',
        ],
        'disney' => [
            // Código de 6-8 dígitos
            '/\b(\d{6,8})\b/',
            // Código con espacios
            '/\b(\d{3,4}\s?\d{3,4})\b/',
            // Código en texto
            '/código[:\s]+(\d{6,8})/i',
            '/code[:\s]+(\d{6,8})/i',
        ],
        'prime' => [
            // Código de 6 dígitos de Amazon
            '/\b(\d{6})\b/',
            // Código OTP de Amazon
            '/OTP[:\s]+(\d{6})/i',
            '/verification code[:\s]+(\d{6})/i',
            // Código en enlace o botón
            '/code[:\s]+(\d{6})/i',
        ],
        'spotify' => [
            // Código de 6 dígitos
            '/\b(\d{6})\b/',
            // Código de verificación
            '/verification code[:\s]+(\d{6})/i',
            '/código[:\s]+(\d{6})/i',
        ],
        'crunchyroll' => [
            // Código de 6 dígitos
            '/\b(\d{6})\b/',
            // Código de acceso
            '/código de acceso[:\s]+(\d{6})/i',
            '/access code[:\s]+(\d{6})/i',
        ],
        'paramount' => [
            // Código de 6 dígitos
            '/\b(\d{6})\b/',
            // Código de acceso
            '/código[:\s]+(\d{6})/i',
            '/code[:\s]+(\d{6})/i',
        ],
        'chatgpt' => [
            // Código de 6 dígitos
            '/\b(\d{6})\b/',
            // Código de verificación
            '/verification code[:\s]+(\d{6})/i',
            '/código[:\s]+(\d{6})/i',
        ],
        'canva' => [
            // Código de 6 dígitos
            '/\b(\d{6})\b/',
            // Código de verificación
            '/verification code[:\s]+(\d{6})/i',
            '/código[:\s]+(\d{6})/i',
        ],
    ];

    /**
     * Patrones para identificar plataforma desde el asunto
     * 
     * @var array
     */
    private array $platformIdentifiers = [
        'netflix' => [
            '/netflix/i',
            '/código de acceso temporal/i',
            '/código de inicio de sesión/i',
            '/restablecimiento de contraseña/i',
        ],
        'disney' => [
            '/disney\+?/i',
            '/disney plus/i',
            '/código de acceso único/i',
        ],
        'prime' => [
            '/amazon/i',
            '/prime video/i',
            '/sign-in attempt/i',
            '/intento de inicio de sesión/i',
        ],
        'spotify' => [
            '/spotify/i',
        ],
        'crunchyroll' => [
            '/crunchyroll/i',
            '/código de acceso/i',
        ],
        'paramount' => [
            '/paramount/i',
            '/paramount plus/i',
        ],
        'chatgpt' => [
            '/chatgpt/i',
            '/openai/i',
            '/cambio de contraseña/i',
            '/cambio de correo/i',
        ],
        'canva' => [
            '/canva/i',
        ],
    ];

    /**
     * Identificar plataforma desde el asunto del email
     * 
     * @param string $subject Asunto del email
     * @return string|null Slug de la plataforma o null si no se identifica
     */
    public function identifyPlatform(string $subject): ?string
    {
        $subject = mb_strtolower($subject, 'UTF-8');
        
        foreach ($this->platformIdentifiers as $platform => $patterns) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $subject)) {
                    return $platform;
                }
            }
        }
        
        return null;
    }

    /**
     * Extraer código de un email
     * 
     * @param array $email Datos del email (subject, body_text, body_html)
     * @param string|null $platform Plataforma específica (opcional, si no se proporciona se intenta identificar)
     * @return array|null Datos del código extraído o null si no se encuentra
     */
    public function extractCode(array $email, ?string $platform = null): ?array
    {
        // Si no se proporciona plataforma, intentar identificarla
        if ($platform === null) {
            $platform = $this->identifyPlatform($email['subject'] ?? '');
            
            if ($platform === null) {
                return null;
            }
        }
        
        // Verificar que la plataforma existe
        if (!isset($this->codePatterns[$platform])) {
            return null;
        }
        
        // Obtener texto para buscar (preferir texto plano, luego HTML)
        $text = $email['body_text'] ?? '';
        if (empty($text) && !empty($email['body_html'])) {
            // Extraer texto del HTML si no hay texto plano
            $text = strip_tags($email['body_html']);
        }
        
        if (empty($text) && !empty($email['body'])) {
            $text = $email['body'];
        }
        
        if (empty($text)) {
            return null;
        }
        
        // Intentar extraer código con cada patrón
        $patterns = $this->codePatterns[$platform];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $code = $this->cleanCode($matches[1]);
                
                // Validar código
                if ($this->validateCode($code, $platform)) {
                    return [
                        'code' => $code,
                        'platform' => $platform,
                        'subject' => $email['subject'] ?? '',
                        'from' => $email['from'] ?? '',
                        'date' => $email['date'] ?? date('Y-m-d H:i:s'),
                        'timestamp' => $email['timestamp'] ?? time(),
                        'extracted_at' => date('Y-m-d H:i:s'),
                    ];
                }
            }
        }
        
        return null;
    }

    /**
     * Extraer múltiples códigos de un array de emails
     * 
     * @param array $emails Array de emails
     * @return array Array de códigos extraídos
     */
    public function extractCodes(array $emails): array
    {
        $codes = [];
        
        foreach ($emails as $email) {
            $code = $this->extractCode($email);
            
            if ($code !== null) {
                $codes[] = $code;
            }
        }
        
        return $codes;
    }

    /**
     * Limpiar código extraído (remover espacios, guiones, etc.)
     * 
     * @param string $code
     * @return string
     */
    private function cleanCode(string $code): string
    {
        // Remover espacios, guiones y otros caracteres no numéricos
        $code = preg_replace('/[^\d]/', '', $code);
        
        return $code;
    }

    /**
     * Validar código extraído
     * 
     * @param string $code
     * @param string $platform
     * @return bool
     */
    private function validateCode(string $code, string $platform): bool
    {
        // Validación básica: debe ser numérico y tener longitud válida
        if (!ctype_digit($code)) {
            return false;
        }
        
        // Longitud mínima y máxima según plataforma
        $lengths = [
            'netflix' => [6, 6],
            'disney' => [6, 8],
            'prime' => [6, 6],
            'spotify' => [6, 6],
            'crunchyroll' => [6, 6],
            'paramount' => [6, 6],
            'chatgpt' => [6, 6],
            'canva' => [6, 6],
        ];
        
        if (!isset($lengths[$platform])) {
            // Longitud por defecto: 6 dígitos
            return strlen($code) >= 6 && strlen($code) <= 8;
        }
        
        [$min, $max] = $lengths[$platform];
        $length = strlen($code);
        
        return $length >= $min && $length <= $max;
    }

    /**
     * Obtener patrones de código para una plataforma
     * 
     * @param string $platform
     * @return array
     */
    public function getPatternsForPlatform(string $platform): array
    {
        return $this->codePatterns[$platform] ?? [];
    }

    /**
     * Agregar patrón personalizado para una plataforma
     * 
     * @param string $platform
     * @param string $pattern Patrón regex
     * @return void
     */
    public function addPattern(string $platform, string $pattern): void
    {
        if (!isset($this->codePatterns[$platform])) {
            $this->codePatterns[$platform] = [];
        }
        
        // Agregar al inicio para prioridad
        array_unshift($this->codePatterns[$platform], $pattern);
    }

    /**
     * Agregar identificador de plataforma personalizado
     * 
     * @param string $platform
     * @param string $pattern Patrón regex
     * @return void
     */
    public function addPlatformIdentifier(string $platform, string $pattern): void
    {
        if (!isset($this->platformIdentifiers[$platform])) {
            $this->platformIdentifiers[$platform] = [];
        }
        
        array_unshift($this->platformIdentifiers[$platform], $pattern);
    }
}