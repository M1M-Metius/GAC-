# 📧 Servicio IMAP - Documentación

## 📋 Descripción

El `ImapService` es responsable de leer emails desde servidores IMAP configurados en el sistema. Este servicio se conecta a las cuentas de email habilitadas y extrae los emails para su posterior procesamiento.

---

## 🏗️ Arquitectura

```
ImapService
    ↓
EmailAccountRepository (obtiene cuentas IMAP habilitadas)
    ↓
Conexión IMAP (usando extensión PHP imap)
    ↓
Lectura de emails
    ↓
Retorno de datos estructurados
```

---

## 📦 Componentes

### 1. `Database` Helper
- **Ubicación:** `src/Helpers/Database.php`
- **Función:** Maneja conexiones PDO a las bases de datos
- **Métodos:**
  - `getConnection()` - Conexión a BD operativa
  - `getWarehouseConnection()` - Conexión a BD warehouse

### 2. `EmailAccountRepository`
- **Ubicación:** `src/Repositories/EmailAccountRepository.php`
- **Función:** Acceso a datos de cuentas de email
- **Métodos:**
  - `findAllEnabled()` - Todas las cuentas habilitadas
  - `findByType($type)` - Cuentas por tipo ('imap' o 'gmail')
  - `findById($id)` - Cuenta específica
  - `updateSyncStatus($id, $status, $errorMessage)` - Actualizar estado de sincronización

### 3. `ImapService`
- **Ubicación:** `src/Services/Email/ImapService.php`
- **Función:** Lectura de emails desde servidores IMAP
- **Métodos principales:**
  - `readAllAccounts()` - Leer todas las cuentas IMAP habilitadas
  - `readAccount($account)` - Leer una cuenta específica

---

## 🔧 Configuración

### Requisitos

1. **Extensión PHP IMAP:**
   ```bash
   # Ubuntu/Debian
   sudo apt-get install php-imap
   
   # Habilitar en php.ini
   extension=imap.so
   ```

2. **Configuración en Base de Datos:**
   - Las cuentas IMAP deben estar registradas en la tabla `email_accounts`
   - Campo `type` debe ser `'imap'`
   - Campo `enabled` debe ser `1`
   - Campo `provider_config` debe contener JSON con:
     ```json
     {
       "imap_server": "imap.dominio.com",
       "imap_port": 993,
       "imap_encryption": "ssl",
       "imap_validate_cert": true,
       "imap_user": "usuario@dominio.com",
       "imap_password": "contraseña"
     }
     ```

---

## 💻 Uso

### Ejemplo Básico

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Gac\Config\AppConfig;
use Gac\Services\Email\ImapService;

// Cargar configuración
AppConfig::load();

// Crear instancia del servicio
$imapService = new ImapService();

// Leer todas las cuentas IMAP habilitadas
$results = $imapService->readAllAccounts();

foreach ($results as $result) {
    if ($result['success']) {
        echo "Cuenta: {$result['account_email']}\n";
        echo "Emails leídos: {$result['emails_count']}\n";
        
        foreach ($result['emails'] as $email) {
            echo "  - Asunto: {$email['subject']}\n";
            echo "  - De: {$email['from']}\n";
            echo "  - Fecha: {$email['date']}\n";
        }
    } else {
        echo "Error en {$result['account_email']}: {$result['error']}\n";
    }
}
```

### Leer una Cuenta Específica

```php
<?php
use Gac\Repositories\EmailAccountRepository;
use Gac\Services\Email\ImapService;

$emailAccountRepository = new EmailAccountRepository();
$imapService = new ImapService();

// Obtener cuenta por ID
$account = $emailAccountRepository->findById(1);

if ($account && $account['type'] === 'imap') {
    try {
        $emails = $imapService->readAccount($account);
        
        foreach ($emails as $email) {
            // Procesar cada email
            echo "Asunto: {$email['subject']}\n";
            echo "Cuerpo: {$email['body_text']}\n";
        }
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
```

---

## 📊 Estructura de Datos

### Email Retornado

```php
[
    'message_number' => 123,           // Número de mensaje en IMAP
    'subject' => 'Asunto del email',   // Asunto decodificado
    'from' => 'remitente@email.com',   // Email del remitente
    'from_name' => 'Nombre Remitente', // Nombre del remitente
    'date' => '2024-01-15 10:30:00',   // Fecha formateada
    'timestamp' => 1705315800,         // Timestamp Unix
    'body' => '...',                   // Cuerpo completo
    'body_text' => '...',              // Solo texto (sin HTML)
    'body_html' => '...'               // Solo HTML (si existe)
]
```

### Resultado de `readAllAccounts()`

```php
[
    [
        'account_id' => 1,
        'account_email' => 'cuenta@dominio.com',
        'success' => true,
        'emails_count' => 5,
        'emails' => [/* array de emails */]
    ],
    [
        'account_id' => 2,
        'account_email' => 'otra@dominio.com',
        'success' => false,
        'error' => 'Error al conectar con IMAP: ...',
        'emails_count' => 0
    ]
]
```

---

## 🔍 Características

### ✅ Funcionalidades Implementadas

- ✅ Conexión a múltiples servidores IMAP
- ✅ Soporte para SSL/TLS
- ✅ Decodificación de headers MIME
- ✅ Extracción de texto plano y HTML
- ✅ Manejo de errores robusto
- ✅ Actualización de estado de sincronización
- ✅ Lectura de últimos 50 emails por cuenta

### 🔄 Próximas Mejoras

- [ ] Filtrado por fecha (solo emails nuevos)
- [ ] Filtrado por asunto (patrones configurables)
- [ ] Soporte para múltiples buzones
- [ ] Marcar emails como leídos
- [ ] Mover emails a carpetas específicas
- [ ] Cache de emails leídos

---

## ⚠️ Consideraciones

1. **Rendimiento:**
   - El servicio lee los últimos 50 emails por cuenta
   - Para cuentas con muchos emails, considerar filtrado por fecha

2. **Seguridad:**
   - Las contraseñas se almacenan en `provider_config` (deben estar cifradas)
   - Usar conexiones SSL/TLS siempre que sea posible

3. **Errores:**
   - Los errores se registran en el log del sistema
   - El estado de sincronización se actualiza automáticamente

4. **Extensión IMAP:**
   - Verificar que la extensión esté habilitada: `php -m | grep imap`
   - En algunos servidores puede requerir configuración adicional

---

## 🧪 Testing

### Verificar Conexión IMAP

```php
<?php
$server = 'imap.dominio.com';
$port = 993;
$username = 'usuario@dominio.com';
$password = 'contraseña';

$connectionString = "{{$server}:{$port}/ssl}INBOX";
$mailbox = @imap_open($connectionString, $username, $password);

if ($mailbox) {
    echo "Conexión exitosa\n";
    echo "Mensajes: " . imap_num_msg($mailbox) . "\n";
    imap_close($mailbox);
} else {
    echo "Error: " . imap_last_error() . "\n";
}
```

---

## 📚 Referencias

- [PHP IMAP Extension](https://www.php.net/manual/en/book.imap.php)
- [IMAP Protocol](https://tools.ietf.org/html/rfc3501)
- Documentación del sistema: `ANALISIS_SISTEMA_ORIGINAL.md`

---

**Última actualización:** 2024