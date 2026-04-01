# 🚀 Guía de Ejecución Rápida - GAC

## Requisitos Previos

- **PHP 7.4 o superior** instalado
- **Composer** instalado
- **Extensiones PHP requeridas:**
  - pdo
  - json
  - mbstring
  - openssl

## Pasos para Ejecutar

### 1. Verificar PHP
```powershell
php -v
```

### 2. Verificar Composer
```powershell
composer --version
```

### 3. Navegar al directorio del proyecto
```powershell
cd SISTEMA_GAC
```

### 4. Instalar dependencias de Composer
```powershell
composer install
```

### 5. Configurar archivo .env
```powershell
# Copiar archivo de ejemplo
Copy-Item .env.example .env
```

**Nota:** Edita el archivo `.env` si necesitas cambiar configuraciones (por ahora puedes dejarlo como está para desarrollo).

### 6. Iniciar servidor de desarrollo
```powershell
# IMPORTANTE: Usa router.php para que funcionen las rutas
php -S localhost:8000 -t public public/router.php
```

**Nota:** El servidor PHP built-in no procesa `.htaccess`, por eso necesitas especificar `router.php`.

### 7. Abrir en el navegador
Abre tu navegador y visita:
```
http://localhost:8000
```

## Comandos Útiles

### Detener el servidor
Presiona `Ctrl + C` en la terminal donde está corriendo el servidor.

### Ver logs (si hay errores)
Los logs se guardan en `logs/` (si está configurado).

### Verificar rutas
- Página principal: `http://localhost:8000/`
- API de consulta: `http://localhost:8000/api/v1/codes/consult` (POST)

## Solución de Problemas

### Error: "vendor/autoload.php not found"
Ejecuta: `composer install`

### Error: "Class 'Dotenv\Dotenv' not found"
Ejecuta: `composer install` (asegúrate de que Composer instaló las dependencias)

### Error: "Extension X not found"
Instala la extensión PHP requerida o comenta temporalmente en `composer.json`

### Puerto 8000 ocupado
Usa otro puerto:
```powershell
php -S localhost:8080 -t public public/router.php
```

### Error 404 en todas las rutas
Asegúrate de usar el router:
```powershell
php -S localhost:8000 -t public public/router.php
```

## Notas Importantes

- El sistema está en modo desarrollo (`APP_ENV=development`)
- La base de datos NO es requerida para ver la interfaz (pero sí para funcionalidad completa)
- Las imágenes deben estar en `public/assets/images/`
- El sistema mostrará mensajes de "en desarrollo" hasta que se conecte la base de datos
