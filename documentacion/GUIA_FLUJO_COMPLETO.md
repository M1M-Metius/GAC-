# 🔄 Guía del Flujo Completo - GAC

Esta guía explica cómo funciona el sistema completo, qué configuraciones necesitas y cómo ponerlo en marcha.

---

## 📋 Índice

1. [Flujo General del Sistema](#flujo-general)
2. [Configuraciones Necesarias](#configuraciones)
3. [Paso a Paso para Ponerlo en Marcha](#paso-a-paso)
4. [Verificación y Pruebas](#verificacion)
5. [Troubleshooting](#troubleshooting)

---

## 🔄 Flujo General del Sistema

### Diagrama de Flujo

```
┌─────────────────────────────────────────────────────────────┐
│                   1. CONFIGURACIÓN INICIAL                  │
├─────────────────────────────────────────────────────────────┤
│  - Base de datos creada y configurada                        │
│  - Variables de entorno (.env) configuradas                  │
│  - Plataformas insertadas en BD                              │
│  - Patrones de asunto configurados                           │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│             2. AGREGAR CUENTAS DE EMAIL (IMAP)               │
├─────────────────────────────────────────────────────────────┤
│  Desde: /admin/email-accounts/create                         │
│                                                               │
│  Datos necesarios:                                           │
│  - Email de la cuenta                                        │
│  - Servidor IMAP (ej: mail.dominio.com)                      │
│  - Puerto IMAP (ej: 993 para SSL)                            │
│  - Usuario IMAP (ej: cuenta@dominio.com)                     │
│  - Contraseña IMAP                                           │
│  - Estado (Activa/Inactiva)                                  │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│           3. CRON JOB LEE EMAILS AUTOMÁTICAMENTE             │
├─────────────────────────────────────────────────────────────┤
│  Script: cron/email_reader.py                                │
│  Frecuencia: Cada 5 minutos (configurable)                  │
│                                                               │
│  Proceso:                                                    │
│  1. Obtiene todas las cuentas IMAP activas                  │
│  2. Para cada cuenta:                                        │
│     a) Conecta al servidor IMAP                              │
│     b) Lee últimos 50 emails                                 │
│     c) Filtra por asunto (usando patrones de settings)      │
│     d) Extrae códigos con regex                              │
│     e) Guarda códigos en BD (si no son duplicados)           │
│     f) Actualiza estado de sincronización                    │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│             4. USUARIOS CONSULTAN CÓDIGOS                   │
├─────────────────────────────────────────────────────────────┤
│  Desde: / (página principal)                                 │
│                                                               │
│  Usuario ingresa:                                            │
│  - Email                                                      │
│  - Username                                                  │
│  - Plataforma (Netflix, Disney+, etc.)                       │
│                                                               │
│  Sistema:                                                    │
│  1. Valida datos                                             │
│  2. Busca código más reciente disponible                     │
│  3. Marca código como "consumed"                            │
│  4. Guarda en warehouse (histórico)                          │
│  5. Retorna código al usuario                               │
└─────────────────────────────────────────────────────────────┘
```

---

## ⚙️ Configuraciones Necesarias

### 1. Base de Datos

#### Crear Bases de Datos

```sql
-- Ejecutar en MySQL
CREATE DATABASE gac_operational CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci;
CREATE DATABASE gac_warehouse CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci;
```

#### Ejecutar Scripts SQL

```bash
# 1. Schema (estructura de tablas)
mysql -u root -p gac_operational < database/schema.sql

# 2. Plataformas iniciales
mysql -u root -p gac_operational < database/seed_platforms.sql

# 3. Settings (patrones de asunto, configuraciones)
mysql -u root -p gac_operational < database/seed_settings.sql
```

### 2. Variables de Entorno (.env)

Crear archivo `.env` en la raíz del proyecto:

```env
# ============================================
# BASE DE DATOS OPERATIVA
# ============================================
DB_HOST=localhost
DB_PORT=3306
DB_NAME=gac_operational
DB_USER=root
DB_PASSWORD=tu_password_aqui
DB_CHARSET=utf8mb4
DB_COLLATE=utf8mb4_spanish_ci

# ============================================
# BASE DE DATOS WAREHOUSE
# ============================================
WAREHOUSE_DB_HOST=localhost
WAREHOUSE_DB_PORT=3306
WAREHOUSE_DB_NAME=gac_warehouse
WAREHOUSE_DB_USER=root
WAREHOUSE_DB_PASSWORD=tu_password_aqui

# ============================================
# CONFIGURACIÓN DE LA APLICACIÓN
# ============================================
APP_ENV=development
APP_NAME=GAC
APP_VERSION=1.0.0
APP_URL=http://localhost:8000
APP_DEBUG=true

# ============================================
# CONFIGURACIÓN DE CRON JOBS
# ============================================
CRON_ENABLED=true
CRON_EMAIL_READER_INTERVAL=5
CRON_WAREHOUSE_SYNC_INTERVAL=60

# ============================================
# LOGGING
# ============================================
LOG_LEVEL=info

# ============================================
# SEGURIDAD
# ============================================
SESSION_LIFETIME=7200
SESSION_SECURE=false
SESSION_HTTPONLY=true

# ============================================
# GMAIL API (Para futura implementación)
# ============================================
GMAIL_CLIENT_ID=
GMAIL_CLIENT_SECRET=
GMAIL_REDIRECT_URI=
GMAIL_SCOPES=https://www.googleapis.com/auth/gmail.readonly
```

### 3. Configurar Cuentas de Email

**Opción A: Desde la Interfaz Web (Recomendado)**

1. Iniciar sesión en el sistema: `/login`
2. Ir a: `/admin/email-accounts`
3. Click en "Agregar Cuenta"
4. Llenar formulario:
   - **Email:** `cuenta@dominio.com`
   - **Servidor IMAP:** `mail.dominio.com` (o `imap.gmail.com` para Gmail)
   - **Puerto:** `993` (SSL) o `143` (TLS)
   - **Usuario IMAP:** `cuenta@dominio.com`
   - **Contraseña:** Tu contraseña de email
   - **Estado:** Activa

**Opción B: Directamente en Base de Datos**

```sql
INSERT INTO email_accounts (
    email,
    type,
    provider_config,
    enabled
) VALUES (
    'cuenta@dominio.com',
    'imap',
    '{
        "imap_server": "mail.dominio.com",
        "imap_port": 993,
        "imap_encryption": "ssl",
        "imap_user": "cuenta@dominio.com",
        "imap_password": "tu_password_aqui"
    }',
    1
);
```

### 4. Configurar Patrones de Asunto

Los patrones ya están en `seed_settings.sql`, pero puedes modificarlos:

```sql
-- Ejemplo: Actualizar asunto para Netflix
UPDATE settings 
SET value = 'Tu código de acceso temporal de Netflix'
WHERE name = 'NETFLIX_1';
```

### 5. Instalar Dependencias

#### PHP (Composer)

```bash
cd SISTEMA_GAC
composer install
```

#### Python (para Cron Jobs)

```bash
cd SISTEMA_GAC/cron
pip install -r requirements.txt
```

---

## 📝 Paso a Paso para Ponerlo en Marcha

### Paso 1: Preparar Base de Datos

```bash
# 1. Crear bases de datos
mysql -u root -p
CREATE DATABASE gac_operational CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci;
CREATE DATABASE gac_warehouse CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci;
EXIT;

# 2. Ejecutar scripts
mysql -u root -p gac_operational < database/schema.sql
mysql -u root -p gac_operational < database/seed_platforms.sql
mysql -u root -p gac_operational < database/seed_settings.sql
```

### Paso 2: Configurar .env

```bash
# Copiar .env.example a .env (si existe)
cp .env.example .env

# O crear .env manualmente con las variables necesarias
```

### Paso 3: Instalar Dependencias

```bash
# PHP
composer install

# Python
cd cron
pip install -r requirements.txt
cd ..
```

### Paso 4: Agregar Cuenta de Email

1. Iniciar servidor PHP:
   ```bash
   php -S localhost:8000 -t public public/router.php
   ```

2. Abrir navegador: `http://localhost:8000/login`

3. Iniciar sesión (crear usuario admin si no existe)

4. Ir a: `http://localhost:8000/admin/email-accounts/create`

5. Agregar cuenta de email con credenciales IMAP

### Paso 5: Probar Lectura Manual

```bash
# Probar conexión
python3 cron/test_connection.py

# Ejecutar lectura manual
python3 cron/email_reader.py
```

### Paso 6: Configurar Cron Job

**Linux/cPanel:**

```bash
crontab -e
```

Agregar:

```cron
*/5 * * * * cd /ruta/completa/a/SISTEMA_GAC && /usr/bin/python3 cron/email_reader.py >> logs/cron.log 2>&1
```

**Windows (Task Scheduler):**

1. Abrir Task Scheduler
2. Crear tarea básica
3. Configurar para ejecutar cada 5 minutos

### Paso 7: Probar Consulta de Códigos

1. Asegurarse de que hay códigos en la BD (del paso 5)
2. Ir a: `http://localhost:8000/`
3. Ingresar:
   - Email: `test@email.com`
   - Username: `testuser`
   - Plataforma: `Netflix`
4. Click en "Consultar"
5. Debería mostrar el código si hay uno disponible

---

## ✅ Verificación y Pruebas

### 1. Verificar Base de Datos

```sql
-- Verificar plataformas
SELECT * FROM platforms WHERE enabled = 1;

-- Verificar settings de asuntos
SELECT name, value FROM settings WHERE name LIKE 'NETFLIX_%';

-- Verificar cuentas de email
SELECT email, type, enabled, sync_status FROM email_accounts;
```

### 2. Verificar Conexión Python

```bash
python3 cron/test_connection.py
```

Debería mostrar:
```
✓ Conexión a BD operativa: OK
✓ Conexión a BD warehouse: OK
✓ EmailAccountRepository: X cuenta(s) encontrada(s)
✓ Plataforma 'netflix': Netflix (enabled: 1)
```

### 3. Verificar Lectura de Emails

```bash
python3 cron/email_reader.py
```

Revisar logs en `logs/cron.log`:
```
✓ Emails leídos: 10
✓ Emails filtrados: 3
✓ Códigos extraídos: 2
✓ Código guardado: 123456 (netflix)
```

### 4. Verificar Códigos en BD

```sql
-- Ver códigos disponibles
SELECT 
    c.code,
    p.display_name as plataforma,
    c.received_at,
    c.status
FROM codes c
JOIN platforms p ON c.platform_id = p.id
WHERE c.status = 'available'
ORDER BY c.received_at DESC
LIMIT 10;
```

### 5. Verificar Consulta Web

1. Ir a `http://localhost:8000/`
2. Llenar formulario
3. Debería retornar código o mensaje de "no disponible"

---

## 🔧 Troubleshooting

### Problema: "No hay códigos disponibles"

**Causas posibles:**
1. No hay emails en la cuenta configurada
2. Los emails no coinciden con los patrones de asunto
3. Los códigos no se extrajeron correctamente
4. Los códigos ya fueron consumidos

**Solución:**
1. Verificar que hay emails en la cuenta
2. Verificar patrones de asunto en `settings`
3. Revisar logs de cron: `tail -f logs/cron.log`
4. Verificar códigos en BD: `SELECT * FROM codes WHERE status = 'available'`

### Problema: "Error al conectar con IMAP"

**Causas posibles:**
1. Credenciales incorrectas
2. Servidor IMAP incorrecto
3. Puerto incorrecto
4. Firewall bloqueando conexión

**Solución:**
1. Verificar credenciales en `email_accounts`
2. Probar conexión manual:
   ```python
   import imaplib
   mail = imaplib.IMAP4_SSL('mail.dominio.com', 993)
   mail.login('usuario', 'password')
   ```

### Problema: "Cron job no se ejecuta"

**Causas posibles:**
1. Cron no configurado
2. Ruta incorrecta en crontab
3. Python no encontrado
4. Permisos incorrectos

**Solución:**
1. Verificar crontab: `crontab -l`
2. Verificar ruta absoluta de Python: `which python3`
3. Verificar permisos: `chmod +x cron/email_reader.py`
4. Revisar logs del sistema: `/var/log/cron` (Linux)

### Problema: "No se guardan códigos"

**Causas posibles:**
1. Códigos duplicados
2. Plataforma deshabilitada
3. Error en BD

**Solución:**
1. Revisar logs: `tail -f logs/cron.log`
2. Verificar plataformas: `SELECT * FROM platforms WHERE enabled = 1`
3. Verificar duplicados: `SELECT code, COUNT(*) FROM codes GROUP BY code HAVING COUNT(*) > 1`

---

## 📊 Monitoreo

### Consultas Útiles

```sql
-- Estadísticas generales
SELECT 
    COUNT(*) as total_codigos,
    SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as disponibles,
    SUM(CASE WHEN status = 'consumed' THEN 1 ELSE 0 END) as consumidos
FROM codes;

-- Por plataforma
SELECT 
    p.display_name,
    COUNT(*) as total,
    SUM(CASE WHEN c.status = 'available' THEN 1 ELSE 0 END) as disponibles
FROM codes c
JOIN platforms p ON c.platform_id = p.id
GROUP BY p.id
ORDER BY total DESC;

-- Estado de sincronización
SELECT 
    email,
    sync_status,
    last_sync_at,
    error_message
FROM email_accounts
WHERE enabled = 1
ORDER BY last_sync_at DESC;
```

---

## 📚 Referencias

- **Instalación:** `INSTALLATION.md`
- **Cron Jobs:** `CRON_JOBS.md`
- **Servicios:** `IMAP_SERVICE.md`, `CODE_EXTRACTOR_SERVICE.md`, `EMAIL_FILTER_SERVICE.md`
- **Code Service:** `CODE_SERVICE.md`

---

**Última actualización:** 2024