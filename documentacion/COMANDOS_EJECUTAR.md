# 🚀 Comandos para Ejecutar el Sistema GAC

## ⚡ Inicio Rápido

### 1. Verificar Dependencias PHP

```bash
# Verificar versión de PHP (debe ser 7.4 o superior)
php -v

# Verificar extensiones necesarias
php -m | findstr "pdo pdo_mysql json mbstring openssl"
```

### 2. Instalar Dependencias PHP (si no están instaladas)

```bash
cd SISTEMA_GAC
composer install
```

### 3. Crear Archivo .env

Crear archivo `.env` en la raíz del proyecto (`SISTEMA_GAC/.env`):

```env
# Base de Datos Operativa
DB_HOST=localhost
DB_PORT=3306
DB_NAME=gac_operational
DB_USER=root
DB_PASSWORD=tu_password_aqui
DB_CHARSET=utf8mb4
DB_COLLATE=utf8mb4_spanish_ci

# Base de Datos Warehouse
WAREHOUSE_DB_HOST=localhost
WAREHOUSE_DB_PORT=3306
WAREHOUSE_DB_NAME=gac_warehouse
WAREHOUSE_DB_USER=root
WAREHOUSE_DB_PASSWORD=tu_password_aqui

# Aplicación
APP_ENV=development
APP_NAME=GAC
APP_VERSION=1.0.0
APP_URL=http://localhost:8000
APP_DEBUG=true

# Cron
CRON_ENABLED=true
CRON_EMAIL_READER_INTERVAL=5

# Logging
LOG_LEVEL=info

# Seguridad
SESSION_LIFETIME=7200
SESSION_SECURE=false
SESSION_HTTPONLY=true
```

### 4. Preparar Base de Datos

```bash
# Conectar a MySQL
mysql -u root -p

# Crear bases de datos
CREATE DATABASE gac_operational CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci;
CREATE DATABASE gac_warehouse CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci;
EXIT;

# Ejecutar scripts SQL
mysql -u root -p gac_operational < database/schema.sql
mysql -u root -p gac_operational < database/seed_platforms.sql
mysql -u root -p gac_operational < database/seed_settings.sql
```

### 5. Ejecutar el Servidor

```bash
cd SISTEMA_GAC
php -S localhost:8000 -t public public/router.php
```

### 6. Acceder al Sistema

Abrir en el navegador:
- **Página principal:** http://localhost:8000
- **Login:** http://localhost:8000/login
- **Dashboard:** http://localhost:8000/admin/dashboard (requiere login)

---

## 📋 Comandos Completos (Copia y Pega)

### Windows (PowerShell)

```powershell
# 1. Ir al directorio
cd SISTEMA_GAC

# 2. Instalar dependencias (si no están)
composer install

# 3. Crear .env (editar con tus credenciales)
# Copiar el contenido de arriba y crear archivo .env

# 4. Preparar BD (en otra terminal)
mysql -u root -p
# Ejecutar comandos SQL de arriba

# 5. Ejecutar servidor
php -S localhost:8000 -t public public/router.php
```

### Linux/Mac

```bash
# 1. Ir al directorio
cd SISTEMA_GAC

# 2. Instalar dependencias
composer install

# 3. Crear .env
nano .env
# Pegar contenido y guardar

# 4. Preparar BD
mysql -u root -p < database/schema.sql
mysql -u root -p gac_operational < database/seed_platforms.sql
mysql -u root -p gac_operational < database/seed_settings.sql

# 5. Ejecutar servidor
php -S localhost:8000 -t public public/router.php
```

---

## 🔧 Verificar que Funciona

### 1. Verificar Servidor

Al ejecutar el comando, deberías ver:
```
PHP 8.x.x Development Server started at http://localhost:8000
```

### 2. Probar en Navegador

Abrir: http://localhost:8000

Deberías ver la página de consulta de códigos.

### 3. Verificar Base de Datos

```sql
-- Verificar plataformas
SELECT * FROM platforms WHERE enabled = 1;

-- Verificar settings
SELECT name, value FROM settings LIMIT 5;
```

---

## 🐍 Ejecutar Cron Job (Opcional)

### Instalar Dependencias Python

```bash
cd SISTEMA_GAC/cron
pip install -r requirements.txt
```

### Probar Conexión

```bash
python3 cron/test_connection.py
```

### Ejecutar Lectura Manual

```bash
python3 cron/email_reader.py
```

---

## ⚠️ Solución de Problemas

### Error: "vendor/autoload.php not found"

```bash
composer install
```

### Error: "Class 'Dotenv\Dotenv' not found"

```bash
composer install --ignore-platform-reqs
```

### Error: "No se puede conectar a MySQL"

- Verificar que MySQL esté corriendo
- Verificar credenciales en `.env`
- Verificar que las bases de datos existan

### Error: "404 Not Found"

- Asegúrate de usar: `php -S localhost:8000 -t public public/router.php`
- No uses solo: `php -S localhost:8000 -t public`

### Puerto 8000 ocupado

Cambiar puerto:
```bash
php -S localhost:8080 -t public public/router.php
```

Luego acceder a: http://localhost:8080

---

## 📚 Más Información

- **Guía Completa:** `GUIA_FLUJO_COMPLETO.md`
- **Instalación:** `INSTALLATION.md`
- **Cron Jobs:** `CRON_JOBS.md`