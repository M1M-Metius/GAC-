<?php
/**
 * GAC - Servicio IMAP
 * 
 * Maneja la lectura de emails desde servidores IMAP
 * 
 * @package Gac\Services\Email
 */

namespace Gac\Services\Email;

use Gac\Repositories\EmailAccountRepository;

class ImapService
{
    /**
     * Repositorio de cuentas de email
     */
    private EmailAccountRepository $emailAccountRepository;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->emailAccountRepository = new EmailAccountRepository();
    }

    /**
     * Leer emails de todas las cuentas IMAP habilitadas
     * 
     * @return array Array con información de emails leídos
     */
    public function readAllAccounts(): array
    {
        $results = [];
        
        // Obtener todas las cuentas IMAP habilitadas
        $accounts = $this->emailAccountRepository->findByType('imap');
        
        foreach ($accounts as $account) {
            try {
                $emails = $this->readAccount($account);
                $results[] = [
                    'account_id' => $account['id'],
                    'account_email' => $account['email'],
                    'success' => true,
                    'emails_count' => count($emails),
                    'emails' => $emails
                ];
                
                // Actualizar estado de sincronización
                $this->emailAccountRepository->updateSyncStatus(
                    $account['id'],
                    'success'
                );
            } catch (\Exception $e) {
                error_log("Error al leer cuenta IMAP {$account['email']}: " . $e->getMessage());
                
                $results[] = [
                    'account_id' => $account['id'],
                    'account_email' => $account['email'],
                    'success' => false,
                    'error' => $e->getMessage(),
                    'emails_count' => 0
                ];
                
                // Actualizar estado de sincronización con error
                $this->emailAccountRepository->updateSyncStatus(
                    $account['id'],
                    'error',
                    $e->getMessage()
                );
            }
        }
        
        return $results;
    }

    /**
     * Leer emails de una cuenta IMAP específica
     * 
     * @param array $account Datos de la cuenta de email
     * @return array Array de emails leídos
     * @throws \Exception
     */
    public function readAccount(array $account): array
    {
        // Decodificar configuración del proveedor
        $config = $this->parseProviderConfig($account['provider_config'] ?? '{}');
        
        // Construir string de conexión IMAP
        $server = $config['imap_server'] ?? '';
        $port = $config['imap_port'] ?? 993;
        $encryption = $config['imap_encryption'] ?? 'ssl';
        $validateCert = $config['imap_validate_cert'] ?? true;
        
        if (empty($server)) {
            throw new \Exception("Servidor IMAP no configurado");
        }
        
        $username = $config['imap_user'] ?? '';
        $password = $config['imap_password'] ?? '';
        
        if (empty($username) || empty($password)) {
            throw new \Exception("Credenciales IMAP no configuradas");
        }
        
        // Construir string de conexión
        $connectionString = $this->buildConnectionString($server, $port, $encryption, $validateCert);
        
        // Conectar a IMAP
        $mailbox = @imap_open($connectionString, $username, $password, OP_READONLY);
        
        if (!$mailbox) {
            $error = imap_last_error();
            throw new \Exception("Error al conectar con IMAP: " . ($error ?: "Error desconocido"));
        }
        
        try {
            // Obtener lista de emails (últimos 50)
            $emails = [];
            $messageCount = imap_num_msg($mailbox);
            
            if ($messageCount > 0) {
                // Leer desde el más reciente (últimos 50 mensajes)
                $start = max(1, $messageCount - 49);
                
                for ($msgNum = $messageCount; $msgNum >= $start; $msgNum--) {
                    try {
                        $email = $this->readEmail($mailbox, $msgNum);
                        if ($email) {
                            $emails[] = $email;
                        }
                    } catch (\Exception $e) {
                        error_log("Error al leer email #{$msgNum}: " . $e->getMessage());
                        // Continuar con el siguiente email
                        continue;
                    }
                }
            }
            
            return $emails;
        } finally {
            // Cerrar conexión
            @imap_close($mailbox);
        }
    }

    /**
     * Leer un email específico
     * 
     * @param resource $mailbox Recurso IMAP
     * @param int $msgNum Número de mensaje
     * @return array|null Datos del email o null si hay error
     */
    private function readEmail($mailbox, int $msgNum): ?array
    {
        // Obtener headers
        $header = imap_headerinfo($mailbox, $msgNum);
        
        if (!$header) {
            return null;
        }
        
        // Obtener asunto
        $subject = isset($header->subject) ? $this->decodeMimeHeader($header->subject) : '';
        
        // Obtener remitente
        $from = isset($header->from[0]) ? $this->getEmailAddress($header->from[0]) : '';
        $fromName = isset($header->from[0]) ? $this->getEmailName($header->from[0]) : '';
        
        // Obtener fecha
        $date = isset($header->date) ? strtotime($header->date) : time();
        
        // Obtener cuerpo del mensaje
        $body = $this->getEmailBody($mailbox, $msgNum);
        
        return [
            'message_number' => $msgNum,
            'subject' => $subject,
            'from' => $from,
            'from_name' => $fromName,
            'date' => date('Y-m-d H:i:s', $date),
            'timestamp' => $date,
            'body' => $body,
            'body_text' => $this->extractTextFromBody($body),
            'body_html' => $this->extractHtmlFromBody($body)
        ];
    }

    /**
     * Obtener cuerpo del email
     * 
     * @param resource $mailbox
     * @param int $msgNum
     * @return string
     */
    private function getEmailBody($mailbox, int $msgNum): string
    {
        // Intentar obtener texto plano primero
        $body = @imap_body($mailbox, $msgNum);
        
        if ($body === false) {
            // Si falla, intentar obtener estructura y decodificar
            $structure = imap_fetchstructure($mailbox, $msgNum);
            if ($structure) {
                $body = $this->decodeBody($mailbox, $msgNum, $structure);
            }
        }
        
        return $body ?: '';
    }

    /**
     * Decodificar cuerpo del email según estructura
     * 
     * @param resource $mailbox
     * @param int $msgNum
     * @param object $structure
     * @return string
     */
    private function decodeBody($mailbox, int $msgNum, $structure): string
    {
        $body = '';
        
        if (!isset($structure->parts)) {
            // Mensaje simple
            $body = imap_body($mailbox, $msgNum);
            if ($structure->encoding == 3) { // BASE64
                $body = base64_decode($body);
            } elseif ($structure->encoding == 4) { // QUOTED-PRINTABLE
                $body = quoted_printable_decode($body);
            }
        } else {
            // Mensaje multiparte
            foreach ($structure->parts as $partNum => $part) {
                $partBody = imap_fetchbody($mailbox, $msgNum, $partNum + 1);
                
                if ($part->encoding == 3) { // BASE64
                    $partBody = base64_decode($partBody);
                } elseif ($part->encoding == 4) { // QUOTED-PRINTABLE
                    $partBody = quoted_printable_decode($partBody);
                }
                
                // Preferir texto plano
                if ($part->subtype == 'PLAIN' || $part->subtype == 'HTML') {
                    $body .= $partBody;
                }
            }
        }
        
        return $body;
    }

    /**
     * Extraer texto plano del cuerpo
     * 
     * @param string $body
     * @return string
     */
    private function extractTextFromBody(string $body): string
    {
        // Si contiene HTML, extraer texto
        if (strip_tags($body) !== $body) {
            $body = strip_tags($body);
        }
        
        // Decodificar entidades HTML
        $body = html_entity_decode($body, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Limpiar espacios en blanco
        $body = preg_replace('/\s+/', ' ', $body);
        
        return trim($body);
    }

    /**
     * Extraer HTML del cuerpo
     * 
     * @param string $body
     * @return string
     */
    private function extractHtmlFromBody(string $body): string
    {
        // Si contiene HTML, devolverlo
        if (strip_tags($body) !== $body) {
            return $body;
        }
        
        return '';
    }

    /**
     * Construir string de conexión IMAP
     * 
     * @param string $server
     * @param int $port
     * @param string $encryption 'ssl' | 'tls' | 'none'
     * @param bool $validateCert
     * @return string
     */
    private function buildConnectionString(string $server, int $port, string $encryption, bool $validateCert): string
    {
        $flags = [];
        
        // Agregar flags según encriptación
        if ($encryption === 'ssl') {
            $flags[] = 'ssl';
        } elseif ($encryption === 'tls') {
            $flags[] = 'tls';
        }
        
        // Validación de certificado
        if (!$validateCert) {
            $flags[] = 'novalidate-cert';
        }
        
        // Buzón (INBOX por defecto)
        $mailbox = 'INBOX';
        
        // Construir string
        $connectionString = "{" . $server . ":" . $port;
        
        if (!empty($flags)) {
            $connectionString .= "/" . implode('/', $flags);
        }
        
        $connectionString .= "}" . $mailbox;
        
        return $connectionString;
    }

    /**
     * Decodificar header MIME
     * 
     * @param string $header
     * @return string
     */
    private function decodeMimeHeader(string $header): string
    {
        // Decodificar si está codificado
        if (function_exists('imap_mime_header_decode')) {
            $decoded = imap_mime_header_decode($header);
            $result = '';
            foreach ($decoded as $part) {
                $result .= $part->text;
            }
            return $result;
        }
        
        return $header;
    }

    /**
     * Obtener dirección de email desde objeto
     * 
     * @param object $addressObj
     * @return string
     */
    private function getEmailAddress($addressObj): string
    {
        if (isset($addressObj->mailbox) && isset($addressObj->host)) {
            return $addressObj->mailbox . '@' . $addressObj->host;
        }
        
        return '';
    }

    /**
     * Obtener nombre desde objeto de dirección
     * 
     * @param object $addressObj
     * @return string
     */
    private function getEmailName($addressObj): string
    {
        if (isset($addressObj->personal)) {
            return $this->decodeMimeHeader($addressObj->personal);
        }
        
        return '';
    }

    /**
     * Parsear configuración del proveedor (JSON)
     * 
     * @param string $configJson
     * @return array
     */
    private function parseProviderConfig(string $configJson): array
    {
        $config = json_decode($configJson, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }
        
        return $config ?: [];
    }
}