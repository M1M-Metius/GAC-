# 🔧 Solución al Error: Class "Dotenv\Dotenv" not found

## ❌ Problema Actual

El sistema está intentando usar la clase `Dotenv\Dotenv` pero el paquete `vlucas/phpdotenv` no está instalado.

## ✅ Solución: Instalar Dependencias de Composer

**Ejecuta estos comandos en PowerShell (uno por uno):**

```powershell
# 1. Ir al directorio del proyecto
cd SISTEMA_GAC

# 2. Instalar todas las dependencias
composer install --ignore-platform-reqs
```

**Si el comando anterior falla, intenta:**

```powershell
composer update --ignore-platform-reqs
```

**O si tienes problemas con extensiones PHP:**

```powershell
composer install --ignore-platform-reqs --no-scripts
```

## 📦 ¿Qué hace `composer install`?

1. Lee el archivo `composer.json`
2. Descarga e instala todos los paquetes necesarios:
   - `vlucas/phpdotenv` (para variables de entorno)
   - `monolog/monolog` (para logging)
   - `google/apiclient` (para Gmail API)
   - `phpmailer/phpmailer` (para emails)
3. Genera el archivo `vendor/autoload.php`
4. Crea todas las clases necesarias en `vendor/`

## ⚠️ Importante

- **NO cierres la terminal** mientras se ejecuta `composer install`
- Puede tardar varios minutos la primera vez (descarga muchos archivos)
- Asegúrate de tener conexión a internet

## 🎯 Después de Instalar

Una vez que termine `composer install`, deberías poder ejecutar:

```powershell
php -S localhost:8000 -t public
```

Y el sistema debería funcionar correctamente.

## 🔍 Verificar que Funcionó

Después de `composer install`, verifica que existe:

```powershell
Test-Path "SISTEMA_GAC\vendor\autoload.php"
```

Debería devolver `True`.
