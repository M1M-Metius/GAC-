# 🚀 Guía de Producción - GAC

Cómo levantar y ejecutar el sistema GAC en un servidor de producción (cPanel/hosting compartido).

---

## 📋 Índice

1. [Subir Archivos al Servidor](#subir-archivos)
2. [Configurar Base de Datos](#configurar-bd)
3. [Configurar Variables de Entorno](#configurar-env)
4. [Configurar Document Root](#configurar-document-root)
5. [Instalar Dependencias](#instalar-dependencias)
6. [Configurar Cron Jobs](#configurar-cron)
7. [Verificar Funcionamiento](#verificar)
8. [Mantenimiento](#mantenimiento)

---

## 📤 Subir Archivos al Servidor

### Opción 1: Git (Recomendado)

```bash
# En Terminal de cPanel
cd ~/public_html
git clone https://github.com/TU_USUARIO/TU_REPOSITORIO.git gac
cd gac
```

### Opción 2: FTP/File Manager

1. Comprimir proyecto localmente (ZIP)
2. Subir ZIP a `public_html/` vía File Manager
3. Extraer ZIP en cPanel
4. Renombrar carpeta a `gac` (opcional)

---

## 🗄️ Configurar Base de Datos

### 1. Crear Bases de Datos en cPanel

1. Ir a **cPanel** → **MySQL Databases**
2. Crear base de datos: `gac_operational`
3. Crear base de datos: `gac_warehouse`
4. Crear usuario MySQL (o usar uno existente)
5. Asignar usuario a ambas bases de datos con **ALL PRIVILEGES**

### 2. Ejecutar Scripts SQL

**Opción A: Desde phpMyAdmin**

1. Ir a **cPanel** → **phpMyAdmin**
2. Seleccionar base de datos `gac_operational`
3. Click en **SQL**
4. Copiar y pegar contenido de `database/schema.sql`
5. Ejecutar
6. Repetir con `database/seed_platforms.sql`
7. Repetir con `database/seed_settings.sql`

**Opción B: Desde Terminal**

```bash
cd ~/public_html/gac
mysql -u usuario_db -p gac_operational < database/schema.sql
mysql -u usuario_db -p gac_operational < database/seed_platforms.sql
mysql -u usuario_db -p gac_operational < database/seed_settings.sql
```

---

## ⚙️ Configurar Variables de Entorno

### 1. Crear Archivo .env

**IMPORTANTE:** El archivo `.env` NO debe subirse a Git por seguridad.

```bash
# En Terminal de cPanel
cd ~/public_html/gac
nano .env
```

### 2. Contenido del .env (Producción)

```env
# ============================================
# BASE DE DATOS OPERATIVA
# ============================================
DB_HOST=localhost
DB_PORT=3306
DB_NAME=gac_operational
DB_USER=usuario_db_cpanel
DB_PASSWORD=contraseña_db_segura
DB_CHARSET=utf8mb4
DB_COLLATE=utf8mb4_spanish_ci

# ============================================
# BASE DE DATOS WAREHOUSE
# ============================================
WAREHOUSE_DB_HOST=localhost
WAREHOUSE_DB_PORT=3306
WAREHOUSE_DB_NAME=gac_warehouse
WAREHOUSE_DB_USER=usuario_db_cpanel
WAREHOUSE_DB_PASSWORD=contraseña_db_segura

# ============================================
# CONFIGURACIÓN DE LA APLICACIÓN
# ============================================
APP_ENV=production
APP_NAME=GAC
APP_VERSION=1.0.0
APP_URL=https://tudominio.com
APP_DEBUG=false

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
SESSION_SECURE=true
SESSION_HTTPONLY=true

# ============================================
# GMAIL API (Opcional - para futura implementación)
# ============================================
GMAIL_CLIENT_ID=
GMAIL_CLIENT_SECRET=
GMAIL_REDIRECT_URI=
GMAIL_SCOPES=https://www.googleapis.com/auth/gmail.readonly
```

### 3. Proteger Archivo .env

```bash
chmod 600 .env
```

---

## 🌐 Configurar Document Root

### Opción A: Subdominio (Recomendado)

1. Ir a **cPanel** → **Subdominios**
2. Crear subdominio: `gac.tudominio.com`
3. **Document Root:** `/home/usuario/public_html/gac/public`
4. Guardar

### Opción B: Carpeta en Dominio Principal

1. El sistema funcionará en: `tudominio.com/gac`
2. El `.htaccess` en `public/` manejará las rutas automáticamente
3. No requiere configuración adicional

### Verificar .htaccess

Asegúrate de que existe `public/.htaccess` con:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>
```

---

## 📦 Instalar Dependencias

### 1. Instalar Composer (si no está instalado)

```bash
# Verificar si Composer está instalado
composer --version

# Si no está, instalarlo
cd ~
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
```

### 2. Instalar Dependencias PHP

```bash
cd ~/public_html/gac
composer install --no-dev --optimize-autoloader
```

**Nota:** `--no-dev` excluye dependencias de desarrollo (solo producción).

### 3. Instalar Dependencias Python (para Cron Jobs)

```bash
cd ~/public_html/gac/cron

# Verificar versión de Python
python3 --version

# Instalar dependencias
pip3 install -r requirements.txt

# O si pip3 no está disponible:
python3 -m pip install -r requirements.txt
```

---

## ⏰ Configurar Cron Jobs

### 1. Cron Job para Lectura de Emails

1. Ir a **cPanel** → **Cron Jobs**
2. Agregar nuevo cron job:
   - **Minuto:** `*/5` (cada 5 minutos)
   - **Hora:** `*`
   - **Día:** `*`
   - **Mes:** `*`
   - **Día de la semana:** `*`
   - **Comando:**
     ```bash
     cd /home/usuario/public_html/gac && /usr/bin/python3 cron/email_reader.py >> logs/cron.log 2>&1
     ```

**Nota:** Ajusta `/usr/bin/python3` según la ruta de Python en tu servidor. Para encontrarla:
```bash
which python3
```

### 2. Verificar Ruta de Python

```bash
# En Terminal de cPanel
which python3
# O
which python
```

Usa la ruta completa en el cron job.

---

## ✅ Verificar Funcionamiento

### 1. Verificar Acceso Web

Abrir en navegador:
- `https://gac.tudominio.com` (si usas subdominio)
- `https://tudominio.com/gac` (si usas carpeta)

Deberías ver la página de consulta de códigos.

### 2. Verificar Base de Datos

```sql
-- En phpMyAdmin o Terminal
SELECT * FROM platforms WHERE enabled = 1;
SELECT COUNT(*) as total FROM codes;
```

### 3. Verificar Cron Job

```bash
# Ver logs del cron
tail -f ~/public_html/gac/logs/cron.log

# O ejecutar manualmente
cd ~/public_html/gac
python3 cron/test_connection.py
python3 cron/email_reader.py
```

### 4. Probar Consulta de Códigos

1. Ir a la página principal
2. Ingresar datos de prueba
3. Verificar que retorne código o mensaje apropiado

---

## 🔧 Mantenimiento

### Actualizar Código

**Si usas Git:**

```bash
cd ~/public_html/gac
git pull origin main
composer install --no-dev --optimize-autoloader
```

**Si subes archivos manualmente:**

1. Subir archivos nuevos vía FTP/File Manager
2. Ejecutar:
   ```bash
   cd ~/public_html/gac
   composer install --no-dev --optimize-autoloader
   ```

### Ver Logs

```bash
# Logs de PHP (si están configurados)
tail -f ~/public_html/gac/logs/app.log

# Logs de Cron
tail -f ~/public_html/gac/logs/cron.log
```

### Limpiar Logs Antiguos

```bash
# Mantener últimos 30 días
find ~/public_html/gac/logs -name "*.log" -mtime +30 -delete
```

### Backup de Base de Datos

```bash
# Backup operativa
mysqldump -u usuario_db -p gac_operational > backup_operational_$(date +%Y%m%d).sql

# Backup warehouse
mysqldump -u usuario_db -p gac_warehouse > backup_warehouse_$(date +%Y%m%d).sql
```

---

## 🔐 Seguridad en Producción

### 1. Permisos de Archivos

```bash
# Archivos
find ~/public_html/gac -type f -exec chmod 644 {} \;

# Directorios
find ~/public_html/gac -type d -exec chmod 755 {} \;

# .env (solo lectura para propietario)
chmod 600 ~/public_html/gac/.env

# Logs (solo escritura para propietario)
chmod 600 ~/public_html/gac/logs/*.log
```

### 2. Ocultar Archivos Sensibles

Verificar que `.htaccess` en `public/` tenga:

```apache
<FilesMatch "\.(env|log|sql|md)$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

### 3. HTTPS

Asegúrate de tener SSL habilitado:
- En cPanel → **SSL/TLS Status**
- Activar certificado SSL (Let's Encrypt es gratuito)

### 4. Actualizar .env

```env
APP_ENV=production
APP_DEBUG=false
SESSION_SECURE=true
```

---

## 🆘 Solución de Problemas

### Error 500 Internal Server Error

1. Verificar permisos de archivos
2. Verificar `.env` existe y tiene valores correctos
3. Verificar logs de error de PHP en cPanel

### Error: "Class not found"

```bash
composer install --no-dev --optimize-autoloader
```

### Error: "Cannot connect to database"

1. Verificar credenciales en `.env`
2. Verificar que usuario MySQL tenga permisos
3. Verificar que bases de datos existan

### Cron Job no se ejecuta

1. Verificar ruta de Python: `which python3`
2. Verificar permisos de ejecución: `chmod +x cron/email_reader.py`
3. Verificar logs: `tail -f logs/cron.log`
4. Probar ejecución manual

### Página en blanco

1. Verificar `APP_DEBUG=true` temporalmente en `.env`
2. Verificar logs de PHP
3. Verificar que `vendor/autoload.php` existe

---

## 📊 Estructura Final en Servidor

```
/home/usuario/
└── public_html/
    └── gac/                          # Proyecto completo
        ├── public/                   # Document Root
        │   ├── index.php
        │   ├── .htaccess
        │   └── assets/
        ├── src/                       # Código fuente
        ├── vendor/                    # Dependencias PHP
        ├── cron/                      # Scripts Python
        ├── database/                  # Scripts SQL
        ├── logs/                      # Logs del sistema
        ├── .env                       # Configuración (NO en Git)
        └── composer.json
```

---

## 📚 Referencias

- **Despliegue Simple:** `DEPLOYMENT_SIMPLE.md`
- **Despliegue Avanzado:** `DEPLOYMENT.md`
- **Guía de Flujo:** `GUIA_FLUJO_COMPLETO.md`
- **Cron Jobs:** `CRON_JOBS.md`

---

**Última actualización:** 2024