# 🔍 Servicio de Extracción de Códigos - Documentación

## 📋 Descripción

El `CodeExtractorService` es responsable de extraer códigos de verificación de los emails leídos, utilizando expresiones regulares específicas para cada plataforma.

---

## 🏗️ Arquitectura

```
Email (desde ImapService)
    ↓
CodeExtractorService
    ↓
Identificar Plataforma (desde asunto)
    ↓
Aplicar Patrones Regex
    ↓
Validar Código
    ↓
Retornar Código Extraído
```

---

## 📦 Componentes

### 1. `CodeExtractorService`
- **Ubicación:** `src/Services/Code/CodeExtractorService.php`
- **Función:** Extracción de códigos usando regex por plataforma
- **Métodos principales:**
  - `identifyPlatform($subject)` - Identificar plataforma desde asunto
  - `extractCode($email, $platform)` - Extraer código de un email
  - `extractCodes($emails)` - Extraer códigos de múltiples emails

### 2. `PlatformRepository`
- **Ubicación:** `src/Repositories/PlatformRepository.php`
- **Función:** Acceso a datos de plataformas
- **Métodos:**
  - `findAllEnabled()` - Todas las plataformas habilitadas
  - `findByName($name)` - Plataforma por nombre
  - `findById($id)` - Plataforma por ID

---

## 🔧 Patrones Regex por Plataforma

### Netflix
- Código de 6 dígitos: `/\b(\d{6})\b/`
- Código con espacios: `/\b(\d{3}\s?\d{3})\b/`
- Código en texto: `/código[:\s]+(\d{6})/i`
- Código en HTML: `/<[^>]*>(\d{6})<\/[^>]*>/`

### Disney+
- Código de 6-8 dígitos: `/\b(\d{6,8})\b/`
- Código con espacios: `/\b(\d{3,4}\s?\d{3,4})\b/`
- Código en texto: `/código[:\s]+(\d{6,8})/i`

### Amazon Prime Video
- Código de 6 dígitos: `/\b(\d{6})\b/`
- Código OTP: `/OTP[:\s]+(\d{6})/i`
- Código de verificación: `/verification code[:\s]+(\d{6})/i`

### Spotify, Crunchyroll, Paramount+, ChatGPT, Canva
- Código de 6 dígitos estándar
- Variaciones con texto descriptivo

---

## 💻 Uso

### Ejemplo Básico

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Gac\Config\AppConfig;
use Gac\Services\Email\ImapService;
use Gac\Services\Code\CodeExtractorService;

// Cargar configuración
AppConfig::load();

// Crear servicios
$imapService = new ImapService();
$extractorService = new CodeExtractorService();

// Leer emails
$results = $imapService->readAllAccounts();

foreach ($results as $result) {
    if ($result['success'] && !empty($result['emails'])) {
        // Extraer códigos de los emails
        $codes = $extractorService->extractCodes($result['emails']);
        
        foreach ($codes as $code) {
            echo "Plataforma: {$code['platform']}\n";
            echo "Código: {$code['code']}\n";
            echo "Asunto: {$code['subject']}\n";
            echo "Fecha: {$code['date']}\n";
            echo "---\n";
        }
    }
}
```

### Extraer Código de un Email Específico

```php
<?php
use Gac\Services\Code\CodeExtractorService;

$extractorService = new CodeExtractorService();

$email = [
    'subject' => 'Tu código de acceso temporal de Netflix',
    'body_text' => 'Tu código de verificación es: 123456',
    'from' => 'noreply@netflix.com',
    'date' => '2024-01-15 10:30:00',
    'timestamp' => 1705315800
];

// Extraer código (identifica plataforma automáticamente)
$code = $extractorService->extractCode($email);

if ($code) {
    echo "Código encontrado: {$code['code']}\n";
    echo "Plataforma: {$code['platform']}\n";
} else {
    echo "No se pudo extraer código\n";
}
```

### Extraer Código con Plataforma Específica

```php
<?php
$email = [
    'subject' => 'Verificación de cuenta',
    'body_text' => 'Tu código es 123456',
    'from' => 'noreply@example.com',
    'date' => '2024-01-15 10:30:00'
];

// Especificar plataforma manualmente
$code = $extractorService->extractCode($email, 'netflix');

if ($code) {
    echo "Código: {$code['code']}\n";
}
```

### Identificar Plataforma

```php
<?php
$subject = 'Tu código de acceso único para Disney+';
$platform = $extractorService->identifyPlatform($subject);

if ($platform) {
    echo "Plataforma identificada: {$platform}\n";
} else {
    echo "No se pudo identificar la plataforma\n";
}
```

---

## 📊 Estructura de Datos

### Código Extraído

```php
[
    'code' => '123456',                    // Código extraído (limpio)
    'platform' => 'netflix',              // Slug de la plataforma
    'subject' => 'Tu código de acceso...', // Asunto del email
    'from' => 'noreply@netflix.com',      // Remitente
    'date' => '2024-01-15 10:30:00',      // Fecha formateada
    'timestamp' => 1705315800,            // Timestamp Unix
    'extracted_at' => '2024-01-15 10:35:00' // Fecha de extracción
]
```

---

## 🔍 Características

### ✅ Funcionalidades Implementadas

- ✅ Identificación automática de plataforma desde asunto
- ✅ Múltiples patrones regex por plataforma
- ✅ Extracción de códigos de texto plano y HTML
- ✅ Validación de códigos (longitud, formato)
- ✅ Limpieza automática de códigos (remover espacios, guiones)
- ✅ Soporte para 8 plataformas principales
- ✅ Patrones personalizables

### 🔄 Próximas Mejoras

- [ ] Cargar patrones desde base de datos (configurables)
- [ ] Soporte para códigos alfanuméricos
- [ ] Detección de códigos en imágenes (OCR)
- [ ] Cache de códigos extraídos
- [ ] Estadísticas de extracción

---

## ⚙️ Configuración

### Agregar Patrón Personalizado

```php
<?php
$extractorService = new CodeExtractorService();

// Agregar patrón para una plataforma existente
$extractorService->addPattern('netflix', '/código especial[:\s]+(\d{6})/i');

// Agregar identificador de plataforma
$extractorService->addPlatformIdentifier('netflix', '/nuevo asunto/i');
```

### Validación de Códigos

Los códigos se validan automáticamente según:
- **Formato:** Solo dígitos numéricos
- **Longitud:** Según plataforma
  - Netflix: 6 dígitos
  - Disney+: 6-8 dígitos
  - Otras: 6 dígitos

---

## 🧪 Testing

### Probar Extracción

```php
<?php
$testEmails = [
    [
        'subject' => 'Tu código de acceso temporal de Netflix',
        'body_text' => 'Tu código es: 123456',
        'from' => 'noreply@netflix.com',
        'date' => date('Y-m-d H:i:s'),
        'timestamp' => time()
    ],
    [
        'subject' => 'Disney+ código de acceso',
        'body_text' => 'Código: 789012',
        'from' => 'noreply@disney.com',
        'date' => date('Y-m-d H:i:s'),
        'timestamp' => time()
    ]
];

$extractorService = new CodeExtractorService();
$codes = $extractorService->extractCodes($testEmails);

foreach ($codes as $code) {
    echo "✅ {$code['platform']}: {$code['code']}\n";
}
```

---

## 🔗 Integración con ImapService

### Flujo Completo

```php
<?php
use Gac\Services\Email\ImapService;
use Gac\Services\Code\CodeExtractorService;
use Gac\Repositories\CodeRepository;

// 1. Leer emails
$imapService = new ImapService();
$emailResults = $imapService->readAllAccounts();

// 2. Extraer códigos
$extractorService = new CodeExtractorService();
$allCodes = [];

foreach ($emailResults as $result) {
    if ($result['success'] && !empty($result['emails'])) {
        $codes = $extractorService->extractCodes($result['emails']);
        $allCodes = array_merge($allCodes, $codes);
    }
}

// 3. Guardar códigos en BD (próximo paso)
// $codeRepository = new CodeRepository();
// foreach ($allCodes as $code) {
//     $codeRepository->save($code);
// }
```

---

## 📚 Referencias

- [PHP Regular Expressions](https://www.php.net/manual/en/book.pcre.php)
- Documentación del sistema: `ANALISIS_SISTEMA_ORIGINAL.md`
- Servicio IMAP: `IMAP_SERVICE.md`

---

**Última actualización:** 2024