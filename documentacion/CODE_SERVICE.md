# 💾 Servicio de Códigos - Documentación

## 📋 Descripción

El `CodeService` maneja la lógica de negocio para la gestión de códigos, incluyendo consulta, guardado y marcado como consumido.

---

## 🏗️ Arquitectura

```
CodeController
    ↓
CodeService (lógica de negocio)
    ↓
CodeRepository (acceso a datos)
    ↓
Database (MySQL)
```

---

## 📦 Componentes

### 1. `CodeRepository`
- **Ubicación:** `src/Repositories/CodeRepository.php`
- **Función:** Acceso a datos de códigos
- **Métodos principales:**
  - `save($codeData)` - Guardar código nuevo
  - `findLatestAvailable($platformId)` - Buscar código más reciente disponible
  - `findById($id)` - Buscar código por ID
  - `markAsConsumed($codeId, $userEmail, $username)` - Marcar como consumido
  - `codeExists($code, $platformId, $emailAccountId)` - Verificar duplicados
  - `getStats()` - Obtener estadísticas
  - `saveToWarehouse($codeData)` - Guardar en histórico

### 2. `CodeService`
- **Ubicación:** `src/Services/Code/CodeService.php`
- **Función:** Lógica de negocio para códigos
- **Métodos principales:**
  - `consultCode($platformSlug, $userEmail, $username)` - Consultar código
  - `saveExtractedCode($codeData, $emailAccountId)` - Guardar código extraído
  - `getStats()` - Obtener estadísticas
  - `getEnabledPlatforms()` - Obtener plataformas habilitadas

### 3. `CodeController` (Actualizado)
- **Ubicación:** `src/Controllers/CodeController.php`
- **Función:** Manejo de requests HTTP
- **Métodos:**
  - `consult()` - Mostrar vista o procesar consulta
  - `apiConsult()` - API endpoint para consulta AJAX
  - `index()` - Listar códigos (admin)

---

## 💻 Uso

### Consulta de Código (Usuario Final)

```php
<?php
use Gac\Services\Code\CodeService;

$codeService = new CodeService();

$result = $codeService->consultCode(
    'netflix',              // Slug de plataforma
    'usuario@email.com',    // Email del usuario
    'username123'           // Username del usuario
);

if ($result['success']) {
    echo "Código: {$result['code']}\n";
    echo "Plataforma: {$result['platform']}\n";
} else {
    echo "Error: {$result['message']}\n";
}
```

### Guardar Código Extraído

```php
<?php
use Gac\Services\Code\CodeService;

$codeService = new CodeService();

$codeData = [
    'code' => '123456',
    'platform' => 'netflix',
    'from' => 'noreply@netflix.com',
    'subject' => 'Tu código de acceso temporal de Netflix',
    'date' => '2024-01-15 10:30:00',
    'origin' => 'imap'
];

$emailAccountId = 1; // ID de la cuenta de email

$codeId = $codeService->saveExtractedCode($codeData, $emailAccountId);

if ($codeId) {
    echo "Código guardado con ID: {$codeId}\n";
} else {
    echo "Error al guardar código\n";
}
```

### Obtener Estadísticas

```php
<?php
$codeService = new CodeService();
$stats = $codeService->getStats();

echo "Total: {$stats['total']}\n";
echo "Disponibles: {$stats['available']}\n";
echo "Consumidos: {$stats['consumed']}\n";

foreach ($stats['by_platform'] as $platform) {
    echo "{$platform['display_name']}: {$platform['total']} total, {$platform['available']} disponibles\n";
}
```

### Obtener Plataformas Habilitadas

```php
<?php
$codeService = new CodeService();
$platforms = $codeService->getEnabledPlatforms();

// $platforms = [
//     'netflix' => 'Netflix',
//     'disney' => 'Disney+',
//     ...
// ]
```

---

## 📊 Estructura de Datos

### Resultado de Consulta

```php
// Éxito
[
    'success' => true,
    'message' => 'Código encontrado',
    'code' => '123456',
    'platform' => 'Netflix',
    'received_at' => '2024-01-15 10:30:00'
]

// Error
[
    'success' => false,
    'message' => 'No hay códigos disponibles...',
    'code' => null
]
```

### Datos de Código en BD

```php
[
    'id' => 1,
    'email_account_id' => 1,
    'platform_id' => 1,
    'code' => '123456',
    'email_from' => 'noreply@netflix.com',
    'subject' => 'Tu código de acceso temporal de Netflix',
    'received_at' => '2024-01-15 10:30:00',
    'origin' => 'imap',
    'status' => 'available', // o 'consumed'
    'consumed_at' => null,
    'platform_name' => 'netflix',
    'platform_display_name' => 'Netflix',
    'account_email' => 'cuenta@dominio.com'
]
```

---

## 🔄 Flujo Completo

### 1. Lectura y Extracción

```php
<?php
use Gac\Services\Email\ImapService;
use Gac\Services\Email\EmailFilterService;
use Gac\Services\Code\CodeExtractorService;
use Gac\Services\Code\CodeService;

// 1. Leer emails
$imapService = new ImapService();
$emailResults = $imapService->readAllAccounts();

// 2. Filtrar por asunto
$filterService = new EmailFilterService();
$allFilteredEmails = [];

foreach ($emailResults as $result) {
    if ($result['success'] && !empty($result['emails'])) {
        $filtered = $filterService->filterBySubject($result['emails']);
        $allFilteredEmails = array_merge($allFilteredEmails, $filtered);
    }
}

// 3. Extraer códigos
$extractorService = new CodeExtractorService();
$codeService = new CodeService();

foreach ($allFilteredEmails as $email) {
    $platform = $email['matched_platform'] ?? null;
    $codeData = $extractorService->extractCode($email, $platform);
    
    if ($codeData) {
        // 4. Guardar código
        $emailAccountId = $email['account_id'] ?? 1;
        $codeService->saveExtractedCode($codeData, $emailAccountId);
    }
}
```

### 2. Consulta de Usuario

```php
<?php
// Usuario consulta código
$result = $codeService->consultCode('netflix', 'user@email.com', 'username');

// El servicio:
// 1. Valida datos
// 2. Busca código disponible más reciente
// 3. Lo marca como consumido
// 4. Lo guarda en warehouse
// 5. Retorna el código
```

---

## 🔧 Configuración

### Scripts SQL Requeridos

1. **Schema:** `database/schema.sql` - Crear tablas
2. **Platforms:** `database/seed_platforms.sql` - Insertar plataformas
3. **Settings:** `database/seed_settings.sql` - Insertar settings de asuntos

### Ejecutar Scripts

```bash
# Conectar a MySQL
mysql -u root -p

# Ejecutar schema
source database/schema.sql

# Ejecutar seeds
source database/seed_platforms.sql
source database/seed_settings.sql
```

---

## ⚠️ Consideraciones

### 1. Duplicados
- El sistema verifica duplicados antes de guardar
- No se guardan códigos duplicados para la misma plataforma y cuenta

### 2. Consumo de Códigos
- Un código solo puede ser consumido una vez
- Se marca como `consumed` inmediatamente al entregarse
- Se guarda en warehouse para histórico

### 3. Disponibilidad
- Solo se entregan códigos con `status = 'available'`
- Se entrega el más reciente (ordenado por `received_at DESC`)

### 4. Warehouse
- Todos los códigos se guardan en `gac_warehouse.codes_history`
- Permite análisis histórico y reportes

---

## 🧪 Testing

### Probar Consulta

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Gac\Config\AppConfig;
use Gac\Services\Code\CodeService;

AppConfig::load();

$codeService = new CodeService();

// Consultar código
$result = $codeService->consultCode('netflix', 'test@email.com', 'testuser');

var_dump($result);
```

### Probar Guardado

```php
<?php
$codeService = new CodeService();

$codeData = [
    'code' => '123456',
    'platform' => 'netflix',
    'from' => 'noreply@netflix.com',
    'subject' => 'Test',
    'date' => date('Y-m-d H:i:s'),
    'origin' => 'imap'
];

$codeId = $codeService->saveExtractedCode($codeData, 1);
echo "Código guardado: {$codeId}\n";
```

---

## 📚 Referencias

- Servicio IMAP: `IMAP_SERVICE.md`
- Servicio de Filtrado: `EMAIL_FILTER_SERVICE.md`
- Servicio de Extracción: `CODE_EXTRACTOR_SERVICE.md`
- Schema de BD: `database/schema.sql`

---

**Última actualización:** 2024