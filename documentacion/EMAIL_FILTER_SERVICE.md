# 🔍 Servicio de Filtrado de Emails - Documentación

## 📋 Descripción

El `EmailFilterService` filtra emails por asunto usando los patrones configurados en la tabla `settings`. Este servicio permite identificar qué emails corresponden a cada plataforma basándose en los asuntos configurados.

---

## 🏗️ Arquitectura

```
Emails (desde ImapService)
    ↓
EmailFilterService
    ↓
SettingsRepository (obtiene asuntos configurados)
    ↓
Comparación de asuntos
    ↓
Emails filtrados con plataforma identificada
```

---

## 📦 Componentes

### 1. `SettingsRepository`
- **Ubicación:** `src/Repositories/SettingsRepository.php`
- **Función:** Acceso a datos de configuraciones
- **Métodos principales:**
  - `get($name, $default)` - Obtener un setting
  - `getByPattern($pattern)` - Obtener settings por patrón
  - `getEmailSubjectsForPlatform($platform)` - Asuntos de una plataforma
  - `getAllEmailSubjects()` - Todos los asuntos por plataforma
  - `isPlatformEnabled($platform)` - Verificar si plataforma está habilitada

### 2. `EmailFilterService`
- **Ubicación:** `src/Services/Email/EmailFilterService.php`
- **Función:** Filtrado de emails por asunto
- **Métodos principales:**
  - `filterBySubject($emails)` - Filtrar emails por asunto
  - `filterByPlatform($emails, $platform)` - Filtrar por plataforma específica
  - `matchesPlatform($email, $platform)` - Verificar si email coincide
  - `matchSubjectToPlatform($subject)` - Identificar plataforma desde asunto
  - `getFilteringStats($emails)` - Obtener estadísticas de filtrado

---

## 🔧 Configuración

### Settings en Base de Datos

Los asuntos de email se almacenan en la tabla `settings` con el formato:
- `PLATAFORMA_N` donde `N` es 1, 2, 3, 4
- Ejemplo: `NETFLIX_1`, `NETFLIX_2`, `DISNEY_1`, etc.

### Habilitación de Plataformas

Cada plataforma tiene un setting `HABILITAR_PLATAFORMA`:
- `HABILITAR_NETFLIX`, `HABILITAR_DISNEY`, etc.
- Valor `'1'` = habilitada, `'0'` = deshabilitada

### Script de Seed

Ejecutar `database/seed_settings.sql` para insertar los settings iniciales.

---

## 💻 Uso

### Ejemplo Básico

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Gac\Config\AppConfig;
use Gac\Services\Email\ImapService;
use Gac\Services\Email\EmailFilterService;

// Cargar configuración
AppConfig::load();

// Crear servicios
$imapService = new ImapService();
$filterService = new EmailFilterService();

// Leer emails
$results = $imapService->readAllAccounts();

foreach ($results as $result) {
    if ($result['success'] && !empty($result['emails'])) {
        // Filtrar emails por asunto
        $filtered = $filterService->filterBySubject($result['emails']);
        
        echo "Emails totales: " . count($result['emails']) . "\n";
        echo "Emails filtrados: " . count($filtered) . "\n";
        
        foreach ($filtered as $email) {
            echo "  - Plataforma: {$email['matched_platform']}\n";
            echo "  - Asunto: {$email['subject']}\n";
            echo "  - Asunto coincidente: {$email['matched_subject']}\n";
        }
    }
}
```

### Filtrar por Plataforma Específica

```php
<?php
use Gac\Services\Email\EmailFilterService;

$filterService = new EmailFilterService();

$emails = [
    [
        'subject' => 'Tu código de acceso temporal de Netflix',
        'body_text' => '...',
        'from' => 'noreply@netflix.com',
        'date' => '2024-01-15 10:30:00'
    ],
    [
        'subject' => 'Tu código de acceso único para Disney+',
        'body_text' => '...',
        'from' => 'noreply@disney.com',
        'date' => '2024-01-15 10:31:00'
    ]
];

// Filtrar solo emails de Netflix
$netflixEmails = $filterService->filterByPlatform($emails, 'netflix');

foreach ($netflixEmails as $email) {
    echo "Email de Netflix: {$email['subject']}\n";
}
```

### Verificar si un Email Coincide

```php
<?php
$email = [
    'subject' => 'Netflix: Tu código de inicio de sesión',
    'body_text' => '...'
];

$filterService = new EmailFilterService();

if ($filterService->matchesPlatform($email, 'netflix')) {
    echo "Este email es de Netflix\n";
}
```

### Identificar Plataforma desde Asunto

```php
<?php
$filterService = new EmailFilterService();

$subject = 'Tu código de acceso único para Disney+';
$platform = $filterService->matchSubjectToPlatform($subject);

if ($platform) {
    echo "Plataforma identificada: {$platform}\n";
} else {
    echo "No se pudo identificar la plataforma\n";
}
```

### Obtener Estadísticas

```php
<?php
$filterService = new EmailFilterService();

$stats = $filterService->getFilteringStats($emails);

echo "Total de emails: {$stats['total']}\n";
echo "Emails filtrados: {$stats['filtered']}\n";
echo "Por plataforma:\n";

foreach ($stats['by_platform'] as $platform => $count) {
    echo "  - {$platform}: {$count}\n";
}
```

---

## 🔍 Algoritmo de Coincidencia

El servicio usa múltiples estrategias para comparar asuntos:

1. **Comparación Exacta:** Asunto y patrón son idénticos
2. **Contains:** El patrón está contenido en el asunto
3. **Contains Inverso:** El asunto está contenido en el patrón
4. **Similitud:** Cálculo de similitud usando distancia de Levenshtein (≥80%)

### Ejemplo de Coincidencias

```
Asunto: "Tu código de acceso temporal de Netflix"
Patrón: "Tu código de acceso temporal de Netflix"
✅ Coincidencia exacta

Asunto: "Netflix: Tu código de inicio de sesión"
Patrón: "Tu código de inicio de sesión"
✅ Contains (patrón contenido en asunto)

Asunto: "Código Netflix"
Patrón: "Tu código de acceso temporal de Netflix"
✅ Contains inverso (asunto contenido en patrón)

Asunto: "Tu codigo de acceso temporal de Netflix" (sin tilde)
Patrón: "Tu código de acceso temporal de Netflix" (con tilde)
✅ Similitud alta (≥80%)
```

---

## 📊 Estructura de Datos

### Email Filtrado

```php
[
    // Datos originales del email
    'message_number' => 123,
    'subject' => 'Tu código de acceso temporal de Netflix',
    'from' => 'noreply@netflix.com',
    'date' => '2024-01-15 10:30:00',
    'body_text' => '...',
    
    // Información agregada por el filtro
    'matched_platform' => 'netflix',
    'matched_subject' => 'Tu código de acceso temporal de Netflix'
]
```

---

## 🔄 Integración Completa

### Flujo: Lectura → Filtrado → Extracción

```php
<?php
use Gac\Services\Email\ImapService;
use Gac\Services\Email\EmailFilterService;
use Gac\Services\Code\CodeExtractorService;

// 1. Leer emails
$imapService = new ImapService();
$emailResults = $imapService->readAllAccounts();

// 2. Filtrar emails por asunto
$filterService = new EmailFilterService();
$allFilteredEmails = [];

foreach ($emailResults as $result) {
    if ($result['success'] && !empty($result['emails'])) {
        $filtered = $filterService->filterBySubject($result['emails']);
        $allFilteredEmails = array_merge($allFilteredEmails, $filtered);
    }
}

// 3. Extraer códigos (usando plataforma identificada)
$extractorService = new CodeExtractorService();
$codes = [];

foreach ($allFilteredEmails as $email) {
    // Usar la plataforma identificada por el filtro
    $platform = $email['matched_platform'] ?? null;
    $code = $extractorService->extractCode($email, $platform);
    
    if ($code) {
        $codes[] = $code;
    }
}
```

---

## ⚙️ Configuración Avanzada

### Agregar Nuevo Asunto

```sql
-- Agregar nuevo asunto para Netflix
INSERT INTO settings (name, value, type, description) VALUES
('NETFLIX_5', 'Nuevo asunto de Netflix', 'string', 'Asunto 5 para emails de Netflix');
```

Luego recargar patrones:

```php
$filterService->reloadPatterns();
```

### Deshabilitar Plataforma

```sql
-- Deshabilitar Canva
UPDATE settings SET value = '0' WHERE name = 'HABILITAR_CANVA';
```

---

## 🧪 Testing

### Probar Filtrado

```php
<?php
$testEmails = [
    [
        'subject' => 'Tu código de acceso temporal de Netflix',
        'body_text' => '...',
        'from' => 'noreply@netflix.com',
        'date' => date('Y-m-d H:i:s')
    ],
    [
        'subject' => 'Email no relacionado',
        'body_text' => '...',
        'from' => 'spam@example.com',
        'date' => date('Y-m-d H:i:s')
    ],
    [
        'subject' => 'Tu código de acceso único para Disney+',
        'body_text' => '...',
        'from' => 'noreply@disney.com',
        'date' => date('Y-m-d H:i:s')
    ]
];

$filterService = new EmailFilterService();
$filtered = $filterService->filterBySubject($testEmails);

echo "Emails filtrados: " . count($filtered) . "\n";
// Debería retornar 2 (Netflix y Disney+)
```

---

## 📚 Referencias

- Documentación del sistema: `ANALISIS_SISTEMA_ORIGINAL.md`
- Servicio IMAP: `IMAP_SERVICE.md`
- Servicio de Extracción: `CODE_EXTRACTOR_SERVICE.md`
- Script de seed: `database/seed_settings.sql`

---

**Última actualización:** 2024