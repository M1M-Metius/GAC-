# GAC - Parámetros y Configuración Definidos

## 🎯 Nombre del Sistema

**GAC** - Gestor Automatizado de Códigos  
**Versión:** 2.0.0

---

## 📐 Arquitectura Definida

### Patrón Arquitectónico:
- **MVC (Model-View-Controller)**
- **Service Layer** (Lógica de negocio)
- **Repository Pattern** (Acceso a datos)

### Estructura de Capas:
```
Views → Controllers → Services → Repositories → Database
```

---

## 🗂️ Estructura de Carpetas (PSR-4)

### Namespace Base:
```php
namespace Gac\
```

### Carpetas Principales:
- `public/` - Punto de entrada público
- `src/` - Código fuente
- `database/` - Scripts de BD
- `cron/` - Scripts Python
- `views/` - Templates
- `api/` - Endpoints REST
- `tests/` - Tests
- `logs/` - Logs

---

## 🗄️ Base de Datos

### Bases de Datos:
1. **`gac_operational`** - Base operativa
2. **`gac_warehouse`** - Data Warehouse

### Tablas Principales:
- `roles` - Roles del sistema
- `permissions` - Permisos
- `users` - Usuarios
- `platforms` - Plataformas (Netflix, Disney+, etc.)
- `email_accounts` - Cuentas de email (IMAP/Gmail)
- `codes` - Códigos activos
- `settings` - Configuraciones
- `codes_history` - Histórico (warehouse)
- `daily_statistics` - Estadísticas diarias (warehouse)

---

## 🔐 Sistema de Roles

### Roles Definidos:
1. **SUPER_ADMIN** - Acceso total
2. **ADMIN** - Gestión completa (excepto usuarios/roles)
3. **OPERATOR** - Consulta y gestión básica
4. **VIEWER** - Solo lectura
5. **USER** - Solo consulta pública

### Permisos por Categoría:
- `codes.*` - Gestión de códigos
- `users.*` - Gestión de usuarios
- `roles.*` - Gestión de roles
- `settings.*` - Configuración
- `statistics.*` - Reportes
- `email_accounts.*` - Cuentas de email
- `dashboard.*` - Dashboard
- `gmail.*` - Gmail API

---

## 🔧 Stack Tecnológico

### Backend:
- **PHP:** 7.4+ / 8.0+ (Recomendado 8.1+)
- **Composer:** Gestión de dependencias
- **PSR-4:** Autoloading estándar

### Base de Datos:
- **MySQL:** 8.0+
- **Charset:** utf8mb4
- **Collation:** utf8mb4_spanish_ci

### Frontend:
- **HTML5**
- **CSS3** (Bootstrap/Tailwind)
- **JavaScript** (Vanilla/Vue.js)

### Servicios Externos:
- **Gmail API** (OAuth 2.0)
- **IMAP** (PHP extension)

### Background Jobs:
- **Python:** 3.9+
- **Librerías:**
  - `google-api-python-client`
  - `mysql-connector-python`
  - `python-dotenv`

---

## 📦 Dependencias PHP

### Producción:
- `vlucas/phpdotenv` - Variables de entorno
- `monolog/monolog` - Logging
- `google/apiclient` - Gmail API
- `phpmailer/phpmailer` - Email

### Desarrollo:
- `phpunit/phpunit` - Testing

---

## 🔄 Flujos Principales

### 1. Consulta de Código:
```
Usuario → CodeController → CodeService → CodeRepository → MySQL
```

### 2. Lectura de Emails:
```
Cron → Python Script → ImapService/GmailApiService → CodeExtractor → CodeRepository
```

### 3. Autenticación:
```
Usuario → AuthController → AuthService → UserRepository → Session
```

### 4. OAuth Gmail:
```
Usuario → GmailController → OAuthService → Google → Callback → Save Tokens
```

---

## ⚙️ Configuración (.env)

### Variables Principales:
- `APP_NAME` - Nombre de la aplicación
- `APP_ENV` - Entorno (development/production)
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` - BD operativa
- `WAREHOUSE_DB_*` - BD warehouse
- `GMAIL_CLIENT_ID`, `GMAIL_CLIENT_SECRET` - Gmail API
- `IMAP_HOST`, `IMAP_PORT` - Configuración IMAP
- `ENCRYPTION_KEY` - Clave de cifrado
- `CRON_ENABLED` - Habilitar cron jobs

---

## 🕐 Cron Jobs

### Intervalos:
- **Lectura de emails:** Cada 5 minutos
- **Sincronización warehouse:** Cada hora

### Scripts:
- `email_reader.py` - Lectura automática
- `code_extractor.py` - Extracción de códigos
- `warehouse_sync.py` - Sincronización histórico

---

## 📱 Vistas Definidas

### Públicas:
- Consulta de códigos (`/codes/consult`)

### Administrativas:
- Dashboard (`/admin/dashboard`)
- Gestión de códigos (`/admin/codes`)
- Cuentas de email (`/admin/email-accounts`)
- Plataformas (`/admin/platforms`)
- Configuración (`/admin/settings`)
- Usuarios (`/admin/users`) - Solo SUPER_ADMIN
- Roles (`/admin/roles`) - Solo SUPER_ADMIN
- Estadísticas (`/admin/statistics`)

### Gmail:
- Conectar (`/gmail/connect`)
- Callback (`/gmail/callback`)

### Perfil:
- Ver/Editar (`/profile`)

---

## 🔒 Seguridad

### Implementaciones:
- **Passwords:** `password_hash()` con bcrypt
- **Tokens OAuth:** Cifrados en BD
- **SQL:** Prepared statements (PDO)
- **Sessions:** HTTPOnly, Secure (en producción)
- **CSRF:** Tokens en formularios
- **Rate Limiting:** Límite de requests

---

## 📊 Convenciones de Nomenclatura

### Clases:
- **Controllers:** `CodeController`, `AuthController`
- **Models:** `User`, `Code`, `Platform`
- **Services:** `CodeService`, `EmailService`
- **Repositories:** `CodeRepository`, `UserRepository`

### Archivos:
- **PHP:** PascalCase (`CodeController.php`)
- **Vistas:** snake_case (`consult.php`)
- **Config:** PascalCase (`AppConfig.php`)

### Base de Datos:
- **Tablas:** snake_case (`email_accounts`)
- **Columnas:** snake_case (`email_account_id`)
- **Índices:** `idx_nombre_columna`

---

## 🎨 Estándares de Código

- **PSR-4:** Autoloading
- **PSR-12:** Coding Style
- **Composer:** Gestión de dependencias
- **Namespaces:** `Gac\` como base

---

## ✅ Estado Actual

### ✅ Completado:
- Estructura de carpetas
- Archivos de configuración
- Schema de base de datos
- Núcleo de aplicación (Router, Request)
- Documentación base

### 📋 Pendiente:
- Implementación de Models
- Implementación de Controllers
- Implementación de Services
- Implementación de Repositories
- Vistas completas
- Tests

---

**Todos los parámetros están definidos y la estructura está lista para desarrollo**
