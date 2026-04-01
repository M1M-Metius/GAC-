# GAC - Guía de Instalación

## 📋 Requisitos Previos

- PHP 7.4+ (Recomendado 8.1+)
- MySQL 8.0+
- Apache 2.4+ con mod_rewrite o Nginx
- Composer
- Python 3.9+ (para cron jobs)
- Git

---

## 🚀 Instalación Paso a Paso

### 1. Clonar/Descargar el Proyecto

```bash
cd /ruta/donde/quieres/instalar
# Si tienes Git:
git clone [url-del-repositorio] SISTEMA_GAC
# O simplemente descomprime el archivo ZIP
```

### 2. Instalar Dependencias PHP

```bash
cd SISTEMA_GAC
composer install
```

### 3. Configurar Variables de Entorno

```bash
# Copiar archivo de ejemplo
cp .env.example .env

# Editar .env con tus configuraciones
# (Ver sección de Configuración más abajo)
```

### 4. Crear Base de Datos

```bash
# Opción 1: Usando MySQL CLI
mysql -u root -p < database/schema.sql

# Opción 2: Usando phpMyAdmin
# Importar database/schema.sql
```

### 5. Ejecutar Migraciones y Seeders

```bash
# Las migraciones y seeders se ejecutarán manualmente
# Ver database/migrations/ y database/seeds/
```

### 6. Configurar Permisos (Linux/Mac)

```bash
chmod -R 755 storage/
chmod -R 755 logs/
chmod -R 755 public/
```

### 7. Configurar Apache/Nginx

#### Apache (.htaccess ya incluido)

Asegúrate de que `mod_rewrite` esté habilitado:

```apache
LoadModule rewrite_module modules/mod_rewrite.so
```

#### Nginx

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### 8. Instalar Dependencias Python (para Cron)

```bash
cd cron
pip install -r requirements.txt
```

### 9. Configurar Cron Jobs

```bash
# Editar crontab
crontab -e

# Agregar:
*/5 * * * * /usr/bin/python3 /ruta/completa/SISTEMA_GAC/cron/email_reader.py >> /ruta/completa/SISTEMA_GAC/logs/cron.log 2>&1
0 * * * * /usr/bin/python3 /ruta/completa/SISTEMA_GAC/cron/warehouse_sync.py >> /ruta/completa/SISTEMA_GAC/logs/cron.log 2>&1
```

---

## ⚙️ Configuración Detallada

### Variables de Entorno (.env)

Edita el archivo `.env` con tus valores:

```env
# Base de Datos
DB_HOST=localhost
DB_NAME=gac_operational
DB_USER=tu_usuario
DB_PASSWORD=tu_contraseña

# Gmail API (Obtener desde Google Cloud Console)
GMAIL_CLIENT_ID=tu_client_id
GMAIL_CLIENT_SECRET=tu_client_secret
GMAIL_REDIRECT_URI=http://tudominio.com/gac/gmail/callback
```

### Configurar Gmail API

1. Ve a [Google Cloud Console](https://console.cloud.google.com/)
2. Crea un nuevo proyecto
3. Habilita Gmail API
4. Crea credenciales OAuth 2.0
5. Agrega las credenciales a `.env`

---

## ✅ Verificación de Instalación

1. Accede a: `http://localhost/gac/public/`
2. Deberías ver la página de inicio
3. Verifica logs en `logs/app.log`

---

## 🔧 Solución de Problemas

### Error: "Class not found"
- Ejecuta: `composer dump-autoload`

### Error: "Database connection failed"
- Verifica credenciales en `.env`
- Asegúrate de que MySQL esté corriendo

### Error: "Permission denied"
- Verifica permisos de carpetas `logs/` y `storage/`

---

## 📚 Próximos Pasos

- Configurar usuarios y roles
- Conectar cuentas Gmail
- Configurar plataformas
- Revisar documentación de API
